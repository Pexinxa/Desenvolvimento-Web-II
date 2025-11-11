<?php
require_once __DIR__ . '/header.php';
require_login();

if (!isset($_GET['pedido_id'])) {
    header('Location: ' . url('index.php'));
    exit;
}

$pedido_id = (int)$_GET['pedido_id'];

// Buscar pedido
$stmt = $pdo->prepare("
    SELECT p.*, u.nome as cliente_nome, u.email as cliente_email
    FROM pedido p
    JOIN usuario u ON p.usuario_id = u.id
    WHERE p.id = ? AND p.usuario_id = ?
");
$stmt->execute([$pedido_id, $_SESSION['user']['id']]);
$pedido = $stmt->fetch();

if (!$pedido) {
    header('Location: ' . url('index.php'));
    exit;
}

// Buscar itens
$stmt = $pdo->prepare("
    SELECT pi.*, s.titulo, s.foto
    FROM pedido_item pi
    JOIN servico s ON pi.servico_id = s.id
    WHERE pi.pedido_id = ?
");
$stmt->execute([$pedido_id]);
$itens = $stmt->fetchAll();
?>

<div class="confirmacao-container">
    <div class="confirmacao-header">
        <div class="sucesso-icon">✅</div>
        <h1>Pedido Confirmado!</h1>
        <p class="pedido-numero">Pedido #<?= str_pad($pedido['id'], 6, '0', STR_PAD_LEFT) ?></p>
    </div>

    <div class="confirmacao-mensagem">
        <p>🎉 <strong>Obrigado, <?= esc($pedido['cliente_nome']) ?>!</strong></p>
        <p>Seu pedido foi recebido e está sendo preparado com todo carinho.</p>
        <p>Você receberá atualizações sobre o status do seu pedido.</p>
    </div>

    <div class="confirmacao-grid">
        <div class="confirmacao-detalhes">
            <h2>📦 Detalhes do Pedido</h2>
            
            <div class="itens-lista">
                <?php foreach($itens as $item): ?>
                    <div class="item-confirmacao">
                        <?php if($item['foto']): ?>
                            <img src="<?= esc($item['foto']) ?>" alt="<?= esc($item['titulo']) ?>">
                        <?php endif; ?>
                        <div class="item-info-conf">
                            <strong><?= esc($item['titulo']) ?></strong>
                            <p>Quantidade: <?= $item['quantidade'] ?>x</p>
                            <p class="preco">R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="total-pedido">
                <span>Total:</span>
                <span class="valor-total">R$ <?= number_format($pedido['total'], 2, ',', '.') ?></span>
            </div>
        </div>

        <div class="confirmacao-info">
            <div class="info-card">
                <h3>📍 Endereço de Entrega</h3>
                <p><?= nl2br(esc($pedido['endereco'])) ?></p>
            </div>

            <?php if($pedido['observacoes']): ?>
                <div class="info-card">
                    <h3>📝 Observações</h3>
                    <p><?= nl2br(esc($pedido['observacoes'])) ?></p>
                </div>
            <?php endif; ?>

            <div class="info-card">
                <h3>⏱️ Tempo Estimado</h3>
                <p class="tempo-destaque">30-45 minutos</p>
            </div>

            <div class="info-card">
                <h3>💳 Pagamento</h3>
                <p>Na entrega (dinheiro ou cartão)</p>
            </div>

            <div class="info-card status-card">
                <h3>📊 Status Atual</h3>
                <div class="status-badge badge-pendente">
                    Pedido Recebido
                </div>
                <p class="status-texto">Estamos preparando seu pedido!</p>
            </div>
        </div>
    </div>

    <div class="confirmacao-acoes">
        <a href="<?= url('pedido_detalhes.php?id=' . $pedido['id']) ?>" class="btn">
            📋 Ver Detalhes Completos
        </a>
        <a href="<?= url('meus_pedidos.php') ?>" class="btn-secondary">
            📦 Ver Todos os Meus Pedidos
        </a>
        <a href="<?= url('services.php') ?>" class="btn-secondary">
            🍽️ Fazer Outro Pedido
        </a>
    </div>

    <div class="proximos-passos">
        <h2>📱 Próximos Passos</h2>
        <div class="passos-grid">
            <div class="passo">
                <span class="passo-numero">1</span>
                <h4>Preparando</h4>
                <p>Estamos preparando seu pedido com ingredientes frescos</p>
            </div>
            <div class="passo">
                <span class="passo-numero">2</span>
                <h4>Saiu para Entrega</h4>
                <p>Seu pedido está a caminho do endereço</p>
            </div>
            <div class="passo">
                <span class="passo-numero">3</span>
                <h4>Entregue</h4>
                <p>Aproveite sua refeição! ❤️</p>
            </div>
        </div>
    </div>
</div>

<style>
.confirmacao-container {
    max-width: 1200px;
    margin: 0 auto;
}

.confirmacao-header {
    text-align: center;
    background: white;
    padding: 3rem 2rem;
    border-radius: 10px;
    box-shadow: var(--shadow);
    margin-bottom: 2rem;
}

.sucesso-icon {
    font-size: 5rem;
    margin-bottom: 1rem;
    animation: pulse 1s ease-in-out;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.confirmacao-header h1 {
    color: var(--success);
    margin: 0 0 0.5rem 0;
    font-size: 2.5rem;
}

.pedido-numero {
    color: var(--primary);
    font-size: 1.5rem;
    font-weight: bold;
    margin: 0;
}

.confirmacao-mensagem {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    padding: 2rem;
    border-radius: 10px;
    text-align: center;
    margin-bottom: 2rem;
    border-left: 4px solid var(--success);
}

.confirmacao-mensagem p {
    margin: 0.5rem 0;
    color: #155724;
    font-size: 1.1rem;
}

.confirmacao-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.confirmacao-detalhes,
.confirmacao-info {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: var(--shadow);
}

.confirmacao-detalhes h2 {
    color: var(--secondary);
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--border);
}

.itens-lista {
    margin-bottom: 1.5rem;
}

.item-confirmacao {
    display: flex;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid var(--border);
}

.item-confirmacao:last-child {
    border-bottom: none;
}

.item-confirmacao img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}

.item-info-conf strong {
    display: block;
    color: var(--secondary);
    margin-bottom: 0.5rem;
}

.item-info-conf p {
    margin: 0.25rem 0;
    color: #666;
}

.item-info-conf .preco {
    color: var(--primary);
    font-weight: 600;
}

.total-pedido {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    margin-top: 1rem;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border-radius: 8px;
    color: white;
    font-size: 1.5rem;
    font-weight: bold;
}

.info-card {
    margin-bottom: 1.5rem;
    padding: 1.5rem;
    background: #f9f9f9;
    border-radius: 8px;
    border-left: 4px solid var(--primary);
}

.info-card h3 {
    margin: 0 0 1rem 0;
    color: var(--secondary);
    font-size: 1.1rem;
}

.info-card p {
    margin: 0;
    color: #666;
    line-height: 1.6;
}

.tempo-destaque {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--primary);
}

