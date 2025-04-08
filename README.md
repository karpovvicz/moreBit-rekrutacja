# Zadanie Rekrutacyjne - Młodszy Programista PHP

## Zadanie 1: Generator kalendarza miesięcznego w HTML

### Opis zadania
Zadanie polegało na napisaniu metody, która generuje w HTML widok kalendarza dla wybranego miesiąca i roku. Dni tygodnia zaczynają się od **poniedziałku**, a **niedziele są oznaczone kolorem czerwonym**.

### Technologie użyte:
- PHP 8.x
- HTML5
- Kod zgodny z UTF-8

---

## Rozwiązanie

W ramach rozwiązania przygotowałem klasę `KalendarzGenerator`, która:

- Przyjmuje miesiąc i rok w konstruktorze
- Waliduje poprawność zakresu miesięcy
- Generuje dynamicznie HTML-ową tabelę z dniami miesiąca
- Uwzględnia, w którym dniu tygodnia zaczyna się miesiąc
- Oznacza **niedziele** na czerwono
- Wyświetla **nazwę miesiąca** w języku polskim

### Kluczowe metody:

#### `render(): string`

Główna metoda generująca kod HTML kalendarza. Tworzy tabelę z nagłówkiem dni tygodnia oraz odpowiednio ułożonymi dniami miesiąca. Niedziele są wyróżnione stylem `color: red`.

#### `renderHeader(): string`

Pomocnicza metoda, która generuje nagłówek tabeli z nazwami dni tygodnia od poniedziałku do niedzieli.

#### `getMonthName(): string`

Zwraca polską nazwę miesiąca na podstawie przekazanego numeru miesiąca oraz rok.

---

## Zadanie 2: Konwersja adresu komórki Excela na format numeryczny

### Opis zadania

Celem zadania było napisanie funkcji, która przekształca adres komórki z programu typu Excel (np. `A2`, `B10`, `Z500`) na format numeryczny w postaci:

Przykłady:
- `A2` → `1.2`
- `B2` → `2.2`
- `AA1` → `27.1`

---

## Rozwiązanie

W ramach rozwiązania stworzona została funkcja PHP:

```php
function konwertujExcelNumerycznie(string $cellAddress): string
```
## Zadanie 3: Formularz rejestracji użytkownika – osoby fizyczne i firmy

### Opis zadania

Zadanie polegało na zaprojektowaniu mechanizmu rejestracji użytkowników z podziałem na:
- **Osoby fizyczne** – wymagane dane: imię, adres e-mail, data urodzenia
- **Firmy** – wymagane dane: nazwa firmy, adres e-mail, numer NIP

Dodatkowo, należało zaproponować strukturę bazy danych oraz zabezpieczenia przed:
- duplikacją danych,
- błędami w danych wejściowych,
- niespójnościami.

---

## Struktura bazy danych

Zaprojektowano 3 tabele:

### Tabela `users`

| Kolumna      | Typ         | Opis                             |
|--------------|-------------|----------------------------------|
| `id`         | INT         | Klucz główny, autoinkrementacja |
| `email`      | VARCHAR     | Unikalny adres e-mail           |
| `user_type`  | ENUM        | `individual` lub `company`     |

### Tabela `user_individuals`

| Kolumna       | Typ      | Opis                              |
|---------------|----------|-----------------------------------|
| `user_id`     | INT      | Klucz obcy do `users.id`          |
| `first_name`  | VARCHAR  | Imię                              |
| `birth_date`  | DATE     | Data urodzenia                    |

### Tabela `user_companies`

| Kolumna        | Typ      | Opis                             |
|----------------|----------|----------------------------------|
| `user_id`      | INT      | Klucz obcy do `users.id`         |
| `company_name` | VARCHAR  | Nazwa firmy                      |
| `nip`          | VARCHAR  | Numer NIP (unikalny)             |

---

## Walidacje i ochrona przed błędami

### 1. **Email**:
- Sprawdzany format `FILTER_VALIDATE_EMAIL`
- Unikalność – weryfikacja w tabeli `users`

### 2. **Dane osoby fizycznej**:
- Imię: musi zawierać litery (z polskimi znakami), minimum 2 znaki
- Data urodzenia: walidacja wieku (minimum 18 lat)

### 3. **Dane firmy**:
- Nazwa firmy: minimum 2 znaki
- NIP:
  - Walidacja długości i poprawności (algorytm wagowy)
  - Unikalność w tabeli `user_companies`

