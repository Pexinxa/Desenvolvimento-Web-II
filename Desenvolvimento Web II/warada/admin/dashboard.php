<?php
require_once __DIR__ . '/../header.php';
require_login('admin'); // só admin acessa

// Estatísticas
$total_usuarios = $pdo->query("SELECT COUNT(*) FROM usuario")->fetchColumn();
$total_servicos = $pdo->query("SELECT COUNT(*) FROM servico")->fetchColumn();
$total_avaliacoes = $pdo->query("SELECT COUNT(*) FROM avaliacao")->fetchColumn();
$total_mensagens = $pdo->query("SELECT COUNT(*) FROM contato")->fetchColumn();
$total_pedidos = $pdo->query("SELECT COUNT(*) FROM pedido")->fetchColumn();
$pedidos_pendentes = $pdo->query("SELECT COUNT(*) FROM pedido WHERE status = 'pendente'")->fetchColumn();
$pedidos_hoje = $pdo->query("SELECT COUNT(*) FROM pedido WHERE DATE(criado_em) = CURDATE()")->fetchColumn();

// Total de vendas
$total_vendas = $pdo->query("SELECT IFNULL(SUM(total), 0) FROM pedido WHERE status != 'cancelado'")->fetchColumn();

// Últimas atividades
$ultimos_usuarios = $pdo->query("SELECT nome, email, criado_em FROM usuario ORDER BY criado_em DESC LIMIT 5")->fetchAll();
$ultimas_mensagens = $pdo->query("SELECT nome, email, mensagem, criado_em FROM contato ORDER BY criado_em DESC LIMIT 5")->fetchAll();
$ultimos_pedidos = $pdo->query("
    SELECT p.id, p.total, p.status, p.criado_em, u.nome as cliente_nome
    FROM pedido p
    JOIN usuario u ON p.usuario_id = u.id
    ORDER BY p.criado_em DESC
    LIMIT 5
")->fetchAll();
?>

<div class="admin-container">
    <h1>🎯 Painel Administrativo</h1>
    <p class="welcome-text">Bem-vindo, <?= esc($_SESSION['user']['nome']) ?>! 👋</p>
    
    <div class="stats-grid">
        <div class="stat-card stat-usuarios">
            <h3>👥 Usuários</h3>
            <p class="stat-number"><?= $total_usuarios ?></p>
            <a href="<?= url('admin/usuarios.php') ?>" class="btn-small">Gerenciar →</a>
        </div>
        
        <div class="stat-card stat-produtos">
            <h3>🍽️ Produtos</h3>
            <p class="stat-number"><?= $total_servicos ?></p>
            <a href="<?= url('admin/servicos.php') ?>" class="btn-small">Gerenciar →</a>
            <a href="<?= url('admin/upload_foto.php') ?>" class="btn-small" style="background:#6c757d;">📸 Upload Fotos</a>
        </div>
        
        <div class="stat-card stat-pedidos">
            <h3>📦 Pedidos</h3>
            <p class="stat-number"><?= $total_pedidos ?></p>
            <a href="<?= url('admin/pedidos.php') ?>" class="btn-small">Gerenciar →</a>
        </div>
        
        <div class="stat-card stat-avaliacoes">
            <h3>⭐ Avaliações</h3>
            <p class="stat-number"><?= $total_avaliacoes ?></p>
            <a href="<?= url('admin/avaliacoes.php') ?>" class="btn-small">Ver todas →</a>
        </div>
        
        <div class="stat-card stat-mensagens">
            <h3>📧 Mensagens</h3>
            <p class="stat-number"><?= $total_mensagens ?></p>
            <a href="<?= url('admin/mensagens.php') ?>" class="btn-small">Ver todas →</a>
        </div>
        
        <div class="stat-card stat-vendas">
            <h3>💰 Total de Vendas</h3>
            <p class="stat-number">R$ <?= number_format($total_vendas, 2, ',', '.') ?></p>
            <p class="stat-subtitle">Pedidos não cancelados</p>
        </div>
    </div>

    <div class="alerts-grid">
        <?php if($pedidos_pendentes > 0): ?>
            <div class="alert-card alert-warning">
                <h4>⚠️ Atenção Necessária</h4>
                <p><strong><?= $pedidos_pendentes ?></strong> pedido(s) pendente(s) aguardando processamento</p>
                <a href="<?= url('admin/pedidos.php?filtro=pendente') ?>" class="btn-small">Ver Pendentes</a>
            </div>
        <?php endif; ?>
        
        <?php if($pedidos_hoje > 0): ?>
            <div class="alert-card alert-success">
                <h4>🎉 Pedidos Hoje</h4>
                <p><strong><?= $pedidos_hoje ?></strong> pedido(s) recebido(s) hoje</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="admin-sections">
        <section class="admin-section">
            <h2>📦 Últimos Pedidos</h2>
            <?php if($ultimos_pedidos): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($ultimos_pedidos as $p): ?>
                            <tr>
                                <td><strong>#<?= str_pad($p['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                                <td><?= esc($p['cliente_nome']) ?></td>
                                <td>R$ <?= number_format($p['total'], 2, ',', '.') ?></td>
                                <td>
                                    <span class="badge-status badge-<?= $p['status'] ?>">
                                        <?= $p['status'] ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($p['criado_em'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="<?= url('admin/pedidos.php') ?>" class="ver-todos">Ver todos os pedidos →</a>
            <?php else: ?>
                <p class="empty-state">Nenhum pedido ainda.</p>
            <?php endif; ?>
        </section>

        <section class="admin-section">
            <h2>👥 Últimos Usuários Registrados</h2>
            <?php if($ultimos_usuarios): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($ultimos_usuarios as $u): ?>
                            <tr>
                                <td><?= esc($u['nome']) ?></td>
                                <td><?= esc($u['email']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($u['criado_em'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="<?= url('admin/usuarios.php') ?>" class="ver-todos">Ver todos os usuários →</a>
            <?php else: ?>
                <p class="empty-state">Nenhum usuário cadastrado.</p>
            <?php endif; ?>
        </section>

        <section class="admin-section">
            <h2>📧 Últimas Mensagens de Contato</h2>
            <?php if($ultimas_mensagens): ?>
                <?php foreach($ultimas_mensagens as $m): ?>
                    <div class="message-card">
                        <div class="message-header">
                            <strong><?= esc($m['nome']) ?></strong>
                            <span class="message-date"><?= date('d/m/Y H:i', strtotime($m['criado_em'])) ?></span>
                        </div>
                        <p class="message-email"><?= esc($m['email']) ?></p>
                        <p class="message-text"><?= esc(substr($m['mensagem'], 0, 100)) ?>...</p>
                    </div>
                <?php endforeach; ?>
                <a href="<?= url('admin/mensagens.php') ?>" class="ver-todos">Ver todas as mensagens →</a>
            <?php else: ?>
                <p class="empty-state">Nenhuma mensagem recebida.</p>
            <?php endif; ?>
        </section>
    </div>
    
</div>
</div>

<style>
.welcome-text {
    color: #666;
    font-size: 1.1rem;
    margin-bottom: 2rem;
}

.stat-card {
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    opacity: 0.1;
}

.stat-usuarios::before { background: #007bff; }
.stat-produtos::before { background: #28a745; }
.stat-pedidos::before { background: #ffc107; }
.stat-avaliacoes::before { background: #fd7e14; }
.stat-mensagens::before { background: #6610f2; }
.stat-vendas::before { background: #20c997; }

.stat-subtitle {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.8);
    margin: 0.5rem 0 0 0;
}

.alerts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin: 2rem 0;
}

.alert-card {
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: var(--shadow);
}

.alert-warning {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border-left: 4px solid #ffc107;
}

.alert-success {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    border-left: 4px solid #28a745;
}

.alert-card h4 {
    margin: 0 0 0.5rem 0;
    color: var(--secondary);
}

.alert-card p {
    margin: 0 0 1rem 0;
    color: #666;
}

.badge-status {
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: capitalize;
}

.ver-todos {
    display: inline-block;
    margin-top: 1rem;
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.ver-todos:hover {
    color: var(--primary-dark);
    transform: translateX(5px);
}
</style>

<?php require_once __DIR__ . '/../footer.php'; ?>