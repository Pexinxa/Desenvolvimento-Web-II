<?php
require_once __DIR__ . '/header.php';
require_login();

$usuario_id = $_SESSION['user']['id'];

// Adicionar ao carrinho
if (isset($_POST['adicionar'])) {
    $servico_id = (int)$_POST['servico_id'];
    $quantidade = (int)$_POST['quantidade'] ?? 1;
    
    // Verificar se o produto existe
    $stmt = $pdo->prepare("SELECT id FROM servico WHERE id = ? AND ativo = 1");
    $stmt->execute([$servico_id]);
    if ($stmt->fetch()) {
        // Verificar se já está no carrinho
        $stmt = $pdo->prepare("SELECT id, quantidade FROM carrinho WHERE usuario_id = ? AND servico_id = ?");
        $stmt->execute([$usuario_id, $servico_id]);
        $item = $stmt->fetch();
        
        if ($item) {
            // Atualizar quantidade
            $nova_quantidade = $item['quantidade'] + $quantidade;
            $stmt = $pdo->prepare("UPDATE carrinho SET quantidade = ? WHERE id = ?");
            $stmt->execute([$nova_quantidade, $item['id']]);
        } else {
            // Inserir novo item
            $stmt = $pdo->prepare("INSERT INTO carrinho (usuario_id, servico_id, quantidade) VALUES (?, ?, ?)");
            $stmt->execute([$usuario_id, $servico_id, $quantidade]);
        }
        $success = "Produto adicionado ao carrinho!";
    }
}

