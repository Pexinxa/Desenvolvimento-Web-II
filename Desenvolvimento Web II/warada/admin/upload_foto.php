<?php
require_once __DIR__ . '/../init.php';
require_login('admin');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto'])) {
    $servico_id = (int)$_POST['servico_id'];
    $file = $_FILES['foto'];
    
    // Validações
    $allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Erro ao fazer upload do arquivo.";
    } elseif (!in_array($file['type'], $allowed)) {
        $error = "Apenas imagens JPG, PNG ou WEBP são permitidas.";
    } elseif ($file['size'] > $max_size) {
        $error = "Arquivo muito grande. Máximo 5MB.";
    } else {
        // Criar pasta se não existir
        $upload_dir = __DIR__ . '/../assets/images/produtos/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Gerar nome único
        $extensao = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nome_arquivo = 'produto_' . $servico_id . '_' . time() . '.' . $extensao;
        $caminho_completo = $upload_dir . $nome_arquivo;
        
        // Fazer upload
        if (move_uploaded_file($file['tmp_name'], $caminho_completo)) {
            // Atualizar banco
            $caminho_relativo = '/assets/images/produtos/' . $nome_arquivo;
            $stmt = $pdo->prepare("UPDATE servico SET foto = ? WHERE id = ?");
            $stmt->execute([$caminho_relativo, $servico_id]);
            
            $success = "Foto atualizada com sucesso!";
        } else {
            $error = "Erro ao salvar o arquivo.";
        }
    }
}

// Buscar todos os serviços
$stmt = $pdo->query("SELECT * FROM servico ORDER BY titulo");
$servicos = $stmt->fetchAll();

require_once __DIR__ . '/../header.php';
?>

<div class="admin-container">
    <h1>📸 Upload de Fotos dos Produtos</h1>
    
    <a href="<?= url('admin/servicos.php') ?>" class="btn-back">← Voltar aos Produtos</a>

    <?php if($success): ?>
        <div class="alert alert-success"><?= esc($success) ?></div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div class="alert alert-error"><?= esc($error) ?></div>
    <?php endif; ?>

    <div class="upload-grid">
        <?php foreach($servicos as $s): ?>
            <div class="produto-upload-card">
                <div class="produto-preview">
                    <?php if($s['foto']): ?>
                        <img src="<?= esc($s['foto']) ?>" alt="<?= esc($s['titulo']) ?>">
                        <span class="status-foto">✅ Com foto</span>
                    <?php else: ?>
                        <div class="sem-foto">📷 Sem foto</div>
                        <span class="status-foto alert">⚠️ Adicione uma foto</span>
                    <?php endif; ?>
                </div>
                
                <div class="produto-upload-info">
                    <h3><?= esc($s['titulo']) ?></h3>
                    <p>R$ <?= number_format($s['preco'], 2, ',', '.') ?></p>
                    
                    <form method="post" enctype="multipart/form-data" class="upload-form">
                        <input type="hidden" name="servico_id" value="<?= $s['id'] ?>">
                        <input type="file" name="foto" accept="image/*" required>
                        <button type="submit" class="btn-small">📤 Enviar Foto</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="instrucoes">
        <h2>📋 Instruções</h2>
        <ul>
            <li>✅ Formatos aceitos: JPG, PNG, WEBP</li>
            <li>✅ Tamanho máximo: 5MB por imagem</li>
            <li>✅ Recomendado: 800x800 pixels (quadrado)</li>
            <li>✅ Use fotos de boa qualidade para melhor apresentação</li>
        </ul>
    </div>
</div>

<style>
.upload-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
    margin: 2rem 0;
}

.produto-upload-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: var(--shadow);
}

.produto-preview {
    position: relative;
    height: 200px;
    background: #f0f0f0;
}

.produto-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.sem-foto {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    font-size: 3rem;
    color: #ccc;
}

.status-foto {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
}

.status-foto.alert {
    background: rgba(255, 193, 7, 0.9);
    color: #333;
}

.produto-upload-info {
    padding: 1.5rem;
}

.produto-upload-info h3 {
    margin: 0 0 0.5rem 0;
    color: var(--secondary);
}

.produto-upload-info p {
    color: var(--primary);
    font-size: 1.2rem;
    font-weight: bold;
    margin: 0 0 1rem 0;
}

.upload-form {
    margin-top: 1rem;
}

.upload-form input[type="file"] {
    width: 100%;
    padding: 0.5rem;
    margin-bottom: 0.5rem;
    border: 2px dashed var(--border);
    border-radius: 5px;
}

.instrucoes {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: var(--shadow);
    margin-top: 2rem;
}

.instrucoes h2 {
    color: var(--secondary);
    margin-bottom: 1rem;
}

.instrucoes ul {
    list-style: none;
    padding: 0;
}

.instrucoes li {
    padding: 0.5rem 0;
    color: #666;
    font-size: 1rem;
}
</style>

<?php require_once __DIR__ . '/../footer.php'; ?>