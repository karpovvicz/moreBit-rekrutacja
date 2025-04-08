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
