<?php

declare(strict_types=1);

/**
 * Klasa bazowa dla wszystkich dokumentów w systemie
 */
abstract class Dokument
{
    private int $id;
    private string $tytul;
    private string $tresc;
    private DateTime $dataUtworzenia;
    private ?DateTime $dataModyfikacji = null;
    private int $autorId;
    private Status $status;
    private array $historia = [];

    public function __construct(string $tytul, string $tresc, int $autorId)
    {
        $this->tytul = $tytul;
        $this->tresc = $tresc;
        $this->autorId = $autorId;
        $this->dataUtworzenia = new DateTime();
        $this->status = Status::UTWORZONY;
        $this->dodajHistorie("Dokument utworzony przez pracownika ID: {$autorId}");
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTytul(): string
    {
        return $this->tytul;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): void
    {
        $this->status = $status;
        $this->dataModyfikacji = new DateTime();
    }

    protected function dodajHistorie(string $opis): void
    {
        $wpis = [
            'data' => new DateTime(),
            'opis' => $opis
        ];
        $this->historia[] = $wpis;
    }

    public function getHistoria(): array
    {
        return $this->historia;
    }
}

/**
 * Enum reprezentujący możliwe statusy dokumentu
 */
enum Status: string
{
    case UTWORZONY = 'utworzony';
    case DO_AKCEPTACJI_KIEROWNIK = 'do_akceptacji_kierownik';
    case ZAAKCEPTOWANY_KIEROWNIK = 'zaakceptowany_kierownik';
    case ODRZUCONY_KIEROWNIK = 'odrzucony_kierownik';
    case DO_ZATWIERDZENIA_DYREKTOR = 'do_zatwierdzenia_dyrektor';
    case ZATWIERDZONY_DYREKTOR = 'zatwierdzony_dyrektor';
    case ODRZUCONY_DYREKTOR = 'odrzucony_dyrektor';
    case WYCOFANY = 'wycofany';
}

/**
 * Interface dla walidatorów
 */
interface WalidatorDokumentu
{
    public function waliduj(Dokument $dokument): bool;
}

/**
 * Interface dla wykonawców akcji na dokumencie
 */
interface AkcjaDokumentu
{
    public function wykonaj(Dokument $dokument, int $pracownikId, ?string $komentarz = null): bool;
}

/**
 * Klasa reprezentująca pismo wychodzące
 */
class PismoWychodzace extends Dokument
{
    private ?string $numerWychodzacy = null;
    private array $zalaczniki = [];

    public function przydzielNumerWychodzacy(string $numer): void
    {
        $this->numerWychodzacy = $numer;
        $this->dodajHistorie("Przydzielono numer wychodzący: {$numer}");
    }

    public function dodajZalacznik(string $nazwaPliku, string $sciezka): void
    {
        $this->zalaczniki[] = [
            'nazwa' => $nazwaPliku,
            'sciezka' => $sciezka,
            'data_dodania' => new DateTime()
        ];
        $this->dodajHistorie("Dodano załącznik: {$nazwaPliku}");
    }
}

**
 * Interfejs reprezentujący pracownika
 */
interface Pracownik
{
    public function getId(): int;
    public function getCzyDostepny(): bool;
    public function getZastepca(): ?int;
}

/**
 * Klasa implementująca pracownika
 */
class PracownikImpl implements Pracownik
{
    private int $id;
    private string $imie;
    private string $nazwisko;
    private string $stanowisko;
    private bool $czyDostepny;
    private ?int $zastepca;

    public function __construct(
        int $id,
        string $imie,
        string $nazwisko,
        string $stanowisko,
        bool $czyDostepny = true,
        ?int $zastepca = null
    ) {
        $this->id = $id;
        $this->imie = $imie;
        $this->nazwisko = $nazwisko;
        $this->stanowisko = $stanowisko;
        $this->czyDostepny = $czyDostepny;
        $this->zastepca = $zastepca;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCzyDostepny(): bool
    {
        return $this->czyDostepny;
    }

    public function getZastepca(): ?int
    {
        return $this->zastepca;
    }
}

/**
 * Serwis zarządzający pracownikami
 */
class PracownikSerwis
{
    private PracownikRepository $pracownikRepository;

