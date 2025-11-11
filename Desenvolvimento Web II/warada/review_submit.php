<?php
require_once __DIR__ . '/header.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_SESSION['user']['nome'];
    $estrelas = (int)($_POST['estrelas'] ?? 5);
    $comentario = trim($_POST['comentario'] ?? '');

    if ($comentario === '') {
        $error = "Comentário não pode ficar vazio.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO avaliacao (nome, estrelas, comentario) VALUES (?, ?, ?)");
        $stmt->execute([$nome, $estrelas, $comentario]);
        header('Location: ' . url('index.php'));
        exit;
    }
}
?>
<h2>Avaliar pedido</h2>
<?php if(!empty($error)): ?><p class="error"><?=esc($error)?></p><?php endif; ?>
<form method="post" action="<?= url('review_submit.php') ?>">
  <label>Estrelas (1-5): <input type="number" name="estrelas" min="1" max="5" value="5"></label><br>
  <label>Comentário:<br>
    <textarea name="comentario" rows="5"></textarea>
  </label><br>
  <button type="submit">Enviar avaliação</button>
</form>

<?php require_once __DIR__ . '/footer.php'; ?>