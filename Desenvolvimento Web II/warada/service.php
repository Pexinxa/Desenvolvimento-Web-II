<?php
require_once __DIR__ . '/header.php';

if (!isset($_GET['id'])) {
    echo "<p>Serviço não encontrado.</p>";
    require_once __DIR__ . '/footer.php';
    exit;
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM servico WHERE id = ? AND ativo = 1");
$stmt->execute([$id]);
$serv = $stmt->fetch();

if (!$serv) {
    echo "<p>Serviço não encontrado.</p>";
    require_once __DIR__ . '/footer.php';
    exit;
}

// Buscar avaliações relacionadas (opcional)
$stmt = $pdo->query("SELECT * FROM avaliacao ORDER BY criado_em DESC LIMIT 3");
$avaliacoes = $stmt->fetchAll();
?>

<div class="produto-container">
    <div class="produto-header">
        <a href="<?= url('services.php') ?>" class="btn-back">← Voltar ao Cardápio</a>
    </div>

    <div class="produto-grid">
        <div class="produto-imagem">
            <?php if($serv['foto']): ?>
                <img src="<?=esc($serv['foto'])?>" alt="<?=esc($serv['titulo'])?>">
            <?php else: ?>
                <div class="no-image-large">📷 Sem imagem disponível</div>
            <?php endif; ?>
        </div>

        <div class="produto-info">
            <h1><?=esc($serv['titulo'])?></h1>
            
            <div class="produto-preco">
                <span class="preco-label">Preço:</span>
                <span class="preco-valor">R$ <?= number_format($serv['preco'], 2, ',', '.') ?></span>
            </div>

            <div class="produto-descricao">
                <h3>📝 Descrição</h3>
                <p><?=nl2br(esc($serv['descricao']))?></p>
            </div>

            <?php if(!is_logged_in()): ?>
                <div class="produto-login">
                    <p>⚠️ Você precisa estar logado para adicionar ao carrinho.</p>
                    <a href="<?= url('login.php') ?>" class="btn">Fazer Login</a>
                    <a href="<?= url('register.php') ?>" class="btn-secondary">Criar Conta</a>
                </div>
            <?php else: ?>
                <form action="<?= url('carrinho.php') ?>" method="post" class="produto-form">
                    <input type="hidden" name="servico_id" value="<?=esc($serv['id'])?>">
                    
                    <div class="quantidade-selector">
                        <label>Quantidade:</label>
                        <div class="quantidade-controls">
                            <button type="button" onclick="diminuir()">−</button>
                            <input type="number" id="quantidade" name="quantidade" value="1" min="1" max="99" readonly>
                            <button type="button" onclick="aumentar()">+</button>
                        </div>
                    </div>
                    
                    <button type="submit" name="adicionar" class="btn btn-adicionar">
                        🛒 Adicionar ao Carrinho
                    </button>
                </form>
                
                <div class="produto-links">
                    <a href="<?= url('carrinho.php') ?>" class="link-carrinho">
                        Ver Carrinho →
                    </a>
                </div>
            <?php endif; ?>

            <div class="produto-info-extra">
                <div class="info-item">
                    <span class="icon">⏱️</span>
                    <div>
                        <strong>Tempo de Preparo</strong>
                        <p>20-30 minutos</p>
                    </div>
                </div>
                <div class="info-item">
                    <span class="icon">🚚</span>
                    <div>
                        <strong>Entrega Grátis</strong>
                        <p>Para toda a região</p>
                    </div>
                </div>
                <div class="info-item">
                    <span class="icon">💳</span>
                    <div>
                        <strong>Pagamento</strong>
                        <p>Na entrega</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if($avaliacoes): ?>
        <section class="produto-avaliacoes">
            <h2>⭐ Avaliações dos Clientes</h2>
            <div class="avaliacoes-grid">
                <?php foreach($avaliacoes as $a): ?>
                    <div class="avaliacao-card">
                        <div class="avaliacao-header">
                            <strong><?=esc($a['nome'])?></strong>
                            <span class="estrelas"><?=str_repeat('⭐', (int)$a['estrelas'])?></span>
                        </div>
                        <p><?=esc($a['comentario'])?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<script>
function aumentar() {
    const input = document.getElementById('quantidade');
    const valor = parseInt(input.value);
    if (valor < 99) {
        input.value = valor + 1;
    }
}

function diminuir() {
    const input = document.getElementById('quantidade');
    const valor = parseInt(input.value);
    if (valor > 1) {
        input.value = valor - 1;
    }
}
</script>

<style>
.produto-container {
    max-width: 1200px;
    margin: 0 auto;
}

.produto-header {
    margin-bottom: 2rem;
}

.produto-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: var(--shadow);
    margin-bottom: 3rem;
}

