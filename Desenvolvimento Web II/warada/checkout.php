<?php
require_once __DIR__ . '/header.php';
require_login();

$usuario_id = $_SESSION['user']['id'];

// Buscar itens do carrinho
$stmt = $pdo->prepare("
    SELECT c.*, s.titulo, s.descricao, s.foto, s.preco
    FROM carrinho c
    JOIN servico s ON c.servico_id = s.id
    WHERE c.usuario_id = ?
");
$stmt->execute([$usuario_id]);
$itens = $stmt->fetchAll();

// Se carrinho vazio, redirecionar
if (empty($itens)) {
    header('Location: ' . url('carrinho.php'));
    exit;
}

// Calcular total
$total = 0;
foreach ($itens as $item) {
    $total += $item['preco'] * $item['quantidade'];
}

// Processar pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $endereco = trim($_POST['endereco'] ?? '');
    $observacoes = trim($_POST['observacoes'] ?? '');
    
    if (empty($endereco)) {
        $error = "Por favor, informe o endereço de entrega.";
    } else {
        try {
            $pdo->beginTransaction();
            
            // Criar pedido
            $stmt = $pdo->prepare("
                INSERT INTO pedido (usuario_id, total, endereco, observacoes, status) 
                VALUES (?, ?, ?, ?, 'pendente')
            ");
            $stmt->execute([$usuario_id, $total, $endereco, $observacoes]);
            $pedido_id = $pdo->lastInsertId();
            
            // Adicionar itens ao pedido
            $stmt = $pdo->prepare("
                INSERT INTO pedido_item (pedido_id, servico_id, quantidade, preco_unitario, subtotal) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            foreach ($itens as $item) {
                $subtotal = $item['preco'] * $item['quantidade'];
                $stmt->execute([
                    $pedido_id,
                    $item['servico_id'],
                    $item['quantidade'],
                    $item['preco'],
                    $subtotal
                ]);
            }
            
            // Limpar carrinho
            $stmt = $pdo->prepare("DELETE FROM carrinho WHERE usuario_id = ?");
            $stmt->execute([$usuario_id]);
            
            $pdo->commit();
            
            // Redirecionar para página de sucesso
            header('Location: ' . url('pedido_confirmado.php?pedido_id=' . $pedido_id));
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Erro ao processar pedido. Tente novamente.";
        }
    }
}
?>

<div class="checkout-container">
    <h1>🛒 Finalizar Pedido</h1>
    
    <?php if(!empty($error)): ?>
        <div class="alert alert-error"><?= esc($error) ?></div>
    <?php endif; ?>

    <div class="checkout-grid">
        <div class="checkout-form-section">
            <form method="post" class="checkout-form">
                <div class="form-section">
                    <h2>📍 Endereço de Entrega</h2>
                    <div class="form-group">
                        <label>Endereço Completo: *</label>
                        <textarea name="endereco" rows="4" required 
                                  placeholder="Ex: Rua das Flores, 123, Apto 45&#10;Bairro: Centro&#10;Cidade: São Paulo - SP&#10;CEP: 12345-678"></textarea>
                        <small>Informe rua, número, complemento, bairro, cidade e CEP</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Observações (opcional):</label>
                        <textarea name="observacoes" rows="3" 
                                  placeholder="Ex: Interfone 45, pedir para deixar com o porteiro, sem cebola, etc."></textarea>
                        <small>Alguma informação adicional para a entrega ou preparo?</small>
                    </div>
                </div>

                <div class="form-section">
                    <h2>💳 Forma de Pagamento</h2>
                    <div class="pagamento-info">
                        <div class="pagamento-opcao selected">
                            <span class="icon">💵</span>
                            <div>
                                <strong>Pagamento na Entrega</strong>
                                <p>Você pode pagar com dinheiro ou cartão no momento da entrega</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="<?= url('carrinho.php') ?>" class="btn-secondary">
                        ← Voltar ao Carrinho
                    </a>
                    <button type="submit" class="btn btn-finalizar">
                        Confirmar Pedido 🚀
                    </button>
                </div>
            </form>
        </div>

        <div class="checkout-resumo">
            <h2>📦 Resumo do Pedido</h2>
            
            <div class="resumo-itens">
                <?php foreach($itens as $item): ?>
                    <div class="resumo-item">
                        <div class="item-info-mini">
                            <?php if($item['foto']): ?>
                                <img src="<?= esc($item['foto']) ?>" alt="<?= esc($item['titulo']) ?>">
                            <?php endif; ?>
                            <div>
                                <strong><?= esc($item['titulo']) ?></strong>
                                <p>Qtd: <?= $item['quantidade'] ?>x</p>
                            </div>
                        </div>
                        <span class="item-preco">
                            R$ <?= number_format($item['preco'] * $item['quantidade'], 2, ',', '.') ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="resumo-valores">
                <div class="resumo-linha">
                    <span>Subtotal:</span>
                    <span>R$ <?= number_format($total, 2, ',', '.') ?></span>
                </div>
                <div class="resumo-linha">
                    <span>Taxa de Entrega:</span>
                    <span class="gratis">Grátis</span>
                </div>
                <div class="resumo-total">
                    <span>Total:</span>
                    <span>R$ <?= number_format($total, 2, ',', '.') ?></span>
                </div>
            </div>

            <div class="info-entrega">
                <h4>ℹ️ Informações Importantes</h4>
                <ul>
                    <li>⏱️ Tempo estimado: 30-45 min</li>
                    <li>📦 Entrega grátis</li>
                    <li>💳 Pagamento na entrega</li>
                    <li>🔔 Você receberá atualizações do pedido</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.checkout-container {
    max-width: 1200px;
    margin: 0 auto;
}

.checkout-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    margin-top: 2rem;
}

.checkout-form-section {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: var(--shadow);
}

.form-section {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 2px solid var(--border);
}

.form-section:last-of-type {
    border-bottom: none;
}

.form-section h2 {
    color: var(--secondary);
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
}

.pagamento-info {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.pagamento-opcao {
    display: flex;
    gap: 1rem;
    padding: 1.5rem;
    background: #f9f9f9;
    border-radius: 8px;
    border: 2px solid var(--border);
    transition: all 0.3s ease;
}

.pagamento-opcao.selected {
    border-color: var(--primary);
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.1) 0%, rgba(212, 175, 55, 0.05) 100%);
}

