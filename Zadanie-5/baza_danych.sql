-- Tabela przechowująca informacje o pracownikach
CREATE TABLE pracownicy (
    id INT AUTO_INCREMENT PRIMARY KEY,
    imie VARCHAR(50) NOT NULL,
    nazwisko VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    stanowisko VARCHAR(100) NOT NULL,
    rola ENUM('PRACOWNIK', 'KIEROWNIK', 'DYREKTOR', 'ADMINISTRATOR') NOT NULL,
    jest_dostepny BOOLEAN DEFAULT TRUE NOT NULL,
    data_utworzenia TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_modyfikacji TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela przechowująca informacje o urlopach pracowników
CREATE TABLE urlopy (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pracownik_id INT NOT NULL,
    data_od DATE NOT NULL,
    data_do DATE NOT NULL,
    typ_urlopu ENUM('WYPOCZYNKOWY', 'CHOROBOWY', 'OKOLICZNOSCIOWY', 'INNY') NOT NULL,
    status ENUM('OCZEKUJACY', 'ZATWIERDZONY', 'ODRZUCONY', 'ANULOWANY') NOT NULL,
    komentarz TEXT,
    data_utworzenia TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_modyfikacji TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pracownik_id) REFERENCES pracownicy(id) ON DELETE CASCADE
);

-- Tabela przechowująca informacje o zastępstwach pracowników
CREATE TABLE zastepstwa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pracownik_id INT NOT NULL,
    zastepca_id INT NOT NULL,
    data_od DATE NOT NULL,
    data_do DATE NOT NULL,
    powod VARCHAR(255),
    status ENUM('AKTYWNE', 'NIEAKTYWNE', 'ANULOWANE') NOT NULL DEFAULT 'AKTYWNE',
    data_utworzenia TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_modyfikacji TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pracownik_id) REFERENCES pracownicy(id) ON DELETE CASCADE,
    FOREIGN KEY (zastepca_id) REFERENCES pracownicy(id) ON DELETE CASCADE,
    CHECK (pracownik_id != zastepca_id)
);

-- Tabela przechowująca informacje o dokumentach
CREATE TABLE dokumenty (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tytul VARCHAR(255) NOT NULL,
    tresc TEXT NOT NULL,
    typ ENUM('PISMO_WYCHODZACE', 'PISMO_PRZYCHODZACE', 'WNIOSEK_URLOPOWY', 'INNY') NOT NULL,
    status VARCHAR(50) NOT NULL,
    autor_id INT NOT NULL,
    numer_wychodzacy VARCHAR(50),
    data_utworzenia TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_modyfikacji TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (autor_id) REFERENCES pracownicy(id)
);

-- Tabela przechowująca historię zmian statusów dokumentów
CREATE TABLE historia_dokumentow (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dokument_id INT NOT NULL,
    poprzedni_status VARCHAR(50),
    nowy_status VARCHAR(50) NOT NULL,
    pracownik_id INT NOT NULL,
    komentarz TEXT,
    czy_zastepstwo BOOLEAN DEFAULT FALSE NOT NULL,
    zastepowany_pracownik_id INT,
    data_zmiany TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dokument_id) REFERENCES dokumenty(id) ON DELETE CASCADE,
    FOREIGN KEY (pracownik_id) REFERENCES pracownicy(id),
    FOREIGN KEY (zastepowany_pracownik_id) REFERENCES pracownicy(id)
);

-- Tabela przechowująca załączniki do dokumentów
CREATE TABLE zalaczniki (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dokument_id INT NOT NULL,
    nazwa_pliku VARCHAR(255) NOT NULL,
    sciezka VARCHAR(255) NOT NULL,
    typ_pliku VARCHAR(100),
    rozmiar INT,
    data_dodania TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dokument_id) REFERENCES dokumenty(id) ON DELETE CASCADE
);

-- Procedura sprawdzająca czy pracownik jest dostępny lub ma aktywne zastępstwo
DELIMITER //
CREATE PROCEDURE sprawdz_dostepnosc_lub_zastepstwo(
    IN p_pracownik_id INT,
    OUT p_dostepny BOOLEAN,
    OUT p_zastepca_id INT
)
BEGIN
    DECLARE czy_dostepny BOOLEAN;
    DECLARE czy_ma_zastepstwo BOOLEAN;
    DECLARE id_zastepcy INT;
    
    -- Sprawdź czy pracownik jest dostępny
    SELECT jest_dostepny INTO czy_dostepny
    FROM pracownicy
    WHERE id = p_pracownik_id;
    
    -- Domyślnie brak zastępcy
    SET p_zastepca_id = NULL;
    
    IF czy_dostepny = TRUE THEN
        -- Pracownik jest dostępny
        SET p_dostepny = TRUE;
    ELSE
        -- Pracownik nie jest dostępny, szukamy aktywnego zastępstwa
        SELECT COUNT(*) > 0, zastepca_id INTO czy_ma_zastepstwo, id_zastepcy
        FROM zastepstwa
        WHERE pracownik_id = p_pracownik_id
          AND status = 'AKTYWNE'
          AND CURRENT_DATE BETWEEN data_od AND data_do
        LIMIT 1;
        
        IF czy_ma_zastepstwo = TRUE THEN
            -- Znaleziono zastępstwo
            SET p_dostepny = TRUE;
            SET p_zastepca_id = id_zastepcy;
        ELSE
            -- Brak dostępności i brak zastępstwa
            SET p_dostepny = FALSE;
        END IF;
    END IF;
END //
DELIMITER ;

-- Przykładowe zapełnienie tabel danymi
INSERT INTO pracownicy (imie, nazwisko, email, stanowisko, rola)
VALUES 
('Jan', 'Kowalski', 'jan.kowalski@firma.pl', 'Specjalista', 'PRACOWNIK'),
('Anna', 'Nowak', 'anna.nowak@firma.pl', 'Kierownik działu', 'KIEROWNIK'),
('Piotr', 'Wiśniewski', 'piotr.wisniewski@firma.pl', 'Dyrektor', 'DYREKTOR'),
('Katarzyna', 'Dąbrowska', 'katarzyna.dabrowska@firma.pl', 'Zastępca kierownika', 'KIEROWNIK'),
('Tomasz', 'Lewandowski', 'tomasz.lewandowski@firma.pl', 'Zastępca dyrektora', 'DYREKTOR');

-- Utworzenie zastępstwa
INSERT INTO zastepstwa (pracownik_id, zastepca_id, data_od, data_do, powod, status)
VALUES 
(2, 4, '2025-04-01', '2025-04-14', 'Urlop wypoczynkowy', 'AKTYWNE'),
(3, 5, '2025-04-05', '2025-04-12', 'Delegacja', 'AKTYWNE');

-- Utworzenie przykładowego pisma wychodzącego
INSERT INTO dokumenty (tytul, tresc, typ, status, autor_id)
VALUES 
('Oferta współpracy', 'Treść oferty współpracy...', 'PISMO_WYCHODZACE', 'UTWORZONY', 1);

-- Dodanie wpisu do historii dokumentu
INSERT INTO historia_dokumentow (dokument_id, poprzedni_status, nowy_status, pracownik_id, komentarz)
VALUES 
