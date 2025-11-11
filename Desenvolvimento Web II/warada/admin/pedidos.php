<?php
require_once __DIR__ . '/../header.php';
require_login('admin');

// Atualizar status do pedido
if (isset($_POST['atualizar_status'])) {
    $pedido_id = (int)$_POST['pedido_id'];
    $novo_status = $_POST['status'];
    
    $status_validos = ['pendente', 'processando', 'enviado', 'entregue', 'cancelado'];
    if (in_array($novo_status, $status_validos)) {
        $stmt = $pdo->prepare("UPDATE pedido SET status = ? WHERE id = ?");
        $stmt->execute([$novo_status, $pedido_id]);
        $success = "Status atualizado com sucesso!";
    }
}

// Filtrar pedidos
$filtro = $_GET['filtro'] ?? 'todos';
$where = '';
if ($filtro !== 'todos') {
    $where = "WHERE p.status = " . $pdo->quote($filtro);
}

// Buscar pedidos
$stmt = $pdo->query("
    SELECT p.*, u.nome as cliente_nome, u.email as cliente_email
    FROM pedido p
    JOIN usuario u ON p.usuario_id = u.id
    $where
    ORDER BY p.criado_em DESC
");
$pedidos = $stmt->fetchAll();

// Estatísticas
$stats = [
    'pendente' => $pdo->query("SELECT COUNT(*) FROM pedido WHERE status = 'pendente'")->fetchColumn(),
    'processando' => $pdo->query("SELECT COUNT(*) FROM pedido WHERE status = 'processando'")->fetchColumn(),
    'enviado' => $pdo->query("SELECT COUNT(*) FROM pedido WHERE status = 'enviado'")->fetchColumn(),
    'entregue' => $pdo->query("SELECT COUNT(*) FROM pedido WHERE status = 'entregue'")->fetchColumn(),
    'cancelado' => $pdo->query("SELECT COUNT(*) FROM pedido WHERE status = 'cancelado'")->fetchColumn(),
];
?>

<div class="admin-container">
    <h1>📦 Gerenciar Pedidos</h1>
    
    <a href="<?= url('admin/dashboard.php') ?>" class="btn-back">← Voltar ao Painel</a>

    <?php if(!empty($success)): ?>
        <div class="alert alert-success"><?= esc($success) ?></div>
    <?php endif; ?>

    <div class="pedidos-stats">
        <div class="stat-mini">
            <h4>Pendentes</h4>
            <p class="stat-number-small"><?= $stats['pendente'] ?></p>
        </div>
        <div class="stat-mini">
            <h4>Em Preparo</h4>
            <p class="stat-number-small"><?= $stats['processando'] ?></p>
        </div>
        <div class="stat-mini">
            <h4>Saíram</h4>
            <p class="stat-number-small"><?= $stats['enviado'] ?></p>
        </div>
        <div class="stat-mini">
            <h4>Entregues</h4>
            <p class="stat-number-small"><?= $stats['entregue'] ?></p>
        </div>
        <div class="stat-mini">
            <h4>Cancelados</h4>
            <p class="stat-number-small"><?= $stats['cancelado'] ?></p>
        </div>
    </div>

    <div class="filtros">
        <a href="<?= url('admin/pedidos.php?filtro=todos') ?>" 
           class="filtro-btn <?= $filtro === 'todos' ? 'active' : '' ?>">
            Todos
        </a>
        <a href="<?= url('admin/pedidos.php?filtro=pendente') ?>" 
           class="filtro-btn <?= $filtro === 'pendente' ? 'active' : '' ?>">
            Pendentes
        </a>
        <a href="<?= url('admin/pedidos.php?filtro=processando') ?>" 
           class="filtro-btn <?= $filtro === 'processando' ? 'active' : '' ?>">
            Em Preparo
        </a>
        <a href="<?= url('admin/pedidos.php?filtro=enviado') ?>" 
           class="filtro-btn <?= $filtro === 'enviado' ? 'active' : '' ?>">
            Saíram
        </a>
        <a href="<?= url('admin/pedidos.php?filtro=entregue') ?>" 
           class="filtro-btn <?= $filtro === 'entregue' ? 'active' : '' ?>">
            Entregues
        </a>
    </div>

    <div class="pedidos-admin-lista">
        <?php if($pedidos): ?>
            <?php foreach($pedidos as $p): ?>
                <?php
                // Buscar itens
                $stmt = $pdo->prepare("
                    SELECT pi.*, s.titulo
                    FROM pedido_item pi
                    JOIN servico s ON pi.servico_id = s.id
                    WHERE pi.pedido_id = ?
                ");
                $stmt->execute([$p['id']]);
                $itens = $stmt->fetchAll();
                ?>
                
                <div class="pedido-admin-card">
                    <div class="pedido-admin-header">
                        <div>
                            <h3>Pedido #<?= str_pad($p['id'], 6, '0', STR_PAD_LEFT) ?></h3>
                            <p class="pedido-cliente">
                                👤 <?= esc($p['cliente_nome']) ?> | 
                                📧 <?= esc($p['cliente_email']) ?>
                            </p>
                            <p class="pedido-data">
                                🕐 <?= date('d/m/Y H:i', strtotime($p['criado_em'])) ?>
                            </p>
                        </div>
                        <div class="pedido-admin-valor">
                            <strong>R$ <?= number_format($p['total'], 2, ',', '.') ?></strong>
                        </div>
                    </div>

                    <div class="pedido-admin-body">
                        <div class="pedido-admin-itens">
                            <strong>Itens:</strong>
                            <?php foreach($itens as $item): ?>
                                <span class="item-tag">
                                    <?= esc($item['titulo']) ?> (<?= $item['quantidade'] ?>x)
                                </span>
                            <?php endforeach; ?>
                        </div>

                        <div class="pedido-admin-endereco">
                            <strong>📍 Endereço:</strong>
                            <p><?= nl2br(esc($p['endereco'])) ?></p>
                        </div>

                        <?php if($p['observacoes']): ?>
                            <div class="pedido-admin-obs">
                                <strong>📝 Observações:</strong>
                                <p><?= nl2br(esc($p['observacoes'])) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="pedido-admin-footer">
                        <form method="post" action="<?= url('admin/pedidos.php') ?>" class="status-form">
                            <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
                            <label>Status:</label>
                            <select name="status">
                                <option value="pendente" <?= $p['status'] === 'pendente' ? 'selected' : '' ?>>
                                    Pendente
                                </option>
                                <option value="processando" <?= $p['status'] === 'processando' ? 'selected' : '' ?>>
                                    Em Preparo
                                </option>
                                <option value="enviado" <?= $p['status'] === 'enviado' ? 'selected' : '' ?>>
                                    Saiu para Entrega
                                </option>
                                <option value="entregue" <?= $p['status'] === 'entregue' ? 'selected' : '' ?>>
                                    Entregue
                                </option>
                                <option value="cancelado" <?= $p['status'] === 'cancelado' ? 'selected' : '' ?>>
                                    Cancelado
                                </option>
                            </select>
                            <button type="submit" name="atualizar_status" class="btn-small">Atualizar</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <p>📭 Nenhum pedido encontrado</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.pedidos-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin: 2rem 0;
}

.stat-mini {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: var(--shadow);
    text-align: center;
}

.stat-mini h4 {
    margin: 0 0 0.5rem 0;
    color: #666;
    font-size: 0.9rem;
}

.stat-number-small {
    margin: 0;
    font-size: 2rem;
    font-weight: bold;
    color: var(--primary);
}

.filtros {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 2rem;
}

.filtro-btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    background: white;
    color: var(--text-dark);
    text-decoration: none;
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
}