### 4. **Mechanizm transakcji**:
- W przypadku błędu w którymkolwiek etapie rejestracji, zmiany są wycofywane (`rollback`)
- Gwarantuje spójność danych

---

## Przykładowe dane wejściowe (POST)

### Osoba fizyczna:
```http
POST /register.php
Content-Type: application/x-www-form-urlencoded

email=j.kowalski@example.com&
user_type=individual&
first_name=Jan&
birth_date=1990-05-20
```

## Interfejs użytkownika (frontend)

Do rejestracji użytkowników został przygotowany prosty formularz HTML, który umożliwia:
- wybór typu użytkownika (osoba fizyczna lub firma),
- dynamiczne wyświetlanie odpowiednich pól,
- podstawową walidację danych po stronie klienta (JavaScript i HTML5).

### Struktura formularza

Formularz wysyła dane metodą `POST` do pliku `zadanie-3.php`.

### Obsługa typu użytkownika (JavaScript)

Skrypt `toggleFields(type)`:
- Pokazuje lub ukrywa odpowiednie pola formularza w zależności od wyboru typu użytkownika (`individual` / `company`).

```js
function toggleFields(type) {
    document.getElementById('individual-fields').style.display = type === 'individual' ? 'block' : 'none';
    document.getElementById('company-fields').style.display = type === 'company' ? 'block' : 'none';
}
```

## Zadanie 4: Analiza i Obsługa Błędów w Aplikacji 

# README — Analiza i Obsługa Błędów w Aplikacji

W ramach przeglądu błędów zgłoszonych przez testera oprogramowania przygotowano poniższe wyjaśnienia przyczyn oraz propozycje ich obsługi. Celem jest poprawa stabilności i odporności systemu na błędne dane lub nieprzewidziane sytuacje.

---

## A. Błąd: `relacja "cregisters.creg" nie istnieje`

**Opis problemu:**  
System próbuje odczytać dane z nieistniejącej tabeli lub błędnie określonego schematu w bazie danych PostgreSQL.

**Rozwiązanie:**
- Zweryfikuj, czy tabela istnieje w schemacie `cregisters`.
- Upewnij się, że migracje bazy danych zostały wykonane poprawnie.
- Stosuj ORM, by unikać literówek i nieprawidłowych zapytań SQL.
- Dodaj obsługę błędu i logowanie po stronie serwera, aby szybko identyfikować problem.

---

## B. Nie można wypisać wniosku poza okresem zatrudnienia

**Opis problemu:**  
Użytkownik próbuje złożyć wniosek (np. urlopowy) na datę, która nie mieści się w jego okresie zatrudnienia.

**Rozwiązanie:**
- Wprowadź walidację daty wniosku względem daty zatrudnienia po stronie backendu.
- Ogranicz dostępne daty w formularzu, aby uniknąć błędnych prób.

---

## C. Błąd: `invalid input syntax for integer: "30B"`

**Opis problemu:**  
Aplikacja próbuje przekształcić nieprawidłowy ciąg znaków (np. "30B") na liczbę całkowitą.

**Rozwiązanie:**
- Waliduj dane wejściowe (np. identyfikatory) jako liczby po stronie klienta i serwera.
- W przypadku dynamicznych zapytań używaj bezpiecznego bindowania wartości.

---

## D. Eksport do Sage ERP – nieudany

**Opis problemu:**  
Błąd występuje podczas eksportu danych do zewnętrznego systemu ERP. Może wynikać z braku danych, błędów sieci, lub zmian po stronie API.

**Rozwiązanie:**
- Dodaj obsługę wyjątków przy eksporcie.
- Zaloguj szczegóły błędu oraz powiadom administratora systemu.

---

## E. `Failed to load resource: net::ERR_FAILED`

**Opis problemu:**  
Przeglądarka nie może załadować zasobu (np. API, pliku JS). Powód: błędny URL, brak połączenia lub brak dostępu do zasobu.

**Rozwiązanie:**
- Sprawdź poprawność adresu URL i dostępność zasobu.
- Przeanalizuj konsolę przeglądarki i sprawdź konfigurację sieci lub CORS.

---

**Wskazówka ogólna:**  
Zaleca się wdrożenie warstw walidacji danych, rozszerzenie logowania błędów oraz używanie mechanizmów try-catch i fallbacków, by zapewnić stabilność aplikacji nawet w przypadku nieprzewidzianych błędów.

"""
