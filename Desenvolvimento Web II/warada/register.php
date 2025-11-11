<?php
require_once __DIR__ . '/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if ($nome && $email && $senha) {
        $stmt = $pdo->prepare("SELECT id FROM usuario WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email já cadastrado.";
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuario (nome,email,senha) VALUES (?,?,?)");
            $stmt->execute([$nome,$email,$hash]);
            header('Location: ' . url('login.php'));
            exit;
        }
    } else {
        $error = "Preencha todos os campos.";
    }
}
?>
<h2>Registrar</h2>
<?php if(!empty($error)): ?><p class="error"><?=esc($error)?></p><?php endif; ?>
<form method="post" action="<?= url('register.php') ?>">
  <label>Nome: <input type="text" name="nome" required></label><br>
  <label>Email: <input type="email" name="email" required></label><br>
  <label>Senha: <input type="password" name="senha" required></label><br>
  <button type="submit">Cadastrar</button>
</form>
<?php require_once __DIR__ . '/footer.php'; ?>