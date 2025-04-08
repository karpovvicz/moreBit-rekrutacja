<?php 

*główna metoda generująca kalendarz w HTML

class KalendarzGenerator 
  {
    private int $month;
    private int $year;

    public function __construct(int $month, int $year)
    {
      if ($month < 1 || $month > 12) {
        trow new InvalidArgumentException("Podaj zakres miesięczny 1-12.");
    
    }

      $this->month = $month;
      $this->year = $year; 

    }

@return string 


public function render(): string 
{
    $firstDayOfMonth = new DateTimeImmutable("{$this->year}-{$this->month}-01");
    $daysInMonth = (int) $firstDayOfMonth->format('t');
    $startWeekDay = (int) $firstDayOfMonth->format('N'); // 1 = Poniedziałek, 7 = Niedziela

    $html = '<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; text-align: center;">';
    $html .= $this->renderHeader();
    $html .= '<tr>';

    // Puste wiersze przed 1 miesiąca
    for ($i = 1; $i < $startWeekDay; $i++) {
        $html .= '<td></td>';
    }

    for ($day = 1; $day <= $daysInMonth; $day++) {
        $currentDate = new DateTimeImmutable("{$this->year}-{$this->month}-{$day}");
        $dayOfWeek = (int) $currentDate->format('N');

        $style = $dayOfWeek === 7 ? ' style="color: red;"' : ''; // Zaznacz niedzielę na czerwono

        $html .= "<td{$style}>{$day}</td>";

        if ($dayOfWeek === 7) {
            $html .= '</tr>';
            if ($day !== $daysInMonth) {
                $html .= '<tr>';
            }
        }
    }

    // Zamknij wiersze na koniec miesiąca
    $lastDayOfMonth = new DateTimeImmutable("{$this->year}-{$this->month}-{$daysInMonth}");
    if ((int)$lastDayOfMonth->format('N') !== 7) {
        $html .= '</tr>';
    }

    $html .= '</table>';
    return $html;
}


  //Render nagłówka kalendarza - dni tygodnia 


      @return string 


      private function renderHeadcer(): string 
      {
      $days = ['Pon', 'Wto', 'Śro', 'Czw', 'Pią', 'Sob', 'Nie'];
      $html = '<tr>';
        foreach ($days as $index => $day) {
        $style = $index === 6 ? ' style="color: red;"' : '';
        $html .= "<th{$style}>{$day}</th>th>";
      
        }

        return $html;
      }

$calendar = new KalendarzGenerator (12, 2024); // Grudzień 2024 - jak w poleceniu zadania 
        echo $calendar->render();
   






           
