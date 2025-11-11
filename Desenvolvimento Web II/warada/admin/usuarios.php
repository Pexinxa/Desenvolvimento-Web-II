<?php
require_once __DIR__ . '/../init.php';
require_login('admin');

$action = $_GET['action'] ?? null;

// Adicionar novo usuário
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $nivel = $_POST['nivel'] ?? 'usuario';

    if ($nome && $email && $senha) {
        // Verificar se email já existe
        $stmt = $pdo->prepare("SELECT id FROM usuario WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Este email já está cadastrado!";
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuario (nome,email,senha,nivel) VALUES (?,?,?,?)");
            $stmt->execute([$nome,$email,$hash,$nivel]);
            header('Location: usuarios.php');
            exit;
        }
    }
}

// Editar usuário
if ($action === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $nivel = $_POST['nivel'];
        $senha = $_POST['senha'] ?? '';

        if ($senha) {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuario SET nome=?, email=?, senha=?, nivel=? WHERE id=?");
            $stmt->execute([$nome,$email,$hash,$nivel,$id]);
        } else {
            $stmt = $pdo->prepare("UPDATE usuario SET nome=?, email=?, nivel=? WHERE id=?");
            $stmt->execute([$nome,$email,$nivel,$id]);
        }
        header('Location: usuarios.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE id=?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    require_once __DIR__ . '/../header.php';
    ?>
    <div class="admin-container">
        <a href="<?= url('admin/usuarios.php') ?>" class="btn-back">← Voltar</a>
        
        <div class="admin-form-container">
            <h2>✏️ Editar Usuário</h2>
            <form method="post" class="admin-form">
                <div class="form-group">
                    <label>Nome Completo:</label>
                    <input type="text" name="nome" value="<?=esc($user['nome'])?>" required>
                </div>
                
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" value="<?=esc($user['email'])?>" required>
                </div>
                
                <div class="form-group">
                    <label>Nível de Acesso:</label>
                    <select name="nivel">
                        <option value="usuario" <?=$user['nivel']==='usuario'?'selected':''?>>👤 Usuário</option>
                        <option value="admin" <?=$user['nivel']==='admin'?'selected':''?>>⚙️ Admin</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Nova Senha (deixe em branco para manter a atual):</label>
                    <input type="password" name="senha" placeholder="Digite apenas se quiser alterar">
                    <small>Mínimo 6 caracteres</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">💾 Salvar Alterações</button>
                    <a href="<?= url('admin/usuarios.php') ?>" class="btn-secondary">❌ Cancelar</a>
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
    // Não permitir excluir o próprio usuário
    if ($id != $_SESSION['user']['id']) {
        $pdo->prepare("DELETE FROM usuario WHERE id=?")->execute([$id]);
    }
    header('Location: usuarios.php');
    exit;
}

// Listar
$stmt = $pdo->query("SELECT * FROM usuario ORDER BY criado_em DESC");
$users = $stmt->fetchAll();

$total_usuarios = count($users);
$total_admins = count(array_filter($users, fn($u) => $u['nivel'] === 'admin'));
$total_clientes = count(array_filter($users, fn($u) => $u['nivel'] === 'usuario'));

require_once __DIR__ . '/../header.php';
?>

<div class="admin-container">
    <h1>👥 Gerenciar Usuários</h1>
    
    <a href="<?= url('admin/dashboard.php') ?>" class="btn-back">← Voltar ao Painel</a>

    <div class="stats-mini-grid">
        <div class="stat-mini-card">
            <h4>Total de Usuários</h4>
            <p class="stat-number"><?= $total_usuarios ?></p>
        </div>
        <div class="stat-mini-card">
            <h4>Administradores</h4>
            <p class="stat-number"><?= $total_admins ?></p>
        </div>
        <div class="stat-mini-card">
            <h4>Clientes</h4>
            <p class="stat-number"><?= $total_clientes ?></p>
        </div>
    </div>

    <?php if(!empty($error)): ?>
        <div class="alert alert-error"><?= esc($error) ?></div>
    <?php endif; ?>

    <div class="admin-form-container">
        <h2>➕ Adicionar Novo Usuário</h2>
        <form method="post" action="?action=add" class="admin-form">
            <div class="form-group">
                <label>Nome Completo:</label>
                <input type="text" name="nome" required>
            </div>
            
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Senha:</label>
                <input type="password" name="senha" required minlength="6">
                <small>Mínimo 6 caracteres</small>
            </div>
            
            <div class="form-group">
                <label>Nível de Acesso:</label>
                <select name="nivel">
                    <option value="usuario">👤 Usuário</option>
                    <option value="admin">⚙️ Admin</option>
                </select>
            </div>
            
            <button type="submit" class="btn">💾 Cadastrar Usuário</button>
        </form>
    </div>

    <div class="admin-list">
        <h2>📋 Lista de Usuários</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Nível</th>
                    <th>Cadastro</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $u): ?>
                    <tr>
                        <td><?=esc($u['id'])?></td>
                        <td><strong><?=esc($u['nome'])?></strong></td>
                        <td><?=esc($u['email'])?></td>
                        <td>
                            <?php if($u['nivel'] === 'admin'): ?>
                                <span class="badge badge-admin">⚙️ Admin</span>
                            <?php else: ?>
                                <span class="badge badge-cliente">👤 Cliente</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($u['criado_em'])) ?></td>
                        <td class="actions">
                            <a href="?action=edit&id=<?=$u['id']?>" class="btn-edit">✏️ Editar</a>
                            <?php if($u['id'] != $_SESSION['user']['id']): ?>
                                <a href="?action=delete&id=<?=$u['id']?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Tem certeza que deseja excluir este usuário?')">
                                    🗑️ Excluir
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.stats-mini-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin: 2rem 0;
}

.stat-mini-card {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: var(--shadow);
    text-align: center;
}

.stat-mini-card h4 {
    margin: 0 0 1rem 0;
    color: #666;
    font-size: 0.9rem;
}

.stat-mini-card .stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: var(--primary);
    margin: 0;
}
</style>

<?php require_once __DIR__ . '/../footer.php'; ?>