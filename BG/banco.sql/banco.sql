-- =========================
-- CRIAÇÃO DO BANCO
-- =========================
CREATE DATABASE IF NOT EXISTS floricultura_db;
USE floricultura_db;

-- =========================
-- TABELA PERFIL (papéis)
-- =========================
CREATE TABLE IF NOT EXISTS perfil (
    id_perfil INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE
);

-- =========================
-- TABELA USUARIO (dados pessoais)
-- =========================
CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    telefone VARCHAR(20),
    id_perfil INT NOT NULL,
    
    FOREIGN KEY (id_perfil) REFERENCES perfil(id_perfil)
);

-- =========================
-- TABELA LOGIN (autenticação)
-- =========================
CREATE TABLE IF NOT EXISTS login (
    id_login INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    id_usuario INT NOT NULL UNIQUE,
    
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- =========================
-- TABELA FLORICULTURA
-- =========================
CREATE TABLE IF NOT EXISTS floricultura (
    id_floricultura INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    endereco VARCHAR(200),
    cidade VARCHAR(100),
    telefone VARCHAR(20),
    id_usuario INT NOT NULL,
    
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- =========================
-- TABELA PRODUTO
-- =========================
CREATE TABLE IF NOT EXISTS produto (
    id_produto INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    estoque INT DEFAULT 0,
    id_floricultura INT NOT NULL,
    
    FOREIGN KEY (id_floricultura) REFERENCES floricultura(id_floricultura) ON DELETE CASCADE
);

-- =========================
-- TABELA PEDIDO
-- =========================
CREATE TABLE IF NOT EXISTS pedido (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    data_pedido DATETIME DEFAULT CURRENT_TIMESTAMP,
    valor_total DECIMAL(10,2),
    status ENUM('pendente', 'confirmado', 'entregue', 'cancelado') DEFAULT 'pendente',
    id_usuario INT NOT NULL,
    
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- =========================
-- TABELA ITEM_PEDIDO
-- =========================
CREATE TABLE IF NOT EXISTS item_pedido (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    quantidade INT NOT NULL,
    preco_venda DECIMAL(10,2) NOT NULL,
    id_pedido INT NOT NULL,
    id_produto INT NOT NULL,
    
    FOREIGN KEY (id_pedido) REFERENCES pedido(id_pedido) ON DELETE CASCADE,
    FOREIGN KEY (id_produto) REFERENCES produto(id_produto)
);

-- =========================
-- ÍNDICES (performance)
-- =========================
CREATE INDEX idx_usuario_perfil ON usuario(id_perfil);
CREATE INDEX idx_login_usuario ON login(id_usuario);
CREATE INDEX idx_floricultura_usuario ON floricultura(id_usuario);
CREATE INDEX idx_produto_floricultura ON produto(id_floricultura);
CREATE INDEX idx_pedido_usuario ON pedido(id_usuario);
CREATE INDEX idx_item_pedido ON item_pedido(id_pedido);
CREATE INDEX idx_item_produto ON item_pedido(id_produto);

-- =========================
-- DADOS INICIAIS (PERFIS)
-- =========================
INSERT INTO perfil (nome) VALUES 
('cliente'),
('vendedor'),
('admin');

-- =========================
-- ADMIN PADRÃO
-- Senha: admin123
-- =========================
INSERT INTO usuario (nome, telefone, id_perfil) VALUES 
('Administrador', '(11) 99999-0000', 3);

INSERT INTO login (email, senha, id_usuario) VALUES 
('admin@flori.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- =========================
-- VENDEDOR PADRÃO
-- Senha: vendedor123
-- =========================
INSERT INTO usuario (nome, telefone, id_perfil) VALUES 
('Vendedor Demo', '(11) 98888-7777', 2);

INSERT INTO login (email, senha, id_usuario) VALUES 
('vendedor@flori.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2);

INSERT INTO floricultura (nome, endereco, cidade, telefone, id_usuario) VALUES 
('Floricultura Demo', 'Rua das Flores, 123', 'São Paulo', '(11) 3456-7890', 2);