.filtro-btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.filtro-btn.active {
    background: var(--primary);
    color: var(--bg-dark);
    font-weight: bold;
}

.pedidos-admin-lista {
    display: grid;
    gap: 1.5rem;
}

.pedido-admin-card {
    background: white;
    border-radius: 10px;
    box-shadow: var(--shadow);
    overflow: hidden;
}

.pedido-admin-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    padding: 1.5rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 2px solid var(--border);
}

.pedido-admin-header h3 {
    margin: 0 0 0.5rem 0;
    color: var(--secondary);
}

.pedido-cliente,
.pedido-data {
    margin: 0.25rem 0;
    color: #666;
    font-size: 0.9rem;
}

.pedido-admin-valor {
    font-size: 1.5rem;
    color: var(--primary);
}

.pedido-admin-body {
    padding: 1.5rem;
}

.pedido-admin-itens,
.pedido-admin-endereco,
.pedido-admin-obs {
    margin-bottom: 1rem;
}

.pedido-admin-itens strong,
.pedido-admin-endereco strong,
.pedido-admin-obs strong {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--secondary);
}

.item-tag {
    display: inline-block;
    background: #f0f0f0;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    margin: 0.25rem 0.25rem 0.25rem 0;
    color: #666;
}

.pedido-admin-endereco p,
.pedido-admin-obs p {
    margin: 0;
    color: #666;
    line-height: 1.6;
}

.pedido-admin-footer {
    padding: 1.5rem;
    background: #f8f9fa;
    border-top: 2px solid var(--border);
}

.status-form {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.status-form label {
    font-weight: 600;
    color: var(--secondary);
    margin: 0;
}

.status-form select {
    padding: 0.5rem;
    border-radius: 5px;
    border: 2px solid var(--border);
    font-size: 1rem;
    margin: 0;
}

@media (max-width: 768px) {
    .pedido-admin-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .status-form {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>

<?php require_once __DIR__ . '/../footer.php'; ?>