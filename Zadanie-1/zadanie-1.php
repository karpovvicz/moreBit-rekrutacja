<?php 


//Ustawienie nagłówka UTF-8
header('Content-Type: text/html; charset=UTF-8');

//Główna metoda generująca kalendarz w HTML

class KalendarzGenerator 
  {
    private int $month;
    private int $year;

    public function __construct(int $month, int $year)
    {
      if ($month < 1 || $month > 12) {
        throw new InvalidArgumentException("Podaj zakres miesięczny 1-12.");
    
    }

      $this->month = $month;
      $this->year = $year; 

    }

//@return string 


public function render(): string 
{
    $firstDayOfMonth = new DateTimeImmutable("{$this->year}-{$this->month}-01");
    $daysInMonth = (int) $firstDayOfMonth->format('t');
    $startWeekDay = (int) $firstDayOfMonth->format('N'); // 1 = Poniedziałek, 7 = Niedziela


    $monthName = $this->getMonthName(); // 
    $html = "<h2>{$monthName} {$this->year}</h2>\n";
    $html .= '<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; text-align: center;">';

    $html .= $this->renderHeader();
    $html .= '<tr>' . "\n";

    // Puste wiersze przed 1 miesiąca
    for ($i = 1; $i < $startWeekDay; $i++) {
        $html .= '<td></td>' . "\n";
    }

    for ($day = 1; $day <= $daysInMonth; $day++) {
        $currentDate = new DateTimeImmutable("{$this->year}-{$this->month}-{$day}");
        $dayOfWeek = (int) $currentDate->format('N');

        $style = $dayOfWeek === 7 ? ' style="color: red;"' : ''; // Zaznacz niedzielę na czerwono

        $html .= "<td{$style}>{$day}</td>\n";

        if ($dayOfWeek === 7) {
            $html .= '</tr>' . "\n";
            if ($day !== $daysInMonth) {
                $html .= '<tr>' . "\n";
            }
        }
    }

    // Zamknij wiersze na koniec miesiąca
    $lastDayOfMonth = new DateTimeImmutable("{$this->year}-{$this->month}-{$daysInMonth}");
    if ((int)$lastDayOfMonth->format('N') !== 7) {
        $html .= '</tr>' . "\n";
    }

     $html .= '</table>' . "\n";
    return $html;
}


  //Render nagłówka kalendarza - dni tygodnia 


     // @return string 


      private function renderHeader(): string 
      {
      $days = ['Pon', 'Wto', 'Śro', 'Czw', 'Pią', 'Sob', 'Nie'];
      $html = '<tr>' . "\n";
        foreach ($days as $index => $day) {
        $style = $index === 6 ? ' style="color: red;"' : '';
        $html .= "<th{$style}>{$day}</th>\n";
      
        }

        return $html;
      }
  

// Metoda zwracająca nazwę miesiąca 
    private function getMonthName(): string
    {
        $months = [
            1 => 'Styczeń', 2 => 'Luty', 3 => 'Marzec', 4 => 'Kwiecień',
            5 => 'Maj', 6 => 'Czerwiec', 7 => 'Lipiec', 8 => 'Sierpień',
            9 => 'Wrzesień', 10 => 'Październik', 11 => 'Listopad', 12 => 'Grudzień'
        ];

        return $months[$this->month] ?? 'Nieznany';
    }
}


// Tworzenie strony HTML z deklaracją UTF-8
echo '<!DOCTYPE html>' . "\n";
echo '<html lang="pl">' . "\n";
echo '<head>' . "\n";
echo '    <meta charset="UTF-8">' . "\n";
    '<title>Kalendarz</title>' . "\n";
echo '    <title>Kalendarz</title>' . "\n";
echo '</head>' . "\n";
echo '<body>' . "\n";


$calendar = new KalendarzGenerator (12, 2024); // Grudzień 2024 - jak w poleceniu zadania 
        echo $calendar->render();


echo '</body>' . "\n";
echo '</html>' . "\n"; // Zamknięcie dokumentu HTML





           
