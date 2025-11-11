<?php
require_once __DIR__ . '/../init.php';
require_login('admin');

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM avaliacao WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: avaliacoes.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM avaliacao ORDER BY criado_em DESC");
$avaliacoes = $stmt->fetchAll();

// Estatísticas
$total_avaliacoes = count($avaliacoes);
$media_estrelas = 0;
if ($total_avaliacoes > 0) {
    $soma_estrelas = array_sum(array_column($avaliacoes, 'estrelas'));
    $media_estrelas = round($soma_estrelas / $total_avaliacoes, 1);
}

$avaliacoes_5estrelas = count(array_filter($avaliacoes, fn($a) => $a['estrelas'] == 5));

require_once __DIR__ . '/../header.php';
?>

<div class="admin-container">
    <h1>⭐ Gerenciar Avaliações</h1>
    
    <a href="<?= url('admin/dashboard.php') ?>" class="btn-back">← Voltar ao Painel</a>

    <div class="stats-mini-grid">
        <div class="stat-mini-card">
            <h4>Total de Avaliações</h4>
            <p class="stat-number"><?= $total_avaliacoes ?></p>
        </div>
        <div class="stat-mini-card">
            <h4>Média de Estrelas</h4>
            <p class="stat-number" style="color: var(--primary);"><?= $media_estrelas ?> ⭐</p>
        </div>
        <div class="stat-mini-card">
            <h4>Avaliações 5⭐</h4>
            <p class="stat-number" style="color: var(--success);"><?= $avaliacoes_5estrelas ?></p>
        </div>
    </div>

    <div class="admin-list">
        <h2>📋 Lista de Avaliações</h2>
        
        <?php if($avaliacoes): ?>
            <?php foreach($avaliacoes as $a): ?>
                <div class="review-card-admin">
                    <div class="review-header">
                        <div>
                            <h3><?=esc($a['nome'])?></h3>
                            <p class="stars"><?=str_repeat('⭐', (int)$a['estrelas'])?></p>
                        </div>
                        <span class="review-date">
                            <?= date('d/m/Y H:i', strtotime($a['criado_em'])) ?>
                        </span>
                    </div>
                    
                    <div class="review-body">
                        <p><?=nl2br(esc($a['comentario']))?></p>
                    </div>
                    
                    <div class="review-actions">
                        <a href="?delete=<?=$a['id']?>" 
                           class="btn-delete" 
                           onclick="return confirm('Tem certeza que deseja excluir esta avaliação?')">
                            🗑️ Excluir Avaliação
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <p>Nenhuma avaliação ainda</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.review-card-admin {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    margin-bottom: 1.5rem;
    box-shadow: var(--shadow);
    border-left: 4px solid var(--primary);
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--border);
}

.review-header h3 {
    margin: 0 0 0.5rem 0;
    color: var(--secondary);
}

.stars {
    color: var(--primary);
    font-size: 1.2rem;
    margin: 0;
}

.review-date {
    color: #999;
    font-size: 0.85rem;
}

.review-body {
    margin: 1rem 0;
    color: var(--text-dark);
    line-height: 1.6;
}

.review-body p {
    margin: 0;
}

.review-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border);
}
</style>

<?php require_once __DIR__ . '/../footer.php'; ?>