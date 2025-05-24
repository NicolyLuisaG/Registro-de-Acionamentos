<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Tela Inicial</title>
    <style>
        body {
            background-color: #003459; 
            color: white;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .logo {
            margin-bottom: 20px;
        }
        .logo img {
            width: 150px;
            height: auto;
        }
        .buttons {
            display: flex;
            gap: 20px;
        }
        button {
            background-color: #006f8e; 
            color: white;
            border: none;
            padding: 15px 30px;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #004c66; 
        }
        footer {
            margin-top: 50px;
            color:rgb(255, 255, 255); 
            font-size: 14px;
        }
    </style>
</head>
<body>
    <!-- Logo no topo da tela -->
    <div class="logo">
        <img src="\img\logo.png" alt="Logo">
    </div>

    <!-- Butões -->
    <div class="buttons">
        <button onclick="window.location.href='aut_log.php'">Processar Arquivos</button>
        <button onclick="window.location.href='aut_log_conc.php'">Somar Arquivos</button>
    </div>

</body>
</html>