.status-card {
    border-left-color: var(--success);
}

.status-badge {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    border-radius: 20px;
    font-weight: bold;
    margin-bottom: 1rem;
}

.badge-pendente {
    background: var(--warning);
    color: #333;
}

.status-texto {
    font-weight: 600;
    color: var(--success);
}

.confirmacao-acoes {
    display: flex;
    gap: 1rem;
    margin-bottom: 3rem;
    justify-content: center;
    flex-wrap: wrap;
}

.confirmacao-acoes .btn,
.confirmacao-acoes .btn-secondary {
    min-width: 250px;
    text-align: center;
}

.proximos-passos {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: var(--shadow);
}

.proximos-passos h2 {
    color: var(--secondary);
    text-align: center;
    margin-bottom: 2rem;
}

.passos-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}

.passo {
    text-align: center;
    padding: 2rem;
    background: #f9f9f9;
    border-radius: 10px;
}

.passo-numero {
    display: inline-block;
    width: 60px;
    height: 60px;
    line-height: 60px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 1rem;
}

.passo h4 {
    color: var(--secondary);
    margin: 1rem 0 0.5rem 0;
}

.passo p {
    margin: 0;
    color: #666;
    line-height: 1.6;
}

@media (max-width: 768px) {
    .confirmacao-grid {
        grid-template-columns: 1fr;
    }
    
    .passos-grid {
        grid-template-columns: 1fr;
    }
    
    .confirmacao-acoes {
        flex-direction: column;
    }
    
    .confirmacao-acoes .btn,
    .confirmacao-acoes .btn-secondary {
        width: 100%;
    }
}
</style>

<?php require_once __DIR__ . '/footer.php'; ?>