.pagamento-opcao .icon {
    font-size: 2rem;
}

.pagamento-opcao strong {
    display: block;
    color: var(--secondary);
    margin-bottom: 0.5rem;
}

.pagamento-opcao p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

.form-actions .btn,
.form-actions .btn-secondary {
    flex: 1;
    text-align: center;
    padding: 1rem;
}

.btn-finalizar {
    font-size: 1.1rem;
}

.checkout-resumo {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: var(--shadow);
    height: fit-content;
    position: sticky;
    top: 100px;
}

.checkout-resumo h2 {
    color: var(--secondary);
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--border);
}

.resumo-itens {
    margin-bottom: 1.5rem;
}

.resumo-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid var(--border);
}

.resumo-item:last-child {
    border-bottom: none;
}

.item-info-mini {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.item-info-mini img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 5px;
}

.item-info-mini strong {
    display: block;
    color: var(--secondary);
    font-size: 0.95rem;
}

.item-info-mini p {
    margin: 0.25rem 0 0 0;
    color: #666;
    font-size: 0.85rem;
}

.item-preco {
    font-weight: 600;
    color: var(--primary);
}

.resumo-valores {
    padding: 1.5rem 0;
    border-top: 2px solid var(--border);
}

.resumo-linha {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    color: #666;
}

.resumo-total {
    display: flex;
    justify-content: space-between;
    padding: 1.5rem 0;
    margin-top: 1rem;
    border-top: 2px solid var(--border);
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--secondary);
}

.info-entrega {
    margin-top: 2rem;
    padding: 1.5rem;
    background: #f9f9f9;
    border-radius: 8px;
}

.info-entrega h4 {
    margin: 0 0 1rem 0;
    color: var(--secondary);
}

.info-entrega ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.info-entrega li {
    padding: 0.5rem 0;
    color: #666;
}

@media (max-width: 768px) {
    .checkout-grid {
        grid-template-columns: 1fr;
    }
    
    .checkout-resumo {
        position: static;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<?php require_once __DIR__ . '/footer.php'; ?>