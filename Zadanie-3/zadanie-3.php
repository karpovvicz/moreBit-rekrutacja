<?php
// Konfiguracja bazy danych
$dsn = 'mysql:host=localhost;dbname=test;charset=utf8mb4';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    exit("Błąd połączenia z bazą danych: " . $e->getMessage());
}

// Prosty router (tylko POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;

    try {
        registerUser($pdo, $data);
        echo "Rejestracja zakończona sukcesem!";
    } catch (Exception $e) {
        echo "Błąd: " . $e->getMessage();
    }
}

function registerUser(PDO $pdo, array $data): void
{
    // Wymagane pola
    $email = trim($data['email'] ?? '');
    $userType = $data['user_type'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Nieprawidłowy email.");
    }

    // Sprawdzenie czy email już istnieje
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Email już istnieje.");
    }

    // Start transakcji
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare("INSERT INTO users (email, user_type) VALUES (?, ?)");
        $stmt->execute([$email, $userType]);
        $userId = $pdo->lastInsertId();

        if ($userType === 'individual') {
            $firstName = trim($data['first_name'] ?? '');
            $birthDate = $data['birth_date'] ?? '';

            if (!preg_match('/^[\p{L} -]{2,}$/u', $firstName)) {
                throw new Exception("Nieprawidłowe imię.");
            }

            if (!validateBirthDate($birthDate)) {
                throw new Exception("Nieprawidłowa lub niepełnoletnia data urodzenia.");
            }

            $stmt = $pdo->prepare("INSERT INTO user_individuals (user_id, first_name, birth_date) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $firstName, $birthDate]);
        } elseif ($userType === 'company') {
            $companyName = trim($data['company_name'] ?? '');
            $nip = preg_replace('/[^0-9]/', '', $data['nip'] ?? '');

            if (strlen($companyName) < 2) {
                throw new Exception("Nazwa firmy jest zbyt krótka.");
            }

            if (!validateNip($nip)) {
                throw new Exception("Nieprawidłowy NIP.");
            }

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_companies WHERE nip = ?");
            $stmt->execute([$nip]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("NIP już istnieje.");
            }

            $stmt = $pdo->prepare("INSERT INTO user_companies (user_id, company_name, nip) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $companyName, $nip]);
        } else {
            throw new Exception("Nieznany typ użytkownika.");
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}


function validateBirthDate(string $date): bool
{
    if (!strtotime($date)) return false;
    $age = date_diff(date_create($date), date_create())->y;
    return $age >= 18;
}

function validateNip(string $nip): bool
{
    if (!preg_match('/^\d{10}$/', $nip)) return false;

    $weights = [6, 5, 7, 2, 3, 4, 5, 6, 7];
    $sum = 0;
    for ($i = 0; $i < 9; $i++) {
        $sum += $nip[$i] * $weights[$i];
    }

    return $sum % 11 === (int)$nip[9];
}
?>




