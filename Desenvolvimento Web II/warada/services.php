<?php
require_once __DIR__ . '/header.php';

$stmt = $pdo->query("SELECT * FROM servico ORDER BY id DESC");
$servicos = $stmt->fetchAll();
?>

<h2>Nosso Cardápio</h2>

<?php if ($servicos): ?>
  <div class="grid">
    <?php foreach($servicos as $s): ?>
      <article class="card">
        <?php if($s['foto']): ?>
          <img src="<?=esc($s['foto'])?>" alt="<?=esc($s['titulo'])?>" style="width:100%;border-radius:8px;">
        <?php endif; ?>
        <h3><?=esc($s['titulo'])?></h3>
        <p><?=nl2br(esc($s['descricao']))?></p>
        <a href="<?= url('service.php?id=' . $s['id']) ?>" class="btn">Ver mais</a>
      </article>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <p>Nenhum produto cadastrado ainda.</p>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>