<?php
// config.php - CONFIGURAÇÃO CORRIGIDA PARA SUBPASTAS

// Configurações do banco de dados
$db_host = '127.0.0.1';
$db_name = 'sistema';
$db_user = 'root';
$db_pass = '';

// DETECTAR AUTOMATICAMENTE O CAMINHO BASE DO PROJETO
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
define('BASE_URL', rtrim($scriptName, '/'));

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", 
        $db_user, 
        $db_pass, 
        $options
    );
} catch (PDOException $e) {
    die("
        <h1>Erro ao conectar no banco de dados</h1>
        <p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
        <h3>Checklist:</h3>
        <ol>
            <li>O XAMPP está rodando?</li>
            <li>O Apache e MySQL estão ativos?</li>
            <li>O banco 'sistema' foi criado no phpMyAdmin?</li>
            <li>Execute o arquivo criar_banco.sql</li>
        </ol>
        <p><strong>Tente acessar:</strong> <a href='http://localhost/phpmyadmin'>phpMyAdmin</a></p>
    ");
}

// Função para gerar URLs corretas
function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}