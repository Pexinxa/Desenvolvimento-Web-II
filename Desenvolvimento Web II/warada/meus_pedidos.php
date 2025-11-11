<?php
require_once __DIR__ . '/header.php';
require_login();

$usuario_id = $_SESSION['user']['id'];

// Buscar pedidos do usuário
$stmt = $pdo->prepare("
    SELECT p.*
    FROM pedido p
    WHERE p.usuario_id = ?
    ORDER BY p.criado_em DESC
");
$stmt->execute([$usuario_id]);
$pedidos = $stmt->fetchAll();

$status_texto = [
    'pendente' => 'Pendente',
    'processando' => 'Em Preparo',
    'enviado' => 'Saiu para Entrega',
    'entregue' => 'Entregue',
    'cancelado' => 'Cancelado'
];

$status_icons = [
    'pendente' => '⏳',
    'processando' => '👨‍🍳',
    'enviado' => '🚚',
    'entregue' => '✅',
    'cancelado' => '❌'
];
?>

<div class="meus-pedidos-container">
    <h1>📦 Meus Pedidos</h1>
    
    <div class="pedidos-info">
        <p>Acompanhe aqui todos os seus pedidos e o status de cada um deles.</p>
    </div>

    <?php if($pedidos): ?>
        <div class="pedidos-lista">
            <?php foreach($pedidos as $p): ?>
                <?php
                // Buscar alguns itens do pedido para preview
                $stmt = $pdo->prepare("
                    SELECT pi.*, s.titulo, s.foto
                    FROM pedido_item pi
                    JOIN servico s ON pi.servico_id = s.id
                    WHERE pi.pedido_id = ?
                    LIMIT 3
                ");
                $stmt->execute([$p['id']]);
                $itens = $stmt->fetchAll();
                
                $total_itens = $pdo->prepare("SELECT COUNT(*) FROM pedido_item WHERE pedido_id = ?");
                $total_itens->execute([$p['id']]);
                $qtd_itens = $total_itens->fetchColumn();
                ?>
                
                <div class="pedido-card">
                    <div class="pedido-header">
                        <div class="pedido-numero">
                            <h3>Pedido #<?= str_pad($p['id'], 6, '0', STR_PAD_LEFT) ?></h3>
                            <p class="pedido-data">
                                <?= date('d/m/Y', strtotime($p['criado_em'])) ?> às 
                                <?= date('H:i', strtotime($p['criado_em'])) ?>
                            </p>
                        </div>
                        <span class="badge-status badge-<?= $p['status'] ?>">
                            <?= $status_icons[$p['status']] ?> <?= $status_texto[$p['status']] ?>
                        </span>
                    </div>

                    <div class="pedido-body">
                        <div class="pedido-itens-preview">
                            <h4>Itens do pedido:</h4>
                            <div class="itens-mini">
                                <?php foreach($itens as $item): ?>
                                    <div class="item-mini">
                                        <?php if($item['foto']): ?>
                                            <img src="<?= esc($item['foto']) ?>" alt="<?= esc($item['titulo']) ?>">
                                        <?php endif; ?>
                                        <div>
                                            <strong><?= esc($item['titulo']) ?></strong>
                                            <p><?= $item['quantidade'] ?>x</p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php if($qtd_itens > 3): ?>
                                    <p class="mais-itens">+ <?= $qtd_itens - 3 ?> item(ns)</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="pedido-valor">
                            <span>Total:</span>
                            <strong>R$ <?= number_format($p['total'], 2, ',', '.') ?></strong>
                        </div>
                    </div>

                    <div class="pedido-footer">
                        <a href="<?= url('pedido_detalhes.php?id=' . $p['id']) ?>" class="btn-detalhes">
                            Ver Detalhes →
                        </a>
                        
                        <?php if($p['status'] === 'entregue'): ?>
                            <a href="<?= url('review_submit.php?pedido_id=' . $p['id']) ?>" class="btn-avaliar">
                                ⭐ Avaliar Pedido
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="pedidos-vazio">
            <p>📭 Você ainda não fez nenhum pedido</p>
            <a href="<?= url('services.php') ?>" class="btn">Ver Cardápio</a>
        </div>
    <?php endif; ?>
</div>

<style>
.meus-pedidos-container {
    max-width: 1200px;
    margin: 0 auto;
}

.pedidos-info {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: var(--shadow);
    margin-bottom: 2rem;
    text-align: center;
}

.pedidos-info p {
    margin: 0;
    color: #666;
}

.pedidos-lista {
    display: grid;
    gap: 1.5rem;
}

.pedido-card {
    background: white;
    border-radius: 10px;
    box-shadow: var(--shadow);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.pedido-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.pedido-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 2px solid var(--border);
}

.pedido-numero h3 {
    margin: 0 0 0.5rem 0;
    color: var(--secondary);
    font-size: 1.3rem;
}

.pedido-data {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.badge-status {
    padding: 0.75rem 1.5rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.95rem;
}

.badge-pendente {
    background: #fff3cd;
    color: #856404;
}

.badge-processando {
    background: #cce5ff;
    color: #004085;
}

.badge-enviado {
    background: #d1ecf1;
    color: #0c5460;
}

.badge-entregue {
    background: #d4edda;
    color: #155724;
}

.badge-cancelado {
    background: #f8d7da;
    color: #721c24;
}

.pedido-body {
    padding: 1.5rem;
}

.pedido-itens-preview h4 {
    margin: 0 0 1rem 0;
    color: var(--secondary);
    font-size: 1.1rem;
}

.itens-mini {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.item-mini {
    display: flex;
    gap: 1rem;
    align-items: center;
    padding: 0.75rem;
    background: #f9f9f9;
    border-radius: 8px;
}

.item-mini img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 5px;
}

.item-mini strong {
    display: block;
    color: var(--secondary);
    font-size: 0.95rem;
}

.item-mini p {
    margin: 0.25rem 0 0 0;
    color: #666;
    font-size: 0.85rem;
}

.mais-itens {
    color: var(--primary);
    font-weight: 600;
    margin: 0;
    padding: 0.75rem;
}

.pedido-valor {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    margin-top: 1rem;
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.1) 0%, rgba(212, 175, 55, 0.05) 100%);
    border-radius: 8px;
    font-size: 1.2rem;
}

.pedido-valor strong {
    color: var(--primary);
    font-size: 1.5rem;
}

.pedido-footer {
    display: flex;
    gap: 1rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-top: 2px solid var(--border);
}

.btn-detalhes,
.btn-avaliar {
    flex: 1;
    text-align: center;
    padding: 0.75rem 1.5rem;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-detalhes {
    background: var(--primary);
    color: var(--bg-dark);
}

.btn-detalhes:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
}

.btn-avaliar {
    background: white;
    color: var(--primary);
    border: 2px solid var(--primary);
}

.btn-avaliar:hover {
    background: var(--primary);
    color: var(--bg-dark);
}

.pedidos-vazio {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 10px;
    box-shadow: var(--shadow);
}

.pedidos-vazio p {
    font-size: 1.5rem;
    color: #999;
    margin-bottom: 2rem;
}

@media (max-width: 768px) {
    .pedido-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .itens-mini {
        grid-template-columns: 1fr;
    }
    
    .pedido-footer {
        flex-direction: column;
    }
}
</style>

<?php require_once __DIR__ . '/footer.php'; ?>