// Atualizar quantidade
if (isset($_POST['atualizar'])) {
    $carrinho_id = (int)$_POST['carrinho_id'];
    $quantidade = (int)$_POST['quantidade'];
    
    if ($quantidade > 0) {
        $stmt = $pdo->prepare("UPDATE carrinho SET quantidade = ? WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$quantidade, $carrinho_id, $usuario_id]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM carrinho WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$carrinho_id, $usuario_id]);
    }
    $success = "Carrinho atualizado!";
}

// Remover item
if (isset($_GET['remover'])) {
    $carrinho_id = (int)$_GET['remover'];
    $stmt = $pdo->prepare("DELETE FROM carrinho WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$carrinho_id, $usuario_id]);
    header('Location: ' . url('carrinho.php'));
    exit;
}

// Limpar carrinho
if (isset($_POST['limpar'])) {
    $stmt = $pdo->prepare("DELETE FROM carrinho WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);
    header('Location: ' . url('carrinho.php'));
    exit;
}

// Buscar itens do carrinho
$stmt = $pdo->prepare("
    SELECT c.*, s.titulo, s.descricao, s.foto, s.preco
    FROM carrinho c
    JOIN servico s ON c.servico_id = s.id
    WHERE c.usuario_id = ?
    ORDER BY c.criado_em DESC
");
$stmt->execute([$usuario_id]);
$itens = $stmt->fetchAll();

// Calcular total
$total = 0;
foreach ($itens as $item) {
    $total += $item['preco'] * $item['quantidade'];
}
?>

<div class="carrinho-container">
    <h1>🛒 Meu Carrinho</h1>
    
    <?php if(!empty($success)): ?>
        <div class="alert alert-success"><?= esc($success) ?></div>
    <?php endif; ?>

    <?php if($itens): ?>
        <div class="carrinho-grid">
            <div class="carrinho-itens">
                <?php foreach($itens as $item): ?>
                    <div class="carrinho-item">
                        <?php if($item['foto']): ?>
                            <img src="<?= esc($item['foto']) ?>" alt="<?= esc($item['titulo']) ?>">
                        <?php else: ?>
                            <div class="no-image">Sem imagem</div>
                        <?php endif; ?>
                        
                        <div class="item-info">
                            <h3><?= esc($item['titulo']) ?></h3>
                            <p class="item-preco">R$ <?= number_format($item['preco'], 2, ',', '.') ?></p>
                        </div>
                        
                        <div class="item-actions">
                            <form method="post" class="quantidade-form">
                                <input type="hidden" name="carrinho_id" value="<?= $item['id'] ?>">
                                <button type="submit" name="atualizar" value="1" 
                                        onclick="this.form.quantidade.value = Math.max(1, parseInt(this.form.quantidade.value) - 1)">
                                    −
                                </button>
                                <input type="number" name="quantidade" value="<?= $item['quantidade'] ?>" 
                                       min="1" max="99" readonly>
                                <button type="submit" name="atualizar" value="1"
                                        onclick="this.form.quantidade.value = Math.min(99, parseInt(this.form.quantidade.value) + 1)">
                                    +
                                </button>
                            </form>
                            
                            <p class="subtotal">
                                Subtotal: <strong>R$ <?= number_format($item['preco'] * $item['quantidade'], 2, ',', '.') ?></strong>
                            </p>
                            
                            <a href="<?= url('carrinho.php?remover=' . $item['id']) ?>" 
                               class="btn-remove"
                               onclick="return confirm('Remover este item do carrinho?')">
                                🗑️ Remover
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <form method="post" class="limpar-carrinho">
                    <button type="submit" name="limpar" class="btn-secondary"
                            onclick="return confirm('Limpar todo o carrinho?')">
                        🗑️ Limpar Carrinho
                    </button>
                </form>
            </div>
            
            <div class="carrinho-resumo">
                <h2>Resumo do Pedido</h2>
                
                <div class="resumo-linha">
                    <span>Subtotal:</span>
                    <span>R$ <?= number_format($total, 2, ',', '.') ?></span>
                </div>
                
                <div class="resumo-linha">
                    <span>Taxa de Entrega:</span>
                    <span class="gratis">Grátis</span>
                </div>
                
                <div class="resumo-total">
                    <span>Total:</span>
                    <span>R$ <?= number_format($total, 2, ',', '.') ?></span>
                </div>
                
                <a href="<?= url('checkout.php') ?>" class="btn btn-checkout">
                    Finalizar Pedido 🚀
                </a>
                
                <a href="<?= url('services.php') ?>" class="btn-secondary">
                    ← Continuar Comprando
                </a>
                
                <div class="info-entrega">
                    <h4>📦 Informações de Entrega</h4>
                    <ul>
                        <li>✅ Entrega grátis</li>
                        <li>⏱️ Tempo estimado: 30-45 min</li>
                        <li>💳 Pagamento na entrega</li>
                    </ul>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="carrinho-vazio">
            <p>🛒 Seu carrinho está vazio</p>
            <a href="<?= url('services.php') ?>" class="btn">Ver Cardápio</a>
        </div>
    <?php endif; ?>
</div>

<style>
.carrinho-container {
    max-width: 1200px;
    margin: 0 auto;
}

.carrinho-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    margin-top: 2rem;
}

.carrinho-itens {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.carrinho-item {
    display: grid;
    grid-template-columns: 120px 1fr auto;
    gap: 1.5rem;
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: var(--shadow);
}

.carrinho-item img {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 8px;
}

.item-info h3 {
    margin: 0 0 0.5rem 0;
    color: var(--secondary);
}

.item-preco {
    color: var(--primary);
    font-size: 1.2rem;
    font-weight: 600;
    margin: 0;
}

.item-actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    align-items: flex-end;
}

.quantidade-form {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.quantidade-form button {
    width: 35px;
    height: 35px;
    border: 2px solid var(--primary);
    background: white;
    color: var(--primary);
    font-size: 1.2rem;
    font-weight: bold;
    cursor: pointer;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.quantidade-form button:hover {
    background: var(--primary);
    color: white;
}

.quantidade-form input {
    width: 60px;
    text-align: center;
    padding: 0.5rem;
    border: 2px solid var(--border);
    border-radius: 5px;
    font-size: 1rem;
    font-weight: 600;
}

.subtotal {
    margin: 0;
    color: #666;
}

.btn-remove {
    color: var(--error);
    text-decoration: none;
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.btn-remove:hover {
    background: var(--error);
    color: white;
}

.limpar-carrinho {
    text-align: right;
    padding: 1rem 0;
}

.carrinho-resumo {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: var(--shadow);
    height: fit-content;
    position: sticky;
    top: 100px;
}

.carrinho-resumo h2 {
    margin: 0 0 1.5rem 0;
    color: var(--secondary);
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--border);
}

.resumo-linha {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    color: #666;
}

.gratis {
    color: var(--success);
    font-weight: 600;
}

.resumo-total {
    display: flex;
    justify-content: space-between;
    padding: 1.5rem 0;
    margin-top: 1rem;
    border-top: 2px solid var(--border);
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--secondary);
}

.btn-checkout {
    width: 100%;
    text-align: center;
    margin: 1.5rem 0 1rem 0;
    font-size: 1.1rem;
    padding: 1rem;
}

.carrinho-resumo .btn-secondary {
    width: 100%;
    text-align: center;
    display: block;
    padding: 0.75rem;
}

.info-entrega {
    margin-top: 2rem;
    padding: 1.5rem;
    background: #f9f9f9;
    border-radius: 8px;
}

.info-entrega h4 {
    margin: 0 0 1rem 0;
    color: var(--secondary);
}

.info-entrega ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.info-entrega li {
    padding: 0.5rem 0;
    color: #666;
}

.carrinho-vazio {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 10px;
    box-shadow: var(--shadow);
}

.carrinho-vazio p {
    font-size: 1.5rem;
    color: #999;
    margin-bottom: 2rem;
}

@media (max-width: 768px) {
    .carrinho-grid {
        grid-template-columns: 1fr;
    }
    
    .carrinho-item {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .item-actions {
        align-items: center;
    }
    
    .carrinho-resumo {
        position: static;
    }
}
</style>

<?php require_once __DIR__ . '/footer.php'; ?>