Od Testera Oprogramowania otrzymujesz dokument z poniższymi komunikatami błędów. Twoim
zadaniem jest wytłumaczeniem przyczyny pojawienia się błędu oraz zaproponowanie sposobu
obsługi danego defektu. 


A. 

[42P01] BŁĄD: relacja "cregisters.creg" nie istnieje.
Typ Błędu w PostgreSQL naprawdopodobniej brakująca tabela lub błędna nazwa schematu/tabeli,
czego przyczyną jest odwołanie do tabeli cregisters.creg, która albo nie istnieje w bazie danych albo ma błąd zapisu.

Alternatywnie tabela nie została jeszcze utworzona np. z powodu błędu w migracjach i
Użytkownik bazy danych nie ma dostępu do tego schematu.

Proponowana obsługa tego błędu:
Weryfikacja schematów i migracji:

sql
Copy
Edit
SELECT * FROM information_schema.tables WHERE table_schema = 'cregisters';
Upewnij się, że tabela creg faktycznie istnieje.

Zabezpieczenie zapytania w PHP: Zamiast pisać surowe SQL-e:

php
Copy
Edit
$sql = "SELECT * FROM cregisters.creg WHERE ...";
używaj mechanizmu ORM (np. Doctrine lub Eloquent), który waliduje zapytania i schematy.

Fallback / logowanie błędu:

php
Copy
Edit
try {
    $result = $db->query("SELECT * FROM cregisters.creg");
} catch (PDOException $e) {
    error_log("Błąd zapytania do tabeli creg: " . $e->getMessage());
    throw new Exception("Wystąpił błąd przy pobieraniu danych. Skontaktuj się z administratorem.");
}



