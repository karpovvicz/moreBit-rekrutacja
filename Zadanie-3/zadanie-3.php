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