.produto-imagem img {
    width: 100%;
    height: auto;
    max-height: 500px;
    object-fit: cover;
    border-radius: 10px;
    box-shadow: var(--shadow);
}

.no-image-large {
    width: 100%;
    height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f0f0f0;
    border-radius: 10px;
    color: #999;
    font-size: 1.5rem;
}

.produto-info h1 {
    color: var(--secondary);
    margin-bottom: 1.5rem;
    font-size: 2.5rem;
}

.produto-preco {
    display: flex;
    align-items: baseline;
    gap: 1rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border-radius: 10px;
    margin-bottom: 2rem;
}

.preco-label {
    color: white;
    font-size: 1.2rem;
}

.preco-valor {
    color: white;
    font-size: 2.5rem;
    font-weight: bold;
}

.produto-descricao {
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: #f9f9f9;
    border-radius: 8px;
}

.produto-descricao h3 {
    margin: 0 0 1rem 0;
    color: var(--secondary);
}

.produto-descricao p {
    margin: 0;
    color: #666;
    line-height: 1.8;
}

.produto-login {
    padding: 2rem;
    background: #fff3cd;
    border-radius: 8px;
    border-left: 4px solid var(--warning);
    text-align: center;
}

.produto-login p {
    margin-bottom: 1.5rem;
    color: #856404;
    font-weight: 600;
}

.produto-login .btn,
.produto-login .btn-secondary {
    margin: 0.5rem;
}

.produto-form {
    margin-bottom: 1.5rem;
}

.quantidade-selector {
    margin-bottom: 2rem;
}

.quantidade-selector label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--secondary);
}

.quantidade-controls {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.quantidade-controls button {
    width: 50px;
    height: 50px;
    border: 2px solid var(--primary);
    background: white;
    color: var(--primary);
    font-size: 1.5rem;
    font-weight: bold;
    cursor: pointer;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.quantidade-controls button:hover {
    background: var(--primary);
    color: white;
    transform: scale(1.1);
}

.quantidade-controls input {
    width: 80px;
    text-align: center;
    padding: 1rem;
    border: 2px solid var(--border);
    border-radius: 8px;
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--secondary);
}

.btn-adicionar {
    width: 100%;
    font-size: 1.2rem;
    padding: 1.2rem;
    margin-bottom: 1rem;
}

.produto-links {
    text-align: center;
    padding: 1rem 0;
}

.link-carrinho {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.link-carrinho:hover {
    color: var(--primary-dark);
    text-decoration: underline;
}

.produto-info-extra {
    margin-top: 2rem;
    padding: 1.5rem;
    background: #f9f9f9;
    border-radius: 8px;
}

.info-item {
    display: flex;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid var(--border);
}

.info-item:last-child {
    border-bottom: none;
}

.info-item .icon {
    font-size: 2rem;
}

.info-item strong {
    display: block;
    color: var(--secondary);
    margin-bottom: 0.25rem;
}

.info-item p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.produto-avaliacoes {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: var(--shadow);
}

.produto-avaliacoes h2 {
    margin-bottom: 2rem;
    color: var(--secondary);
}

.avaliacoes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.avaliacao-card {
    padding: 1.5rem;
    background: #f9f9f9;
    border-radius: 8px;
    border-left: 4px solid var(--primary);
}

.avaliacao-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.avaliacao-header strong {
    color: var(--secondary);
}

.estrelas {
    color: var(--primary);
}

.avaliacao-card p {
    margin: 0;
    color: #666;
    line-height: 1.6;
}

@media (max-width: 768px) {
    .produto-grid {
        grid-template-columns: 1fr;
    }
    
    .produto-info h1 {
        font-size: 2rem;
    }
    
    .preco-valor {
        font-size: 2rem;
    }
    
    .avaliacoes-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once __DIR__ . '/footer.php'; ?>