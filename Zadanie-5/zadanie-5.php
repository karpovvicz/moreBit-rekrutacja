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

