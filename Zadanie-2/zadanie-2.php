<?php 

function konwertujExcelNumerycznie(string $cellAddress): string {
  //Uwierzytelnienie danych wejściowych
  if (!preg_match('/[A-Z]+)(\d)$/i', strtoupper($cellAddress), $matches)) {
    throw new InvalidArgumentException("Niepoprawny format komórki: $cellAddress");
  }

[$_, $columnLetters, $rowNumbers] = $matches;

  $columnNumber = 0;
  $length = strlen($columnLetters);
  for ($i = 0; $i < $length; $i++) {
    $char = $columnLetters[$i];
    $columnNumber *= 26;
    $columnNumber += ord($char) - ord('A') +1;
  }

  return $columnNumber . '.' . (int)$rowNumber;
  
}
