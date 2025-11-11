<?php
require_once __DIR__ . '/header.php';
require_login();

if (!isset($_GET['id'])) {
    header('Location: ' . url('meus_pedidos.php'));
    exit;
}

$pedido_id = (int)$_GET['id'];

// Buscar pedido
$stmt = $pdo->prepare("
    SELECT p.* 
    FROM pedido p
    WHERE p.id = ? AND p.usuario_id = ?
");
$stmt->execute([$pedido_id, $_SESSION['user']['id']]);
$pedido = $stmt->fetch();

if (!$pedido) {
    header('Location: ' . url('meus_pedidos.php'));
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

$status_texto = [
    'pendente' => 'Pendente',
    'processando' => 'Em Preparo',
    'enviado' => 'Saiu para Entrega',
    'entregue' => 'Entregue',
    'cancelado' => 'Cancelado'
];
?>

<div class="detalhes-container">
    <a href="<?= url('meus_pedidos.php') ?>" class="btn-back">← Voltar aos Meus Pedidos</a>
    
    <div class="detalhes-header">
        <div>
            <h1>Pedido #<?= str_pad($pedido['id'], 6, '0', STR_PAD_LEFT) ?></h1>
            <p>Realizado em <?= date('d/m/Y às H:i', strtotime($pedido['criado_em'])) ?></p>
        </div>
        <span class="badge-status badge-<?= $pedido['status'] ?>">
            <?= $status_texto[$pedido['status']] ?? $pedido['status'] ?>
        </span>
    </div>

    <div class="timeline">
        <div class="timeline-item <?= in_array($pedido['status'], ['pendente', 'processando', 'enviado', 'entregue']) ? 'active' : '' ?>">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
                <h4>Pedido Recebido</h4>
                <p><?= date('d/m/Y H:i', strtotime($pedido['criado_em'])) ?></p>
            </div>
        </div>
        
        <div class="timeline-item <?= in_array($pedido['status'], ['processando', 'enviado', 'entregue']) ? 'active' : '' ?>">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
                <h4>Em Preparo</h4>
                <?php if(in_array($pedido['status'], ['processando', 'enviado', 'entregue'])): ?>
                    <p>Preparando seu pedido...</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="timeline-item <?= in_array($pedido['status'], ['enviado', 'entregue']) ? 'active' : '' ?>">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
                <h4>Saiu para Entrega</h4>
                <?php if(in_array($pedido['status'], ['enviado', 'entregue'])): ?>
                    <p>A caminho do endereço</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="timeline-item <?= $pedido['status'] === 'entregue' ? 'active' : '' ?>">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
                <h4>Entregue</h4>
                <?php if($pedido['status'] === 'entregue'): ?>
                    <p>Pedido concluído!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="detalhes-grid">
        <div class="detalhes-itens">
            <h2>🍽️ Itens do Pedido</h2>
            <?php foreach($itens as $item): ?>
                <div class="item-detalhes">
                    <?php if($item['foto']): ?>
                        <img src="<?= esc($item['foto']) ?>" alt="<?= esc($item['titulo']) ?>">
                    <?php endif; ?>
                    <div class="item-info-full">
                        <h4><?= esc($item['titulo']) ?></h4>
                        <p>Quantidade: <?= $item['quantidade'] ?></p>
                        <p class="item-preco">
                            R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?> x <?= $item['quantidade'] ?> = 
                            <strong>R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></strong>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="detalhes-total">
                <span>Total do Pedido:</span>
                <span class="total-valor">R$ <?= number_format($pedido['total'], 2, ',', '.') ?></span>
            </div>
        </div>

        <div class="detalhes-info">
            <h2>📍 Informações de Entrega</h2>
            
            <div class="info-box">
                <h4>Endereço:</h4>
                <p><?= nl2br(esc($pedido['endereco'])) ?></p>
            </div>
            
            <?php if($pedido['observacoes']): ?>
                <div class="info-box">
                    <h4>Observações:</h4>
                    <p><?= nl2br(esc($pedido['observacoes'])) ?></p>
                </div>
            <?php endif; ?>
            
            <div class="info-box">
                <h4>Forma de Pagamento:</h4>
                <p>💳 Pagamento na Entrega</p>
            </div>
        </div>
    </div>
</div>

<style>
.detalhes-container {
    max-width: 1200px;
    margin: 0 auto;
}

.detalhes-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: var(--shadow);
    margin-bottom: 2rem;
}

.detalhes-header h1 {
    margin: 0 0 0.5rem 0;
    color: var(--secondary);
}

.detalhes-header p {
    margin: 0;
    color: #666;
}

.timeline {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: var(--shadow);
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    position: relative;
}

.timeline::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 2px;
    background: var(--border);
    z-index: 0;
}

.timeline-item {
    position: relative;
    z-index: 1;
    text-align: center;
    flex: 1;
}

.timeline-dot {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: var(--border);
    margin: 0 auto 1rem;
    border: 4px solid white;
    transition: all 0.3s ease;
}

.timeline-item.active .timeline-dot {
    background: var(--primary);
    box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.2);
}

.timeline-content h4 {
    margin: 0 0 0.5rem 0;
    color: var(--secondary);
}

.timeline-content p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.timeline-item.active .timeline-content h4 {
    color: var(--primary);
    font-weight: bold;
}

.detalhes-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
}

.detalhes-itens,
.detalhes-info {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: var(--shadow);
}

.detalhes-itens h2,
.detalhes-info h2 {
    color: var(--secondary);
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--border);
}

.item-detalhes {
    display: flex;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid var(--border);
}

.item-detalhes img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
}

.item-info-full h4 {
    margin: 0 0 0.5rem 0;
    color: var(--secondary);
}

.item-info-full p {
    margin: 0.25rem 0;
    color: #666;
}

.item-preco {
    color: var(--primary);
    font-weight: 600;
}

.detalhes-total {
    display: flex;
    justify-content: space-between;
    padding: 1.5rem;
    margin-top: 1rem;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border-radius: 8px;
    color: white;
    font-size: 1.3rem;
    font-weight: 600;
}

.info-box {
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: #f9f9f9;
    border-radius: 8px;
    border-left: 4px solid var(--primary);
}

.info-box h4 {
    margin: 0 0 0.5rem 0;
    color: var(--secondary);
}

.info-box p {
    margin: 0;
    color: #666;
    line-height: 1.6;
}

@media (max-width: 768px) {
    .detalhes-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .timeline {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .timeline::before {
        width: 2px;
        height: 100%;
        top: 0;
        left: 15px;
    }
    
    .timeline-item {
        text-align: left;
        padding-left: 3rem;
    }
    
    .timeline-dot {
        position: absolute;
        left: 0;
    }
    
    .detalhes-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once __DIR__ . '/footer.php'; ?>