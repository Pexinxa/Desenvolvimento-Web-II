<?php
require_once __DIR__ . '/../init.php';
require_login('admin');

$action = $_GET['action'] ?? null;

// Adicionar
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $foto = trim($_POST['foto']);
    $preco = (float)$_POST['preco'];

    if ($titulo && $descricao && $preco > 0) {
        $stmt = $pdo->prepare("INSERT INTO servico (titulo,descricao,foto,preco) VALUES (?,?,?,?)");
        $stmt->execute([$titulo,$descricao,$foto,$preco]);
        $success = "Produto adicionado com sucesso!";
    } else {
        $error = "Preencha todos os campos obrigatórios!";
    }
}

// Editar
if ($action === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $titulo = trim($_POST['titulo']);
        $descricao = trim($_POST['descricao']);
        $foto = trim($_POST['foto']);
        $preco = (float)$_POST['preco'];
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        $stmt = $pdo->prepare("UPDATE servico SET titulo=?, descricao=?, foto=?, preco=?, ativo=? WHERE id=?");
        $stmt->execute([$titulo,$descricao,$foto,$preco,$ativo,$id]);
        header('Location: servicos.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM servico WHERE id=?");
    $stmt->execute([$id]);
    $serv = $stmt->fetch();

    require_once __DIR__ . '/../header.php';
    ?>
    <div class="admin-container">
        <a href="<?= url('admin/servicos.php') ?>" class="btn-back">← Voltar</a>
        
        <div class="admin-form-container">
            <h2>✏️ Editar Produto</h2>
            <form method="post" class="admin-form">
                <div class="form-group">
                    <label>Título do Produto: *</label>
                    <input type="text" name="titulo" value="<?=esc($serv['titulo'])?>" required>
                </div>
                
                <div class="form-group">
                    <label>Descrição: *</label>
                    <textarea name="descricao" rows="5" required><?=esc($serv['descricao'])?></textarea>
                    <small>Descreva os ingredientes e características do produto</small>
                </div>
                
                <div class="form-group">
                    <label>Preço (R$): *</label>
                    <input type="number" name="preco" step="0.01" min="0.01" 
                           value="<?=esc($serv['preco'])?>" required>
                </div>
                
                <div class="form-group">
                    <label>Foto (URL ou caminho):</label>
                    <input type="text" name="foto" value="<?=esc($serv['foto'])?>" 
                           placeholder="/assets/images/produto.jpg">
                    <small>Exemplo: /assets/images/kibe.jpg</small>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="ativo" <?= $serv['ativo'] ? 'checked' : '' ?>>
                        Produto ativo (visível no cardápio)
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">💾 Salvar Alterações</button>
                    <a href="<?= url('admin/servicos.php') ?>" class="btn-secondary">❌ Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/../footer.php';
    exit;
}

// Excluir
if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $pdo->prepare("DELETE FROM servico WHERE id=?")->execute([$id]);
    header('Location: servicos.php');
    exit;
}

