<?php
session_start();

if (!isset($_SESSION['nome']) || !isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$nome = htmlspecialchars($_SESSION['nome']);
$email = htmlspecialchars($_SESSION['email']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Área do Usuário</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg, #20002c 0%, #cbb4d4 100%);
            color: #fff;
        }

        .container {
            background: rgba(255, 255, 255, 0.1);
            padding: 50px 60px;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
            text-align: center;
            width: 380px;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        h2 {
            font-size: 1.9em;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .info {
            text-align: left;
            margin-top: 20px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            padding: 20px;
            font-size: 1.1em;
        }

        .info p {
            margin: 10px 0;
        }

        strong {
            color: #fff;
        }

        button {
            margin-top: 25px;
            padding: 12px 35px;
            background: #fff;
            color: #9b0088;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        button:hover {
            background: #9b0088;
            color: #fff;
            box-shadow: 0 0 10px #fff;
        }

        
    </style>
</head>
<body>
    <div class="container">
        <h2>Bem-vindo, <?= $nome ?></h2>

        <div class="info">
            <p><strong>Nome:</strong> <?= $nome ?></p>
            <p><strong>E-mail:</strong> <?= $email ?></p>
        </div>

        <form method="post" action="logout.php">
            <button type="submit">Logout</button>
        </form>
    </div>
</body>
</html>
