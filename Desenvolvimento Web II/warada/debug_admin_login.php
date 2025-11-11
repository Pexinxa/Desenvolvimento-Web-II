<?php
// debug_admin_login.php - Coloque este arquivo na RAIZ do projeto
require_once __DIR__ . '/config.php';

echo "<h1>🔍 Diagnóstico do Login Admin</h1>";
echo "<style>body{font-family:monospace;} .ok{color:green;} .error{color:red;} .info{background:#f0f0f0;padding:10px;margin:10px 0;}</style>";

// 1. Verificar se o banco está conectado
echo "<h2>1. Conexão com Banco de Dados</h2>";
try {
    $pdo->query("SELECT 1");
    echo "<p class='ok'>✅ Conexão OK</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
    exit;
}

// 2. Verificar se a tabela usuario existe
echo "<h2>2. Tabela 'usuario'</h2>";
try {
    $count = $pdo->query("SELECT COUNT(*) FROM usuario")->fetchColumn();
    echo "<p class='ok'>✅ Tabela existe com $count usuário(s)</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Tabela não existe. Execute o schema.sql!</p>";
    exit;
}

// 3. Listar todos os usuários
echo "<h2>3. Usuários Cadastrados</h2>";
$stmt = $pdo->query("SELECT id, nome, email, nivel, senha FROM usuario");
$usuarios = $stmt->fetchAll();

if (empty($usuarios)) {
    echo "<p class='error'>❌ Nenhum usuário encontrado! Execute o schema.sql</p>";
    exit;
}

echo "<table border='1' cellpadding='10' style='border-collapse:collapse;width:100%;'>";
echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Nível</th><th>Hash da Senha</th></tr>";
foreach ($usuarios as $u) {
    $nivel_cor = $u['nivel'] === 'admin' ? 'style="background:#d4edda;"' : '';
    echo "<tr $nivel_cor>";
    echo "<td>{$u['id']}</td>";
    echo "<td>{$u['nome']}</td>";
    echo "<td><strong>{$u['email']}</strong></td>";
    echo "<td><strong>{$u['nivel']}</strong></td>";
    echo "<td style='font-size:10px;'>" . substr($u['senha'], 0, 30) . "...</td>";
    echo "</tr>";
}
echo "</table>";

// 4. Testar senhas comuns
echo "<h2>4. Teste de Senhas Comuns</h2>";
$senhas_testar = ['admin123', 'secret', '123456', 'admin', 'password'];

foreach ($usuarios as $u) {
    if ($u['nivel'] !== 'admin') continue;
    
    echo "<div class='info'>";
    echo "<strong>Testando admin: {$u['email']}</strong><br>";
    
    foreach ($senhas_testar as $senha) {
        if (password_verify($senha, $u['senha'])) {
            echo "✅ <span class='ok'><strong>SENHA ENCONTRADA: '$senha'</strong></span><br>";
        } else {
            echo "❌ Não é: '$senha'<br>";
        }
    }
    echo "</div>";
}

// 5. Gerar novo hash para admin123
echo "<h2>5. Gerar Novo Hash</h2>";
$novo_hash = password_hash('admin123', PASSWORD_DEFAULT);
echo "<p>Se quiser alterar a senha do admin para 'admin123', execute este SQL:</p>";
echo "<div class='info'>";
echo "<pre style='background:#fff;padding:10px;border:1px solid #ccc;'>";
echo "UPDATE usuario SET senha = '$novo_hash' WHERE email = 'admin@warada.com';";
echo "</pre>";
echo "</div>";

// 6. Instruções
echo "<h2>6. Como Fazer Login</h2>";
echo "<div class='info'>";
echo "<p><strong>Use os dados encontrados acima!</strong></p>";
echo "<p>Acesse: <a href='login.php'>login.php</a></p>";
echo "<p>Digite o email e a senha correta identificada no teste acima.</p>";
echo "</div>";

// 7. Teste direto de login
echo "<h2>7. Teste Direto de Login</h2>";
echo "<form method='post' action=''>";
echo "Email: <input type='email' name='test_email' value='admin@warada.com'><br><br>";
echo "Senha: <input type='password' name='test_senha' placeholder='Digite a senha'><br><br>";
echo "<button type='submit' name='test_login'>🔐 Testar Login</button>";
echo "</form>";

if (isset($_POST['test_login'])) {
    $email = $_POST['test_email'];
    $senha = $_POST['test_senha'];
    
    echo "<div class='info'>";
    echo "<h3>Resultado do Teste:</h3>";
    
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo "<p class='error'>❌ Email não encontrado!</p>";
    } else {
        echo "<p class='ok'>✅ Usuário encontrado: {$user['nome']}</p>";
        echo "<p>Nível: <strong>{$user['nivel']}</strong></p>";
        
        if (password_verify($senha, $user['senha'])) {
            echo "<p class='ok'>✅✅✅ <strong>SENHA CORRETA! LOGIN FUNCIONARIA!</strong></p>";
            echo "<p>Este usuário conseguiria fazer login com estas credenciais.</p>";
        } else {
            echo "<p class='error'>❌ Senha incorreta!</p>";
        }
    }
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>💡 Dica:</strong> Depois de descobrir a senha, delete este arquivo por segurança!</p>";
?>