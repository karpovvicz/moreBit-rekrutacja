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
