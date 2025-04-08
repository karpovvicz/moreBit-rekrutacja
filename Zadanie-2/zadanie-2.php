<?php 

function konwertujExcelNumerycznie(string $cellAddress): string {
  //Uwierzytelnienie danych wejściowych
  if (!preg_match('/^([A-Z]+)(\d+)$/i', strtoupper($cellAddress), $matches)) {
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

try {
    echo convertExcelCellToNumeric('A9') . "\n";   //  wynik: 1.1
    echo convertExcelCellToNumeric('B2') ."\n";   // wynik: 2.2
    echo convertExcelCellToNumeric('A10') . "\n"; // wynik: 27.10
    echo convertExcelCellToNumeric('Z500') . "\n"; // wynik: 16384.1048576 (maksymalna komórka Excela)
} catch (InvalidArgumentException $e) {
    echo "Błąd: " . $e->getMessage();
}
