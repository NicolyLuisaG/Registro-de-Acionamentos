<?php 
// inclui o autoload do Composer para carregar as dependências necessárias
require 'vendor/autoload.php';

// importa as classes do PhpSpreadsheet para poder manipular arquivos Excel
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// função que vai processar o arquivo Excel e retornar os resultados
function processarArquivo($arquivo, $colunaAgrupamento = 'Identificação', $colunaBateria = 'Bateria') {
    try {
        // tenta carregar o arquivo Excel usando o PhpSpreadsheet
        $spreadsheet = IOFactory::load($arquivo);
        $worksheet = $spreadsheet->getActiveSheet(); // pega a planilha ativa

        // cria um array para armazenar o cabeçalho
        $header = [];
        // pega a primeira linha (cabeçalho)
        $headerRow = $worksheet->getRowIterator(1)->current();  
        $cellIterator = $headerRow->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false); // inclui células vazias na iteração

        // aqui estamos montando o array com os nomes das colunas
        foreach ($cellIterator as $cell) {
            $header[] = trim($cell->getValue()); // salva o nome da coluna no array, removendo espaços extras
        }

        // array onde vamos salvar os resultados agrupados
        $resultados = [];

        // começa a iteração pelas linhas da planilha, começando da segunda (porque a primeira é o cabeçalho)
        foreach ($worksheet->getRowIterator(2) as $row) {  
            $rowValues = []; // array temporário para armazenar os valores da linha
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false); // inclui células vazias na iteração

            // percorre todas as células da linha
            foreach ($cellIterator as $cell) {
                $rowValues[] = trim($cell->getValue()); // adiciona o valor da célula ao array, removendo espaços
            }

            // a coluna "Identificação" está na coluna J (índice 9)
            $identificador = ltrim($rowValues[9], '0'); // tira o zero à esquerda
            $bateria = $rowValues[array_search($colunaBateria, $header)]; // encontra a posição da coluna de "Bateria" e pega o valor da célula

            // verifica se já existe o identificador nos resultados
            if (!isset($resultados[$identificador])) {
                // se não existir, cria uma nova entrada para esse identificador
                $resultados[$identificador] = [
                    'total' => 0,  // vai contar o total de linhas
                    'colunaf' => 0,  // vai contar as linhas onde bateria = "F"
                    'bateria' => 0   // vai contar as linhas onde bateria != "F"
                ];
            }

            // incrementa o total de linhas para esse identificador
            $resultados[$identificador]['total']++;

            // verifica o valor da coluna "Bateria" e aumenta a contagem adequada
            if (strtolower($bateria) == 'f') {
                $resultados[$identificador]['colunaf']++;  // se for "f", conta aqui
            } else {
                $resultados[$identificador]['bateria']++;  // se não for "f", conta aqui
            }
        }

        return $resultados; // retorna os resultados processados
    } catch (Exception $e) {
        // se acontecer algum erro ao tentar processar o arquivo, vai lançar uma exceção com a mensagem de erro
        throw new Exception("Erro ao processar o arquivo '$arquivo': {$e->getMessage()}");
    }
}