// Toggle ativo/inativo
if ($action === 'toggle' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("UPDATE servico SET ativo = NOT ativo WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: servicos.php');
    exit;
}

// Listar
$stmt = $pdo->query("SELECT * FROM servico ORDER BY id DESC");
$servicos = $stmt->fetchAll();

$total_produtos = count($servicos);
$produtos_ativos = count(array_filter($servicos, fn($s) => $s['ativo']));
$produtos_inativos = $total_produtos - $produtos_ativos;

require_once __DIR__ . '/../header.php';
?>

<div class="admin-container">
    <h1>🍽️ Gerenciar Produtos</h1>
    
    <a href="<?= url('admin/dashboard.php') ?>" class="btn-back">← Voltar ao Painel</a>

    <div class="stats-mini-grid">
        <div class="stat-mini-card">
            <h4>Total de Produtos</h4>
            <p class="stat-number"><?= $total_produtos ?></p>
        </div>
        <div class="stat-mini-card">
            <h4>Produtos Ativos</h4>
            <p class="stat-number" style="color: var(--success);"><?= $produtos_ativos ?></p>
        </div>
        <div class="stat-mini-card">
            <h4>Produtos Inativos</h4>
            <p class="stat-number" style="color: #999;"><?= $produtos_inativos ?></p>
        </div>
    </div>

    <?php if(!empty($success)): ?>
        <div class="alert alert-success"><?= esc($success) ?></div>
    <?php endif; ?>
    
    <?php if(!empty($error)): ?>
        <div class="alert alert-error"><?= esc($error) ?></div>
    <?php endif; ?>

    <div class="admin-form-container">
        <h2>➕ Adicionar Novo Produto</h2>
        <form method="post" action="?action=add" class="admin-form">
            <div class="form-group">
                <label>Título do Produto: *</label>
                <input type="text" name="titulo" required placeholder="Ex: Kibe Frito">
            </div>
            
            <div class="form-group">
                <label>Descrição: *</label>
                <textarea name="descricao" rows="5" required 
                          placeholder="Descreva o produto, ingredientes, etc."></textarea>
            </div>
            
            <div class="form-group">
                <label>Preço (R$): *</label>
                <input type="number" name="preco" step="0.01" min="0.01" required 
                       placeholder="15.90">
            </div>
            
            <div class="form-group">
                <label>Foto (URL ou caminho):</label>
                <input type="text" name="foto" placeholder="/assets/images/produto.jpg">
                <small>Exemplo: /assets/images/kibe.jpg</small>
            </div>
            
            <button type="submit" class="btn">💾 Adicionar Produto</button>
        </form>
    </div>

    <div class="admin-list">
        <h2>📋 Lista de Produtos</h2>
        <div class="produtos-grid">
            <?php foreach($servicos as $s): ?>
                <div class="produto-card-admin <?= !$s['ativo'] ? 'inativo' : '' ?>">
                    <?php if($s['foto']): ?>
                        <img src="<?=esc($s['foto'])?>" alt="<?=esc($s['titulo'])?>">
                    <?php else: ?>
                        <div class="no-image">Sem imagem</div>
                    <?php endif; ?>
                    
                    <div class="produto-info-admin">
                        <h3><?=esc($s['titulo'])?></h3>
                        <p class="descricao"><?=esc(substr($s['descricao'], 0, 100))?>...</p>
                        <p class="preco">R$ <?= number_format($s['preco'], 2, ',', '.') ?></p>
                        
                        <div class="status-badge">
                            <?php if($s['ativo']): ?>
                                <span class="badge-ativo">✅ Ativo</span>
                            <?php else: ?>
                                <span class="badge-inativo">❌ Inativo</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="produto-actions">
                        <a href="?action=toggle&id=<?=$s['id']?>" class="btn-toggle">
                            <?= $s['ativo'] ? '🔒 Desativar' : '🔓 Ativar' ?>
                        </a>
                        <a href="?action=edit&id=<?=$s['id']?>" class="btn-edit">✏️ Editar</a>
                        <a href="?action=delete&id=<?=$s['id']?>" 
                           class="btn-delete" 
                           onclick="return confirm('Tem certeza que deseja excluir este produto?')">
                            🗑️ Excluir
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.produtos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.produto-card-admin {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: transform 0.3s ease;
}

.produto-card-admin:hover {
    transform: translateY(-5px);
}

.produto-card-admin.inativo {
    opacity: 0.6;
    border: 2px solid #ccc;
}

.produto-card-admin img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.produto-info-admin {
    padding: 1.5rem;
}

.produto-info-admin h3 {
    margin: 0 0 0.5rem 0;
    color: var(--secondary);
}

.produto-info-admin .descricao {
    color: #666;
    font-size: 0.9rem;
    margin: 0.5rem 0;
    line-height: 1.6;
}

.produto-info-admin .preco {
    font-size: 1.5rem;
    color: var(--primary);
    font-weight: bold;
    margin: 1rem 0;
}

.status-badge {
    margin: 1rem 0;
}

.badge-ativo {
    background: #d4edda;
    color: #155724;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-inativo {
    background: #f8d7da;
    color: #721c24;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.produto-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding: 1rem 1.5rem;
    background: #f8f9fa;
    border-top: 2px solid var(--border);
}

.btn-toggle {
    padding: 0.6rem 1rem;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 600;
    text-align: center;
    transition: all 0.3s ease;
    background: #6c757d;
    color: white;
}

.btn-toggle:hover {
    background: #5a6268;
}
</style>

<?php require_once __DIR__ . '/../footer.php'; ?>