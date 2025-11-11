-- ========================================
-- WARADA EXPRESS - BANCO DE DADOS COMPLETO
-- ========================================

-- 1. Criar o banco de dados
CREATE DATABASE IF NOT EXISTS sistema CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE sistema;

-- 2. Criar tabela de usuários (CORRIGIDO: nivel agora usa 'usuario' em vez de 'cliente')
CREATE TABLE IF NOT EXISTS usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nivel ENUM('usuario', 'admin') DEFAULT 'usuario',
    foto VARCHAR(255) DEFAULT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Criar tabela de serviços/produtos (ADICIONADO: campo preco que estava faltando)
CREATE TABLE IF NOT EXISTS servico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT,
    foto VARCHAR(255) DEFAULT NULL,
    preco DECIMAL(10,2) DEFAULT 0.00,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Criar tabela de avaliações
CREATE TABLE IF NOT EXISTS avaliacao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    estrelas INT NOT NULL CHECK (estrelas BETWEEN 1 AND 5),
    comentario TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Criar tabela de contato
CREATE TABLE IF NOT EXISTS contato (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    mensagem TEXT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. NOVA TABELA: Pedidos
CREATE TABLE IF NOT EXISTS pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pendente', 'processando', 'enviado', 'entregue', 'cancelado') DEFAULT 'pendente',
    endereco TEXT NOT NULL,
    observacoes TEXT DEFAULT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. NOVA TABELA: Itens do Pedido
CREATE TABLE IF NOT EXISTS pedido_item (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    servico_id INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedido(id) ON DELETE CASCADE,
    FOREIGN KEY (servico_id) REFERENCES servico(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. NOVA TABELA: Carrinho
CREATE TABLE IF NOT EXISTS carrinho (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    servico_id INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_carrinho (usuario_id, servico_id),
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE,
    FOREIGN KEY (servico_id) REFERENCES servico(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- DADOS INICIAIS
-- ========================================

-- Inserir usuário admin padrão (senha: admin123)
INSERT INTO usuario (nome, email, senha, nivel) VALUES 
('Administrador', 'admin@warada.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE nome = nome;

-- Inserir usuário teste (senha: 123456)
INSERT INTO usuario (nome, email, senha, nivel) VALUES 
('João Silva', 'joao@teste.com', '$2y$10$N9qo8uLOickgx2ZMRZoMye/IU8fKVIjQTJPKmVr6nJTH5xHNQPWmG', 'usuario')
ON DUPLICATE KEY UPDATE nome = nome;

-- Inserir produtos de exemplo
INSERT INTO servico (titulo, descricao, foto, preco) VALUES 
('Kibe Frito', 'Delicioso kibe frito com carne temperada, trigo e especiarias árabes. Servido crocante e suculento.', '/assets/images/kibe.jpg', 15.90),
('Esfiha de Carne', 'Esfiha aberta com carne moída temperada, tomate, cebola e especiarias. Massa fofinha e recheio generoso.', '/assets/images/esfiha.jpg', 8.50),
('Tabule', 'Salada árabe fresca com trigo, tomate, pepino, hortelã, salsinha e limão. Leve e refrescante.', '/assets/images/tabule.jpg', 12.00),
('Homus', 'Pasta cremosa de grão de bico com tahine, alho e limão. Acompanha pão sírio quentinho.', '/assets/images/homus.jpg', 10.00),
('Kafta', 'Espetinho de carne moída temperada com salsa, cebola e especiarias. Grelhado na perfeição.', '/assets/images/kafta.jpg', 18.90),
('Baklava', 'Doce folhado tradicional com nozes, pistache e calda de mel. Irresistível!', '/assets/images/baklava.jpg', 14.00),
('Shawarma', 'Wrap de frango ou carne com molho tahine, picles e vegetais frescos.', '/assets/images/shawarma.jpg', 22.00),
('Falafel', 'Bolinhos crocantes de grão de bico e ervas. Servido com molho tahine.', '/assets/images/falafel.jpg', 16.00)
ON DUPLICATE KEY UPDATE titulo = titulo;

-- Inserir avaliações de exemplo
INSERT INTO avaliacao (nome, estrelas, comentario) VALUES 
('Maria Silva', 5, 'Comida deliciosa e entrega super rápida! O kibe estava perfeito!'),
('João Santos', 4, 'Muito bom, recomendo o kibe e a esfiha. Voltarei a pedir.'),
('Ana Costa', 5, 'Melhor comida árabe da região! Tudo fresquinho e saboroso.'),
('Pedro Oliveira', 5, 'O homus é maravilhoso! E o atendimento é excelente.'),
('Carla Mendes', 4, 'Adorei o tabule! Delivery pontual e comida de qualidade.')
ON DUPLICATE KEY UPDATE nome = nome;


-- Atualizar produtos existentes com fotos 
UPDATE servico SET foto = 'https://images.unsplash.com/photo-1529006557810-274b9b2fc783?w=500' WHERE titulo LIKE '%Kibe%';
UPDATE servico SET foto = 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=500' WHERE titulo LIKE '%Esfiha%'
UPDATE servico SET foto = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=500' WHERE titulo LIKE '%Tabule%';;
UPDATE servico SET foto = 'https://images.unsplash.com/photo-1637949385162-e416fb15b2ce?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=1452' WHERE titulo LIKE '%Homus%';;
UPDATE servico SET foto = 'https://images.unsplash.com/photo-1603360946369-dc9bb6258143?w=500' WHERE titulo LIKE '%Kafta%';
UPDATE servico SET foto = 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=500' WHERE titulo LIKE '%Baklava%';
UPDATE servico SET foto = 'https://images.unsplash.com/photo-1599487488170-d11ec9c172f0?w=500' WHERE titulo LIKE '%Shawarma%';
UPDATE servico SET foto = 'https://images.unsplash.com/photo-1588137378633-dea1336ce1e2?w=500' WHERE titulo LIKE '%Falafel%';

-- ========================================
-- ÍNDICES PARA PERFORMANCE
-- ========================================

CREATE INDEX idx_pedido_usuario ON pedido(usuario_id);
CREATE INDEX idx_pedido_status ON pedido(status);
CREATE INDEX idx_pedido_data ON pedido(criado_em);
CREATE INDEX idx_servico_ativo ON servico(ativo);
CREATE INDEX idx_carrinho_usuario ON carrinho(usuario_id);

-- ========================================
-- FIM DO SCRIPT
-- ========================================