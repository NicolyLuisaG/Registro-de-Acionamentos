<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Exibe o formulário para selecionar os arquivos
    echo '<div style="text-align: left; margin-bottom: 20px;">';
    echo '<a href="principal.php">';
        echo '<img src="\img\logo.png" alt="Logo" style="width: 100px; height: auto;">';
    echo '</a>';
    echo '</div>';
    $v=rand(2, 5);
    echo '
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <title>Selecionar Arquivos para Somar</title>
        
        <style>
            body {
                background-color: #003459;
                color: white;
                font-family: Arial, sans-serif;
                padding: 20px;
                text-align: center;
            }
            h1 {
                font-size: 24px;
            }
            form {
                margin-top: 50px;
                padding: 20px;
                background-color: #005b7f;
                border-radius: 10px;
                display: inline-block;
            }
            input[type="file"] {
                margin: 10px 0;
                padding: 10px;
                border-radius: 5px;
                background-color: #ffffff;
                border: 1px solid #ddd;
            }
            button {
                background-color: #006f8e;
                color: white;
                border: none;
                padding: 10px 20px;
                cursor: pointer;
                font-size: 16px;
                border-radius: 5px;
            }
            button:hover {
                background-color: #004c66;
            }
            .loading {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                text-align: center;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            }
            .loading img {
                width: 100px;
                height: auto;
            }
        </style>
    </head>
    <body>
        <h1>Selecione os Arquivos para Somar</h1>
        <form method="post" enctype="multipart/form-data" onsubmit="mostrarCarregando();">
            <input type="file" name="arquivos[]" multiple>
            <br><br>
            <button type="submit">Somar Arquivos</button>
        </form>

        <!-- Div de carregamento -->
           <div id="loading" class="loading">
                 <img src="\img\carregando'.$v.'.gif" alt="Carregando...">
           </div>

        <script>
            function mostrarCarregando() {
                document.getElementById("loading").style.display = "flex";
            }
        </script>
    </body>
    </html>';
    exit;
}
?>

<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Verifica se arquivos foram enviados
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['arquivos']['tmp_name'])) {
    $arquivos = $_FILES['arquivos']['tmp_name'];
    $resultadosConsolidados = [];

    // Processa cada arquivo
    foreach ($arquivos as $arquivo) {
        // Carrega o arquivo Excel
        $spreadsheet = IOFactory::load($arquivo);
        $sheet = $spreadsheet->getActiveSheet();

        // Itera sobre as linhas do arquivo (ignorando o cabeçalho)
        foreach ($sheet->getRowIterator(2) as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            // Extrai os valores da linha
            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[] = $cell->getValue();
            }

            // Identificador (coluna A)
            $identificador = $rowData[0];

            // Valores das colunas B, C e D
            $bateriaF = $rowData[1];
            $bateriaOutro = $rowData[2];
            $totalLinhas = $rowData[3];

            // Se o identificador já existe, soma os valores
            if (isset($resultadosConsolidados[$identificador])) {
                $resultadosConsolidados[$identificador]['bateriaF'] += $bateriaF;
                $resultadosConsolidados[$identificador]['bateriaOutro'] += $bateriaOutro;
                $resultadosConsolidados[$identificador]['totalLinhas'] += $totalLinhas;
            } else {
                // Se não existe, cria uma nova entrada
                $resultadosConsolidados[$identificador] = [
                    'bateriaF' => $bateriaF,
                    'bateriaOutro' => $bateriaOutro,
                    'totalLinhas' => $totalLinhas,
                ];
            }
        }
    }

    ksort($resultadosConsolidados);
    echo '<div style="text-align: left; margin-bottom: 20px;">';
            echo '<a href="aut_log_conc.php">';
             echo '<img src="\img\logo.png" alt="Logo" style="width: 100px; height: auto;">';
            echo '</a>';
        echo '</div>';
    echo '
    <style>
        body {
            background-color: #003459;
            color: white;
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        .bateria-f { background-color: red; color: white; }
        .bateria-outro { background-color: green; color: white; }
        button {
            background-color: #006f8e;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
        }
        button:hover {
            background-color: #004c66;
        }
    </style>';

    echo '<table>';
    echo '<tr><th>Identificador</th><th>bateria = "F"</th><th>bateria != "F"</th><th>Total de Linhas</th></tr>';
    foreach ($resultadosConsolidados as $identificador => $dados) {
    
        $bateriaFClass = $dados['bateriaF'] > 0 ? 'bateria-f' : '';
        $bateriaOutroClass = $dados['bateriaOutro'] > 0 ? 'bateria-outro' : '';

        echo '<tr>';
        echo '<td>' . htmlspecialchars($identificador) . '</td>';
        echo '<td class="' . $bateriaFClass . '">' . htmlspecialchars($dados['bateriaF']) . '</td>';
        echo '<td class="' . $bateriaOutroClass . '">' . htmlspecialchars($dados['bateriaOutro']) . '</td>';
        echo '<td>' . htmlspecialchars($dados['totalLinhas']) . '</td>';
        echo '</tr>';
    }
    echo '</table>';

    echo '<br><br>';
    echo '<form method="post" action="download_excel_conc.php" align="center">';
    echo '<input type="hidden" name="resultados" value="' . htmlspecialchars(serialize($resultadosConsolidados)) . '">';
    echo '<button type="submit">Gerar Excel</button>';
    echo '</form>';
} else {
    echo "Nenhum arquivo selecionado.";
}
?>
