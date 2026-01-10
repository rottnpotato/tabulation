<?php

require __DIR__.'/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$spreadsheet = IOFactory::load(__DIR__.'/tabulation.xlsx');

foreach ($spreadsheet->getSheetNames() as $sheetName) {
    echo "\n========== SHEET: {$sheetName} ==========\n\n";

    $sheet = $spreadsheet->getSheetByName($sheetName);
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();

    // Print headers (first few rows for context)
    echo "--- Headers/Data Preview ---\n";
    for ($row = 1; $row <= min(10, $highestRow); $row++) {
        $rowData = [];
        foreach (range('A', $highestColumn) as $col) {
            $cell = $sheet->getCell($col.$row);
            $value = $cell->getValue();
            if ($value !== null && $value !== '') {
                $rowData[] = "{$col}{$row}: ".$value;
            }
        }
        if (! empty($rowData)) {
            echo "Row {$row}: ".implode(' | ', $rowData)."\n";
        }
    }

    // Find and print all formulas
    echo "\n--- Formulas Found ---\n";
    $formulasFound = false;

    foreach ($sheet->getRowIterator() as $row) {
        foreach ($row->getCellIterator() as $cell) {
            $value = $cell->getValue();
            if (is_string($value) && str_starts_with($value, '=')) {
                $formulasFound = true;
                $coord = $cell->getCoordinate();
                $calculated = $cell->getCalculatedValue();
                echo "Cell {$coord}: {$value}\n";
                echo "  -> Calculates to: {$calculated}\n\n";
            }
        }
    }

    if (! $formulasFound) {
        echo "No formulas found in this sheet.\n";
    }
}

echo "\n========== END ==========\n";
