<?php
require_once __DIR__ . '/header.php';

$stmt = $pdo->query("SELECT * FROM servico ORDER BY id DESC LIMIT 6");
$servicos = $stmt->fetchAll();

$stmt = $pdo->query("SELECT * FROM avaliacao ORDER BY id DESC LIMIT 5");
$avaliacoes = $stmt->fetchAll();
?>

<section id="quem-somos">
  <h2>Quem somos</h2>
  <p>Warada Express — sabor autêntico árabe na sua porta. Entregas rápidas e pratos preparados com carinho.</p>
</section>

<section id="chamada-cadastro">
  <h2>Junte-se ao Warada</h2>
  <?php if(!is_logged_in()): ?>
    <p>Crie uma conta e faça seu pedido:</p>
    <a class="btn" href="<?= url('register.php') ?>">Cadastre-se</a>
  <?php else: ?>
    <p>Olá, <?=esc($user['nome'])?>! Confira nosso cardápio.</p>
  <?php endif; ?>
</section>

<section id="produtos">
  <h2>Produtos & Serviços</h2>
  <?php if($servicos): ?>
    <div class="grid">
      <?php foreach($servicos as $s): ?>
        <article class="card">
          <?php if($s['foto']): ?><img src="<?=esc($s['foto'])?>" alt="<?=esc($s['titulo'])?>"></<?php endif; ?>
          <h3><?=esc($s['titulo'])?></h3>
          <p><?=nl2br(esc($s['descricao']))?></p>
          <a href="<?= url('service.php?id=' . $s['id']) ?>" class="btn">Comprar</a>
        </article>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p>Sem produtos cadastrados.</p>
  <?php endif; ?>
</section>

<section id="avaliacoes">
  <h2>Avaliações</h2>
  <?php if($avaliacoes): ?>
    <?php foreach($avaliacoes as $a): ?>
      <div class="review">
        <strong><?=esc($a['nome'])?></strong> — <?=str_repeat('★', (int)$a['estrelas'])?>
        <p><?=esc($a['comentario'])?></p>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p>Seja o primeiro a avaliar!</p>
  <?php endif; ?>
</section>

<section id="contato-cta">
  <h2>Fale conosco</h2>
  <a class="btn" href="<?= url('contact.php') ?>">Enviar mensagem</a>
</section>

<?php require_once __DIR__ . '/footer.php'; ?>