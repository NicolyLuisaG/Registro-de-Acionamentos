<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// recebe os dados via POST
$resultados = unserialize($_POST['resultados']);

// criação de um objeto Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// adiciona os cabeçalhos na primeira linha
$sheet->setCellValue('A1', 'Identificador');
$sheet->setCellValue('B1', 'bateria = "F"');
$sheet->setCellValue('C1', 'bateria != "F"');
$sheet->setCellValue('D1', 'Total de Linhas');

// adiciona os dados a partir da linha 2
$row = 2;
foreach ($resultados as $identificador => $dados) {
    $sheet->setCellValue('A' . $row, $identificador);
    $sheet->setCellValue('B' . $row, $dados['colunaf']);
    $sheet->setCellValue('C' . $row, $dados['bateria']);
    $sheet->setCellValue('D' . $row, $dados['total']);
    $row++;
}
$dia= date('d'.'_'.'m'.'_'.'Y');
// define o tipo de conteúdo para download de um arquivo Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Ac_'.$dia.'.xlsx"');
header('Cache-Control: max-age=0');

// cria o arquivo Excel e força o download
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
