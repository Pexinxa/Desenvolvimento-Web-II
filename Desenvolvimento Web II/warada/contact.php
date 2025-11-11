<?php
require_once __DIR__ . '/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');

    if ($nome && $email && $mensagem) {
        $stmt = $pdo->prepare("INSERT INTO contato (nome,email,mensagem) VALUES (?,?,?)");
        $stmt->execute([$nome,$email,$mensagem]);
        $success = "Mensagem enviada. Obrigado!";
    } else {
        $error = "Preencha todos os campos.";
    }
}
?>

<h2>Contato</h2>
<?php if(!empty($success)): ?><p class="success"><?=esc($success)?></p><?php endif; ?>
<?php if(!empty($error)): ?><p class="error"><?=esc($error)?></p><?php endif; ?>

<form method="post" action="<?= url('contact.php') ?>">
  <label>Nome: <input type="text" name="nome" required></label><br>
  <label>Email: <input type="email" name="email" required></label><br>
  <label>Mensagem: <textarea name="mensagem" rows="5" required></textarea></label><br>
  <button type="submit">Enviar</button>
</form>

<?php require_once __DIR__ . '/footer.php'; ?>