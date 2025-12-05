<?php
require_once 'config.php';

// Filtros
$plataforma = isset($_GET['plataforma']) ? $_GET['plataforma'] : '';
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$condicao = isset($_GET['condicao']) ? $_GET['condicao'] : '';
$busca = isset($_GET['busca']) ? sanitize($_GET['busca']) : '';

// Construir query
$sql = "SELECT j.*, c.nome as categoria_nome FROM jogos j LEFT JOIN categorias c ON j.categoria_id = c.id WHERE j.ativo = 1";
$params = [];

if ($plataforma) {
    $sql .= " AND j.plataforma = ?";
    $params[] = $plataforma;
}

if ($categoria) {
    $sql .= " AND j.categoria_id = ?";
    $params[] = $categoria;
}

if ($condicao) {
    $sql .= " AND j.condicao = ?";
    $params[] = $condicao;
}

if ($busca) {
    $sql .= " AND (j.titulo LIKE ? OR j.descricao LIKE ?)";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}

$sql .= " ORDER BY j.destaque DESC, j.data_cadastro DESC";

try {
    $conn = getConnection();
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $jogos = $stmt->fetchAll();
    
    // Buscar categorias para o filtro
    $stmt_cat = $conn->query("SELECT * FROM categorias ORDER BY nome");
    $categorias = $stmt_cat->fetchAll();
} catch(PDOException $e) {
    $jogos = [];
    $categorias = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1 style="text-align: center; color: #667eea; margin: 30px 0;">Catálogo de Jogos</h1>
        
        <div class="filters">
            <h3>Filtrar Jogos</h3>
            <form method="GET" action="">
                <div class="filter-group">
                    <input type="text" name="busca" placeholder="Buscar por título..." value="<?php echo htmlspecialchars($busca); ?>">
                    
                    <select name="plataforma">
                        <option value="">Todas as Plataformas</option>
                        <option value="Xbox One" <?php echo $plataforma === 'Xbox One' ? 'selected' : ''; ?>>Xbox One</option>
                        <option value="Xbox Series X/S" <?php echo $plataforma === 'Xbox Series X/S' ? 'selected' : ''; ?>>Xbox Series X/S</option>
                        <option value="PlayStation 4" <?php echo $plataforma === 'PlayStation 4' ? 'selected' : ''; ?>>PlayStation 4</option>
                        <option value="PlayStation 5" <?php echo $plataforma === 'PlayStation 5' ? 'selected' : ''; ?>>PlayStation 5</option>
                    </select>
                    
                    <select name="categoria">
                        <option value="">Todas as Categorias</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $categoria == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select name="condicao">
                        <option value="">Todas as Condições</option>
                        <option value="novo" <?php echo $condicao === 'novo' ? 'selected' : ''; ?>>Novo</option>
                        <option value="seminovo" <?php echo $condicao === 'seminovo' ? 'selected' : ''; ?>>Seminovo</option>
                        <option value="usado" <?php echo $condicao === 'usado' ? 'selected' : ''; ?>>Usado</option>
                    </select>
                    
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="catalogo.php" class="btn btn-secondary">Limpar</a>
                </div>
            </form>
        </div>
        
        <p style="margin-bottom: 20px; color: #666;">
            Encontrados <?php echo count($jogos); ?> jogos
        </p>
        
        <?php if (empty($jogos)): ?>
            <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 10px;">
                <p style="font-size: 1.2rem; color: #666;">Nenhum jogo encontrado com os filtros selecionados.</p>
                <a href="catalogo.php" class="btn btn-primary" style="margin-top: 20px;">Ver todos os jogos</a>
            </div>
        <?php else: ?>
            <div class="games-grid">
                <?php foreach ($jogos as $jogo): ?>
                    <div class="game-card">
                        <div class="game-image">
                            <?php echo htmlspecialchars($jogo['titulo']); ?>
                        </div>
                        <div class="game-info">
                            <h3 class="game-title"><?php echo htmlspecialchars($jogo['titulo']); ?></h3>
                            <span class="game-platform"><?php echo htmlspecialchars($jogo['plataforma']); ?></span>
                            <span class="game-condition"><?php echo ucfirst($jogo['condicao']); ?></span>
                            <?php if ($jogo['categoria_nome']): ?>
                                <p style="color: #666; font-size: 0.9rem; margin-top: 5px;">
                                    📁 <?php echo htmlspecialchars($jogo['categoria_nome']); ?>
                                </p>
                            <?php endif; ?>
                            <p class="game-price"><?php echo formatPrice($jogo['preco']); ?></p>
                            <?php if ($jogo['preco_troca']): ?>
                                <p style="color: #666; font-size: 0.9rem;">Troca: <?php echo formatPrice($jogo['preco_troca']); ?></p>
                            <?php endif; ?>
                            <?php if ($jogo['estoque'] > 0): ?>
                                <p style="color: #28a745; font-size: 0.85rem;">✓ Em estoque (<?php echo $jogo['estoque']; ?> unidades)</p>
                            <?php else: ?>
                                <p style="color: #dc3545; font-size: 0.85rem;">✗ Fora de estoque</p>
                            <?php endif; ?>
                            <div class="game-actions">
                                <a href="jogo.php?id=<?php echo $jogo['id']; ?>" class="btn btn-primary">Ver Detalhes</a>
                                <?php if (isLoggedIn() && $jogo['estoque'] > 0): ?>
                                    <a href="adicionar-carrinho.php?id=<?php echo $jogo['id']; ?>" class="btn btn-success">Comprar</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