// se o método da requisição for POST (significa que um formulário foi enviado)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // pega os arquivos enviados no formulário
    $arquivos = $_FILES['arquivos']['tmp_name'];
    $resultadosTotais = []; // array para armazenar o total agrupado dos resultados

    // para cada arquivo enviado
    foreach ($arquivos as $arquivo) {
        try {
            // processa o arquivo
            $resultados = processarArquivo($arquivo);

            // agrupa os resultados de cada arquivo
            foreach ($resultados as $identificador => $dados) {
                // se ainda não existe esse identificador, cria a entrada
                if (!isset($resultadosTotais[$identificador])) {
                    $resultadosTotais[$identificador] = [
                        'total' => 0,
                        'colunaf' => 0,
                        'bateria' => 0
                    ];
                }

                // soma os resultados de cada arquivo para o identificador
                $resultadosTotais[$identificador]['total'] += $dados['total'];
                $resultadosTotais[$identificador]['colunaf'] += $dados['colunaf'];
                $resultadosTotais[$identificador]['bateria'] += $dados['bateria'];
            }

        } catch (Exception $e) {
            // se der erro ao processar algum arquivo, exibe a mensagem de erro
            echo 'Erro ao processar o arquivo: ' . $e->getMessage();
        }
    }

    // organiza os resultados de forma crescente pelo identificador
    ksort($resultadosTotais);

    // exibe os resultados em uma tabela HTML
    if (!empty($resultadosTotais)) {
        // estilo CSS para a página
        echo '<style>';
            echo 'body { background-color: #003459; color: white; font-family: Arial, sans-serif; padding: 20px; }';
            echo 'table { width: 100%; border-collapse: collapse; margin-top: 20px; }';
            echo 'th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }';
            echo '.bateria-f { background-color: red; color: white; }';
            echo '.bateria-outro { background-color: green; color: white; }';
            echo 'button { background-color: #006f8e; color: white; border: none; padding: 10px 20px; cursor: pointer; font-size: 16px; border-radius: 5px; }';
            echo 'button:hover { background-color: #004c66; }';
            echo 'input[type="file"] { margin: 10px 0; padding: 10px; border-radius: 5px; background-color: #ffffff; border: 1px solid #ddd; }';
        echo '</style>';

        // logo da página
        echo '<div style="text-align: left; margin-bottom: 20px;">';
            echo '<a href="aut_log.php">';
             echo '<img src="\img\logo.png" alt="Logo" style="width: 100px; height: auto;">';
            echo '</a>';
        echo '</div>';

        // tabela com os resultados
        echo '<table>';
            echo '<tr><th>Identificador</th><th>bateria = "F"</th><th>bateria != "F"</th><th>Total de Linhas</th></tr>';
            // para cada identificador, exibe as contagens em linhas
            foreach ($resultadosTotais as $identificador => $dados) {
                // se tiver mais de uma linha com bateria "F", pinta de vermelho
                $bateriaClassF = $dados['colunaf'] > 0 ? 'bateria-f' : '';
                // se tiver mais de uma linha com bateria diferente de "F", pinta de verde
                $bateriaClassOutro = $dados['bateria'] > 0 ? 'bateria-outro' : '';

                echo '<tr>';
                    echo '<td>' . htmlspecialchars($identificador) . '</td>';
                    echo '<td class="' . $bateriaClassF . '">' . htmlspecialchars($dados['colunaf']) . '</td>';
                    echo '<td class="' . $bateriaClassOutro . '">' . htmlspecialchars($dados['bateria']) . '</td>';
                    echo '<td>' . htmlspecialchars($dados['total']) . '</td>';
                echo '</tr>';
            }
        echo '</table>';

        // botão para gerar o arquivo Excel com os resultados
        echo '<br><br>';
        echo '<form method="post" action="download_excel.php" align="center">';
            echo '<input type="hidden" name="resultados" value="' . htmlspecialchars(serialize($resultadosTotais)) . '">';
            echo '<button type="submit">Gerar Excel</button>';
        echo '</form>';
    } else {
        echo 'Nenhum dado encontrado.'; // se não encontrou nenhum dado
    }
} else {
    // caso a requisição não seja POST (ou seja, a página foi acessada sem enviar formulário)
    echo '<style>';
        echo 'body { background-color: #003459; color: white; font-family: Arial, sans-serif; padding: 20px; text-align: center; }';
        echo 'h1 { font-size: 24px; }';
        echo 'form { margin-top: 50px; padding: 20px; background-color: #005b7f; border-radius: 10px; display: inline-block; }';
        echo 'input[type="file"] { margin: 10px 0; padding: 10px; border-radius: 5px; background-color: #ffffff; border: 1px solid #ddd; }';
        echo 'button { background-color: #006f8e; color: white; border: none; padding: 10px 20px; cursor: pointer; font-size: 16px; border-radius: 5px; }';
        echo 'button:hover { background-color: #004c66; }';
        echo '.loading { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); text-align: center; justify-content: center; align-items: center; z-index: 9999; }';
        echo '.loading img { width: 100px; height: auto; }';
    echo '</style>';

    // logo da página
    echo '<div style="text-align: left; margin-bottom: 30px;">';
    echo '<a href="principal.php">';
        echo '<img src="\img\logo.png" alt="Logo" style="width: 100px; height: auto;">';
    echo '</a>';
    echo '</div>';

    // formulário para o usuário selecionar os arquivos
    echo '<h1>Selecione os Arquivos para Processar</h1>';
    echo '<form method="post" enctype="multipart/form-data" onsubmit="mostrarCarregando();">'; 
        echo '<input type="file" name="arquivos[]" multiple onchange="atualizarQuantidade();">';
        echo '</br></br><span id="quantidade-arquivos">Nenhum arquivo selecionado</span>';
        echo '</br></br><button type="submit">Processar Arquivos</button>';
    echo '</form>';
    
    // div para mostrar o carregamento
    echo '<div id="loading" class="loading">';
    echo '<img src="\img\carregando' . rand(2, 5) . '.gif" alt="Carregando...">';
    echo '</div>';
    
    // scripts JS para atualizar o contador de arquivos e mostrar o carregamento
    echo '<script>';
        echo 'function atualizarQuantidade() {'; 
        echo '  var input = document.querySelector("input[type=\'file\']");'; 
        echo '  var quantidade = input.files.length;';
        echo '  var quantidadeTexto = quantidade + " arquivos selecionados";';
        echo '  document.getElementById("quantidade-arquivos").textContent = quantidadeTexto;'; // atualiza o texto
        echo '}'; 

        echo 'function mostrarCarregando() {'; 
        echo '  document.getElementById("loading").style.display = "flex";'; // exibe o carregamento
        echo '}'; 
    echo '</script>';
}
?>