    public function __construct(PracownikRepository $pracownikRepository)
    {
        $this->pracownikRepository = $pracownikRepository;
    }

    public function pobierzPracownikaLubZastepce(int $pracownikId): ?Pracownik
    {
        $pracownik = $this->pracownikRepository->pobierzPrzezId($pracownikId);
        
        if ($pracownik === null) {
            return null;
        }
        
        if ($pracownik->getCzyDostepny()) {
            return $pracownik;
        }
        
        $zastepca = $pracownik->getZastepca();
        if ($zastepca === null) {
            return null;
        }
        
        return $this->pracownikRepository->pobierzPrzezId($zastepca);
    }
}

/**
 * Interfejs repozytorium pracowników
 */
interface PracownikRepository
{
    public function pobierzPrzezId(int $id): ?Pracownik;
    public function pobierzKierownikow(): array;
    public function pobierzDyrektorow(): array;
}

/**
 * Akcja akceptacji pisma przez kierownika
 */
class AkceptujKierownikAkcja implements AkcjaDokumentu
{
    private PracownikSerwis $pracownikSerwis;
    private PracownikRepository $pracownikRepository;

    public function __construct(PracownikSerwis $pracownikSerwis, PracownikRepository $pracownikRepository)
    {
        $this->pracownikSerwis = $pracownikSerwis;
        $this->pracownikRepository = $pracownikRepository;
    }

    public function wykonaj(Dokument $dokument, int $pracownikId, ?string $komentarz = null): bool
    {
        if (!($dokument instanceof PismoWychodzace)) {
            throw new InvalidArgumentException("Dokument musi być typu PismoWychodzace");
        }
        
        if ($dokument->getStatus() !== Status::DO_AKCEPTACJI_KIEROWNIK) {
            throw new LogicException("Nieprawidłowy status dokumentu dla tej akcji");
        }
        
        // Sprawdź czy pracownik jest kierownikiem lub zastępcą
        $kierownicy = $this->pracownikRepository->pobierzKierownikow();
        $aktualnyPracownik = $this->pracownikSerwis->pobierzPracownikaLubZastepce($pracownikId);
        
        $czyUprawniony = false;
        foreach ($kierownicy as $kierownik) {
            if ($kierownik->getId() === $pracownikId) {
                $czyUprawniony = true;
                break;
            }
            
            // Sprawdź czy pracownik jest zastępcą
            if ($kierownik->getZastepca() === $pracownikId && !$kierownik->getCzyDostepny()) {
                $czyUprawniony = true;
                break;
            }
        }
        
        if (!$czyUprawniony) {
            throw new UnauthorizedException("Brak uprawnień do wykonania tej akcji");
        }

        $dokument->setStatus(Status::ZAAKCEPTOWANY_KIEROWNIK);
        
        // Automatyczne przesłanie do dyrektora
        $dokument->setStatus(Status::DO_ZATWIERDZENIA_DYREKTOR);
        
        return true;
    }
}

/**
 * Akcja odrzucenia pisma przez kierownika
 */
class OdrzucKierownikAkcja implements AkcjaDokumentu
{
    private PracownikSerwis $pracownikSerwis;
    private PracownikRepository $pracownikRepository;

    public function __construct(PracownikSerwis $pracownikSerwis, PracownikRepository $pracownikRepository)
    {
        $this->pracownikSerwis = $pracownikSerwis;
        $this->pracownikRepository = $pracownikRepository;
    }

