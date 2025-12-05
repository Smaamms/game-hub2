<?php
require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    redirect('catalogo.php');
}

try {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT j.*, c.nome as categoria_nome FROM jogos j LEFT JOIN categorias c ON j.categoria_id = c.id WHERE j.id = ? AND j.ativo = 1");
    $stmt->execute([$id]);
    $jogo = $stmt->fetch();
    
    if (!$jogo) {
        redirect('catalogo.php');
    }
    
    // Buscar avaliações
    $stmt_aval = $conn->prepare("SELECT a.*, u.nome as usuario_nome FROM avaliacoes a JOIN usuarios u ON a.usuario_id = u.id WHERE a.jogo_id = ? ORDER BY a.data_avaliacao DESC");
    $stmt_aval->execute([$id]);
    $avaliacoes = $stmt_aval->fetchAll();
    
    // Calcular média de avaliações
    if (count($avaliacoes) > 0) {
        $soma_notas = array_sum(array_column($avaliacoes, 'nota'));
        $media_avaliacoes = $soma_notas / count($avaliacoes);
    } else {
        $media_avaliacoes = 0;
    }
} catch(PDOException $e) {
    redirect('catalogo.php');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($jogo['titulo']); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="game-details">
            <div class="game-details-content">
                <div class="game-details-image">
                    <?php echo htmlspecialchars($jogo['titulo']); ?>
                </div>
                
                <div class="game-details-info">
                    <h1><?php echo htmlspecialchars($jogo['titulo']); ?></h1>
                    
                    <?php if ($media_avaliacoes > 0): ?>
                        <p style="color: #ffc107; font-size: 1.2rem; margin-bottom: 15px;">
                             <?php echo number_format($media_avaliacoes, 1); ?>/5 (<?php echo count($avaliacoes); ?> avaliações)
                        </p>
                    <?php endif; ?>
                    
                    <div class="game-meta">
                        <div class="meta-item">
                            <strong>Plataforma:</strong><br>
                            <?php echo htmlspecialchars($jogo['plataforma']); ?>
                        </div>
                        <div class="meta-item">
                            <strong>Categoria:</strong><br>
                            <?php echo htmlspecialchars($jogo['categoria_nome']); ?>
                        </div>
                        <div class="meta-item">
                            <strong>Condição:</strong><br>
                            <?php echo ucfirst($jogo['condicao']); ?>
                        </div>
                        <div class="meta-item">
                            <strong>Estoque:</strong><br>
                            <?php echo $jogo['estoque'] > 0 ? $jogo['estoque'] . ' unidades' : 'Fora de estoque'; ?>
                        </div>
                        <?php if ($jogo['desenvolvedor']): ?>
                        <div class="meta-item">
                            <strong>Desenvolvedor:</strong><br>
                            <?php echo htmlspecialchars($jogo['desenvolvedor']); ?>
                        </div>
                        <?php endif; ?>
                        <?php if ($jogo['ano_lancamento']): ?>
                        <div class="meta-item">
                            <strong>Ano de Lançamento:</strong><br>
                            <?php echo $jogo['ano_lancamento']; ?>
                        </div>
                        <?php endif; ?>
                        <?php if ($jogo['classificacao']): ?>
                        <div class="meta-item">
                            <strong>Classificação:</strong><br>
                            <?php echo htmlspecialchars($jogo['classificacao']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($jogo['descricao']): ?>
                        <h3 style="margin-top: 20px; color: #667eea;">Descrição</h3>
                        <p><?php echo nl2br(htmlspecialchars($jogo['descricao'])); ?></p>
                    <?php endif; ?>
                    
                    <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                        <h3 style="color: #667eea; margin-bottom: 15px;">Preços</h3>
                        <p style="font-size: 2rem; font-weight: bold; color: #28a745; margin-bottom: 10px;">
                            <?php echo formatPrice($jogo['preco']); ?>
                        </p>
                        <?php if ($jogo['preco_troca']): ?>
                            <p style="font-size: 1.2rem; color: #666;">
                                Valor para troca: <?php echo formatPrice($jogo['preco_troca']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($jogo['estoque'] > 0): ?>
                            <div style="margin-top: 20px; display: flex; gap: 10px;">
                                <?php if (isLoggedIn()): ?>
                                    <a href="adicionar-carrinho.php?id=<?php echo $jogo['id']; ?>" class="btn btn-success">🛒 Adicionar ao Carrinho</a>
                                    <?php if ($jogo['preco_troca']): ?>
                                        <a href="propor-troca.php?jogo_id=<?php echo $jogo['id']; ?>" class="btn btn-warning">🔄 Propor Troca</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-primary">Faça login para comprar</a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <p style="color: #dc3545; font-weight: bold; margin-top: 15px;">Este jogo está temporariamente fora de estoque.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Avaliações -->
        <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-top: 30px;">
            <h2 style="color: #667eea; margin-bottom: 20px;">Avaliações dos Clientes</h2>
            
            <?php if (empty($avaliacoes)): ?>
                <p style="color: #666;">Nenhuma avaliação ainda. Seja o primeiro a avaliar!</p>
            <?php else: ?>
                <?php foreach ($avaliacoes as $avaliacao): ?>
                    <div style="border-bottom: 1px solid #ddd; padding: 20px 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <strong><?php echo htmlspecialchars($avaliacao['usuario_nome']); ?></strong>
                            <span style="color: #ffc107;">
                                <?php echo str_repeat('⭐', $avaliacao['nota']); ?>
                            </span>
                        </div>
                        <?php if ($avaliacao['comentario']): ?>
                            <p style="color: #666;"><?php echo nl2br(htmlspecialchars($avaliacao['comentario'])); ?></p>
                        <?php endif; ?>
                        <small style="color: #999;">
                            <?php echo date('d/m/Y H:i', strtotime($avaliacao['data_avaliacao'])); ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
