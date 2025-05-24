<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$resultados = unserialize($_POST['resultados']);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue('A1', 'Identificador');
$sheet->setCellValue('B1', 'bateria = "F"');
$sheet->setCellValue('C1', 'bateria != "F"');
$sheet->setCellValue('D1', 'Total de Linhas');

$row = 2;
foreach ($resultados as $identificador => $dados) {
    $sheet->setCellValue('A' . $row, $identificador);
    $sheet->setCellValue('B' . $row, $dados['bateriaF']);
    $sheet->setCellValue('C' . $row, $dados['bateriaOutro']);
    $sheet->setCellValue('D' . $row, $dados['totalLinhas']);
    $row++;
}

$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Consolidado_' . date('d_m_Y') . '.xlsx"');
header('Cache-Control: max-age=0');
$writer->save('php://output');
exit;
?>