    public function wykonaj(Dokument $dokument, int $pracownikId, ?string $komentarz = null): bool
    {
        if (!($dokument instanceof PismoWychodzace)) {
            throw new InvalidArgumentException("Dokument musi być typu PismoWychodzace");
        }
        
        if ($dokument->getStatus() !== Status::DO_AKCEPTACJI_KIEROWNIK) {
            throw new LogicException("Nieprawidłowy status dokumentu dla tej akcji");
        }
        
        // Sprawdź czy pracownik jest kierownikiem lub zastępcą
        $kierownicy = $this->pracownikRepository->pobierzKierownikow();
        $aktualnyPracownik = $this->pracownikSerwis->pobierzPracownikaLubZastepce($pracownikId);
        
        $czyUprawniony = false;
        foreach ($kierownicy as $kierownik) {
            if ($kierownik->getId() === $pracownikId) {
                $czyUprawniony = true;
                break;
            }
            
            // Sprawdź czy pracownik jest zastępcą
            if ($kierownik->getZastepca() === $pracownikId && !$kierownik->getCzyDostepny()) {
                $czyUprawniony = true;
                break;
            }
        }
        
        if (!$czyUprawniony) {
            throw new UnauthorizedException("Brak uprawnień do wykonania tej akcji");
        }

        $dokument->setStatus(Status::ODRZUCONY_KIEROWNIK);
        
        return true;
    }
}


/**
 * Klasa obsługująca obieg dokumentów
 */
class ObiegDokumentow
{
    private array $akcje;
    
    public function __construct(array $akcje)
    {
        $this->akcje = $akcje;
    }
    
    public function wykonajAkcje(string $nazwaAkcji, Dokument $dokument, int $pracownikId, ?string $komentarz = null): bool
    {
        if (!isset($this->akcje[$nazwaAkcji])) {
            throw new InvalidArgumentException("Nieznana akcja: {$nazwaAkcji}");
        }
        
        $akcja = $this->akcje[$nazwaAkcji];
        return $akcja->wykonaj($dokument, $pracownikId, $komentarz);
    }
}

/**
 * Przykład użycia systemu obiegu dokumentów
 */
function przykladUzycia(): void
{
    // Tworzenie repozytoriów i serwisów
    $pracownikRepository = new PracownikRepositoryImpl(); // Implementacja interfejsu
    $pracownikSerwis = new PracownikSerwis($pracownikRepository);

    // Tworzenie akcji
    $akcje = [
        'wyslij_do_akceptacji_kierownik' => new WyslijDoAkceptacjiKierownikAkcja($pracownikSerwis),
        'akceptuj_kierownik' => new AkceptujKierownikAkcja($pracownikSerwis, $pracownikRepository),
        'odrzuc_kierownik' => new OdrzucKierownikAkcja($pracownikSerwis, $pracownikRepository),
        'zatwierdz_dyrektor' => new ZatwierdzDyrektorAkcja($pracownikSerwis, $pracownikRepository),
        // Dodatkowe akcje...
    ];

    // Inicjalizacja systemu obiegu
    $obiegDokumentow = new ObiegDokumentow($akcje);

    // Przykład obiegu dokumentu
    $autorId = 1; // ID pracownika będącego autorem pisma
    $pismo = new PismoWychodzace('Przykładowe pismo wychodzące', 'Treść pisma...', $autorId);

    // 1. Wysłanie pisma do akceptacji przez kierownika
    $obiegDokumentow->wykonajAkcje('wyslij_do_akceptacji_kierownik', $pismo, $autorId);

    // 2. Kierownik (lub jego zastępca) akceptuje pismo
    $kierownikId = 2; // ID kierownika
    $obiegDokumentow->wykonajAkcje('akceptuj_kierownik', $pismo, $kierownikId, 'Akceptuję pismo');

    // 3. Dyrektor (lub jego zastępca) zatwierdza pismo
    $dyrektorId = 3; // ID dyrektora
    $obiegDokumentow->wykonajAkcje('zatwierdz_dyrektor', $pismo, $dyrektorId, 'Zatwierdzam pismo');

    // Wyświetlenie historii obiegu pisma
    $historia = $pismo->getHistoria();
    foreach ($historia as $wpis) {
        echo "{$wpis['data']->format('Y-m-d H:i:s')} - {$wpis['opis']}\n";
    }
}

