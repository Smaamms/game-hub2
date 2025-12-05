-- Banco de Dados Game Hub
-- Sistema de Compra, Venda e Troca de Jogos

CREATE DATABASE IF NOT EXISTS gamehub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gamehub;

-- Tabela de Usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20),
    endereco TEXT,
    cidade VARCHAR(100),
    estado VARCHAR(2),
    cep VARCHAR(10),
    tipo ENUM('cliente', 'admin') DEFAULT 'cliente',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE,
    INDEX idx_email (email),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Categorias
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    descricao TEXT,
    icone VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Jogos
CREATE TABLE IF NOT EXISTS jogos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT,
    plataforma ENUM('Xbox One', 'Xbox Series X/S', 'PlayStation 4', 'PlayStation 5') NOT NULL,
    categoria_id INT,
    preco DECIMAL(10, 2) NOT NULL,
    preco_troca DECIMAL(10, 2),
    condicao ENUM('novo', 'seminovo', 'usado') DEFAULT 'seminovo',
    estoque INT DEFAULT 0,
    imagem VARCHAR(255),
    desenvolvedor VARCHAR(100),
    ano_lancamento YEAR,
    classificacao VARCHAR(10),
    destaque BOOLEAN DEFAULT FALSE,
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
    INDEX idx_plataforma (plataforma),
    INDEX idx_preco (preco),
    INDEX idx_destaque (destaque),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Pedidos
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    valor_total DECIMAL(10, 2) NOT NULL,
    status ENUM('pendente', 'processando', 'enviado', 'entregue', 'cancelado') DEFAULT 'pendente',
    forma_pagamento ENUM('cartao_credito', 'cartao_debito', 'pix', 'boleto') NOT NULL,
    endereco_entrega TEXT NOT NULL,
    observacoes TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_status (status),
    INDEX idx_data (data_pedido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Itens do Pedido
CREATE TABLE IF NOT EXISTS itens_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    jogo_id INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    preco_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (jogo_id) REFERENCES jogos(id) ON DELETE CASCADE,
    INDEX idx_pedido (pedido_id),
    INDEX idx_jogo (jogo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Propostas de Troca
CREATE TABLE IF NOT EXISTS trocas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_ofertante_id INT NOT NULL,
    usuario_destinatario_id INT,
    jogo_oferecido_id INT NOT NULL,
    jogo_desejado_id INT,
    mensagem TEXT,
    status ENUM('pendente', 'aceita', 'recusada', 'concluida', 'cancelada') DEFAULT 'pendente',
    data_proposta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_resposta TIMESTAMP NULL,
    FOREIGN KEY (usuario_ofertante_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_destinatario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (jogo_oferecido_id) REFERENCES jogos(id) ON DELETE CASCADE,
    FOREIGN KEY (jogo_desejado_id) REFERENCES jogos(id) ON DELETE CASCADE,
    INDEX idx_ofertante (usuario_ofertante_id),
    INDEX idx_destinatario (usuario_destinatario_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Carrinho de Compras
CREATE TABLE IF NOT EXISTS carrinho (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    jogo_id INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    data_adicao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (jogo_id) REFERENCES jogos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_carrinho (usuario_id, jogo_id),
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Avaliações
CREATE TABLE IF NOT EXISTS avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jogo_id INT NOT NULL,
    usuario_id INT NOT NULL,
    nota INT NOT NULL CHECK (nota >= 1 AND nota <= 5),
    comentario TEXT,
    data_avaliacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (jogo_id) REFERENCES jogos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_avaliacao (jogo_id, usuario_id),
    INDEX idx_jogo (jogo_id),
    INDEX idx_nota (nota)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir categorias padrão
INSERT INTO categorias (nome, descricao, icone) VALUES
('Ação', 'Jogos de ação e aventura', 'fa-bolt'),
('RPG', 'Jogos de interpretação de personagens', 'fa-dragon'),
('Esportes', 'Jogos de esportes e corrida', 'fa-futbol'),
('Tiro', 'Jogos de tiro em primeira e terceira pessoa', 'fa-crosshairs'),
('Estratégia', 'Jogos de estratégia e simulação', 'fa-chess'),
('Luta', 'Jogos de luta e combate', 'fa-hand-rock'),
('Corrida', 'Jogos de corrida e veículos', 'fa-car'),
('Aventura', 'Jogos de aventura e exploração', 'fa-map');

-- Inserir usuário administrador padrão (senha: admin123)
INSERT INTO usuarios (nome, email, senha, tipo) VALUES
('Administrador', 'admin@gamehub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Inserir jogos de exemplo
INSERT INTO jogos (titulo, descricao, plataforma, categoria_id, preco, preco_troca, condicao, estoque, imagem, desenvolvedor, ano_lancamento, classificacao, destaque) VALUES
('Halo Infinite', 'O Master Chief retorna na mais épica aventura de Halo até hoje.', 'Xbox Series X/S', 1, 249.90, 199.90, 'novo', 15, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT2nde7QkLscgCGw66D9u-uCF4fN6JfUveuUw&s', '343 Industries', 2021, '16 anos', TRUE),
('Forza Horizon 5', 'Explore os vibrantes e em constante evolução cenários do México.', 'Xbox Series X/S', 7, 299.90, 249.90, 'novo', 20, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTKuT_9Jt3RieSSq--WSvF5iXfNCozByf759Q&s', 'Playground Games', 2021, 'Livre', TRUE),
('God of War Ragnarök', 'Kratos e Atreus embarcam em uma jornada épica pela mitologia nórdica.', 'PlayStation 5', 1, 349.90, 299.90, 'novo', 25, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT0b_2yJIxtvl2RHGFwDYft6x7YpwFKdjo5Cg&s', 'Santa Monica Studio', 2022, '18 anos', TRUE),
('Spider-Man Miles Morales', 'Miles Morales descobre poderes explosivos que o diferenciam de seu mentor.', 'PlayStation 5', 1, 249.90, 199.90, 'seminovo', 18, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSBnPIBouAAUr-wtEDMHLqBHE8lEMioWRaNQA&s', 'Insomniac Games', 2020, '12 anos', TRUE),
('FIFA 23', 'O jogo de futebol mais realista do mundo.', 'PlayStation 4', 3, 199.90, 149.90, 'seminovo', 30, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRKdbfx0ibEI7_pmjoz_E1eqsMgczc4MkD-xQ&s', 'EA Sports', 2022, 'Livre', FALSE),
('Call of Duty Modern Warfare II', 'A Task Force 141 enfrenta a ameaça global definitiva.', 'Xbox One', 4, 299.90, 249.90, 'novo', 22, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT3qNUY7nSuHQal8MrdFc46rFR3qGgWKGWI7g&s', 'Infinity Ward', 2022, '18 anos', TRUE),
('The Last of Us Part II', 'Uma jornada implacável de vingança em um mundo pós-apocalíptico.', 'PlayStation 4', 1, 179.90, 139.90, 'usado', 12, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSBnPIBouAAUr-wtEDMHLqBHE8lEMioWRaNQA&s', 'Naughty Dog', 2020, '18 anos', FALSE),
('Elden Ring', 'Um novo RPG de fantasia desenvolvido pela FromSoftware e George R.R. Martin.', 'PlayStation 5', 2, 299.90, 249.90, 'novo', 28, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSVma7_X9NF1sfb9_tVuBwf1FyQQT7fKjddcA&s', 'FromSoftware', 2022, '16 anos', TRUE),
('Gran Turismo 7', 'O simulador de corrida definitivo retorna com gráficos impressionantes.', 'PlayStation 5', 7, 349.90, 299.90, 'novo', 16, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT2nde7QkLscgCGw66D9u-uCF4fN6JfUveuUw&s', 'Polyphony Digital', 2022, 'Livre', FALSE),
('Gears 5', 'Kait Diaz descobre suas conexões com o inimigo e a verdadeira ameaça à Sera.', 'Xbox One', 4, 149.90, 119.90, 'seminovo', 14, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT3qNUY7nSuHQal8MrdFc46rFR3qGgWKGWI7g&s', 'The Coalition', 2019, '18 anos', FALSE),
('Horizon Forbidden West', 'Junte-se a Aloy enquanto ela enfrenta o Oeste Proibido.', 'PlayStation 5', 1, 349.90, 299.90, 'novo', 20, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT0b_2yJIxtvl2RHGFwDYft6x7YpwFKdjo5Cg&s', 'Guerrilla Games', 2022, '14 anos', TRUE),
('Mortal Kombat 11', 'O jogo de luta definitivo com gráficos brutais e fatalities épicos.', 'Xbox One', 6, 179.90, 139.90, 'seminovo', 10, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRKdbfx0ibEI7_pmjoz_E1eqsMgczc4MkD-xQ&s', 'NetherRealm Studios', 2019, '18 anos', FALSE);