<?php 

function konwertujExcelNumerycznie(string $cellAddress): string {
  //Uwierzytelnienie danych wejściowych
  if (!preg_match('/[A-Z]+)(\d)$/i', strtoupper($cellAddress), $matches)) {
    throw new InvalidArgumentException("Niepoprawny format komórki: $cellAddress");
  }



  
}
