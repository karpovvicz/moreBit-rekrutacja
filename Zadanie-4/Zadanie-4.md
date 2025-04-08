Od Testera Oprogramowania otrzymujesz dokument z poniższymi komunikatami błędów. Twoim
zadaniem jest wytłumaczeniem przyczyny pojawienia się błędu oraz zaproponowanie sposobu
obsługi danego defektu. 


## A. [42P01] BŁĄD: relacja "cregisters.creg" nie istnieje.
Typ Błędu w PostgreSQL jest to naprawdopodobniej brakująca tabela lub błędna nazwa schematu/tabeli,
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
    }

## B.  Nie można wypisać wniosku na dzień, w którym nie ma okresu zatrudnienia

Błąd najprawdopodobniej wynika z błędu logiki walidacji zakresu dat, który się pojawia gdy użytkownik próbuje wygenerować wniosek urlopowy na dzień poza datą zatrudnienia.
Sposobem rozwiązania takiego problemu może być ustanowienie walidacji po stronie serwera

    php  

    Copy  

    Edit  

  
    
    $dataWniosku = new DateTime($_POST['data']);
     if ($dataWniosku < $pracownik->data_zatrudnienia_od || 
    ($pracownik->data_zatrudnienia_do && $dataWniosku > $pracownik->data_zatrudnienia_do)) {
    throw new Exception("Nie można wypisać wniosku poza okresem zatrudnienia.");
     }  

Po zastosowaniu formularz powinien dynamicznie dopasować dostępne daty.

## C.  [22P02] BŁĄD: invalid input syntax for integer: "30B"

Błąd w PostgreSQL na skutek nieprawidłowej konwersji do integera.
Wartość "30B" jest wstawiana w kolumnę oczekującą liczby całkowitej (integer).

Przyczyną może być 
niepoprawnny kod z formularza np. numer pokoju lub brak walidacji po stronie frontu lub serwera.

Sposobem rozwiązania takiego problemu może być ustanowienie walidacji wejściowej: 

     $id = $_POST['id'];
     if (!ctype_digit($id)) {
     throw new InvalidArgumentException("Nieprawidłowy identyfikator.");
      }

lub zastosowanie tzw. Sanity-check przed wysyłką zapytania: 

     $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
     $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
     $stmt->execute();

## D.  [25-Nov-2022 15:50:02 Europe/Warsaw] Eksport danych do Sage ERP FK -1

Błąd wystepuje najprawdopodobniej na skutek niepowodzenia w wysyłce danych czego przyczyną może być np. brak danych, błąd połączenia, błąd autoryzacji, zmiana w API. 

Sposobem rozwiązania tego problemu może być obsługa wyjątków przy eksporcie danych za pomocą funkcji: 

     try {
    $result = $sageClient->eksportuj($dane);
    if ($result->kod !== 0) {
        throw new RuntimeException("Eksport nieudany: kod " . $result->kod);
    }
    } catch (Exception $e) {
    error_log("Błąd eksportu do Sage: " . $e->getMessage());
    notifyAdmin($e);
    }
