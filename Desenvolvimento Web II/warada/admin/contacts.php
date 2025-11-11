<?php
require_once __DIR__ . '/../init.php';
require_login('admin');

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM contato WHERE id=?")->execute([$id]);
    header('Location: contacts.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM contato ORDER BY data_envio DESC");
$mensagens = $stmt->fetchAll();

require_once __DIR__ . '/../header.php';
?>
<h2>Mensagens de Contato</h2>
<table border="1" cellpadding="5">
  <tr><th>ID</th><th>Nome</th><th>Email</th><th>Mensagem</th><th>Data</th><th>Ações</th></tr>
  <?php foreach($mensagens as $m): ?>
    <tr>
      <td><?=esc($m['id'])?></td>
      <td><?=esc($m['nome'])?></td>
      <td><?=esc($m['email'])?></td>
      <td><?=nl2br(esc($m['mensagem']))?></td>
      <td><?=esc($m['data_envio'])?></td>
      <td><a href="?delete=<?=$m['id']?>" onclick="return confirm('Excluir?')">Excluir</a></td>
    </tr>
  <?php endforeach; ?>
</table>
<?php require_once __DIR__ . '/../footer.php'; ?>
