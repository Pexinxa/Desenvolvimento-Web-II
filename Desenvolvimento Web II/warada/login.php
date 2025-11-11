<?php
require_once __DIR__ . '/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE email = ?");
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    
    if ($u && password_verify($senha, $u['senha'])) {
        $_SESSION['user'] = [
            'id' => $u['id'],
            'nome' => $u['nome'],
            'email' => $u['email'],
            'nivel' => $u['nivel'],
            'foto' => $u['foto']
        ];
        if ($u['nivel'] === 'admin') {
            header('Location: ' . url('admin/dashboard.php'));
        } else {
            header('Location: ' . url('index.php'));
        }
        exit;
    } else {
        $error = "Credenciais inválidas.";
    }
}
?>
<h2>Entrar</h2>
<?php if(!empty($error)): ?><p class="error"><?=esc($error)?></p><?php endif; ?>
<form method="post" action="<?= url('login.php') ?>">
  <label>Email: <input type="email" name="email" required></label><br>
  <label>Senha: <input type="password" name="senha" required></label><br>
  <button type="submit">Entrar</button>
</form>
<?php require_once __DIR__ . '/footer.php'; ?>