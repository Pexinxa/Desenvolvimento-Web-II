<?php
// header.php - CORRIGIDO PARA FUNCIONAR EM SUBPASTAS
require_once __DIR__ . '/init.php';
$user = current_user();

// Contar itens no carrinho (se o usuário estiver logado)
$carrinho_count = 0;
if ($user) {
    $stmt = $pdo->prepare("SELECT SUM(quantidade) FROM carrinho WHERE usuario_id = ?");
    $stmt->execute([$user['id']]);
    $carrinho_count = (int)$stmt->fetchColumn();
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Warada Express - Comida Árabe Autêntica</title>
  <link rel="stylesheet" href="<?= url('assets/style.css') ?>">
</head>
<body>
<header>
  <div class="logo"><h1>Warada Express</h1></div>
  <nav>
    <a href="<?= url('index.php') ?>">Home</a>
    <a href="<?= url('services.php') ?>">Cardápio</a>
    <a href="<?= url('contact.php') ?>">Contato</a>
    
    <?php if(!$user): ?>
      <a href="<?= url('login.php') ?>">Entrar</a>
      <a href="<?= url('register.php') ?>">Cadastrar</a>
    <?php else: ?>
      <a href="<?= url('carrinho.php') ?>" class="nav-carrinho">
        🛒 Carrinho
        <?php if($carrinho_count > 0): ?>
          <span class="carrinho-badge"><?= $carrinho_count ?></span>
        <?php endif; ?>
      </a>
      <a href="<?= url('meus_pedidos.php') ?>">Meus Pedidos</a>
      <span class="user-info">
        <?=esc($user['nome'])?> (<?=esc($user['nivel'])?>)
      </span>
      <a href="<?= url('logout.php') ?>">Sair</a>
      <?php if($user['nivel'] === 'admin'): ?>
        <a href="<?= url('admin/dashboard.php') ?>" class="admin-link">⚙️ Painel</a>
      <?php endif; ?>
    <?php endif; ?>
  </nav>
</header>
<main>

<style>
.nav-carrinho {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.carrinho-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: bold;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.admin-link {
    background: rgba(255,255,255,0.2);
    padding: 0.5rem 1rem;
    border-radius: 5px;
    border: 1px solid rgba(255,255,255,0.3);
}

@media (max-width: 768px) {
    .carrinho-badge {
        position: static;
        margin-left: 0.5rem;
    }
}
</style>