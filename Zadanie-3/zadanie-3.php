<?php 
//Konfiguracja bazy danych 
$dsn = 'mysql:host=localhost;dbname=test;charset=utf8mb4';
$dbUser = 'root';
$dbPass =";

try {
$pdo=new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);
} catch (PDOException $e) {
exit("Błąd połączenia z bazą danych: " . $e->getMessage());

}

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
$email = trim($data['email'] ?? ");
$userType = $data['user_type'] ?? ";

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
throw new Exception("Nieprawidłowy email.");
}

$stmt->execute([$email]); 
if($stmt->fetchColumn() > 0) {
throw new Exception("Email już istnieje.");

}

$pdo->beginTransaction();

try{
$stmt = $pdo->prepare("INSERT INTO users (email, user_type) VALUES (?,?)");
$stmt->execute([$email, $userType]);
$userId = $pdo->lastInserId();

if ($userType === 'individual')
{
$firstName = trim($data['first_name'] ?? '');
$birthDate = $data['birth_date'] ?? '';

if (!preg_match('/^[\p{L} -]{2,}$/u', $firstName)) {
throw new Exception("Nieprawidłowe imię.");
}

if (!validateBirthDate($birthDate)) {
throw new Exception("Nieprawidłowa data urodzenia.");
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





