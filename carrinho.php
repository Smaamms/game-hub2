<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Processar ações do carrinho
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $item_id = (int)($_POST['item_id'] ?? 0);
    
    try {
        $conn = getConnection();
        
        if ($acao === 'remover' && $item_id) {
            $stmt = $conn->prepare("DELETE FROM carrinho WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$item_id, $_SESSION['usuario_id']]);
            $_SESSION['sucesso'] = 'Item removido do carrinho.';
        } elseif ($acao === 'atualizar' && $item_id) {
            $quantidade = (int)($_POST['quantidade'] ?? 1);
            if ($quantidade > 0) {
                $stmt = $conn->prepare("UPDATE carrinho SET quantidade = ? WHERE id = ? AND usuario_id = ?");
                $stmt->execute([$quantidade, $item_id, $_SESSION['usuario_id']]);
                $_SESSION['sucesso'] = 'Quantidade atualizada.';
            }
        }
    } catch(PDOException $e) {
        $_SESSION['erro'] = 'Erro ao processar ação.';
    }
    
    redirect('carrinho.php');
}

// Buscar itens do carrinho
try {
    $conn = getConnection();
    $stmt = $conn->prepare("
        SELECT c.*, j.titulo, j.preco, j.plataforma, j.estoque 
        FROM carrinho c 
        JOIN jogos j ON c.jogo_id = j.id 
        WHERE c.usuario_id = ? 
        ORDER BY c.data_adicao DESC
    ");
    $stmt->execute([$_SESSION['usuario_id']]);
    $itens = $stmt->fetchAll();
    
    // Calcular total
    $total = 0;
    foreach ($itens as $item) {
        $total += $item['preco'] * $item['quantidade'];
    }
} catch(PDOException $e) {
    $itens = [];
    $total = 0;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho de Compras - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1 style="text-align: center; color: #667eea; margin: 30px 0;">Meu Carrinho</h1>
        
        <?php if (isset($_SESSION['sucesso'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['sucesso']; unset($_SESSION['sucesso']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['erro'])): ?>
            <div class="alert alert-error"><?php echo $_SESSION['erro']; unset($_SESSION['erro']); ?></div>
        <?php endif; ?>
        
        <?php if (empty($itens)): ?>
            <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 10px;">
                <p style="font-size: 1.2rem; color: #666; margin-bottom: 20px;">Seu carrinho está vazio.</p>
                <a href="catalogo.php" class="btn btn-primary">Continuar Comprando</a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                <div class="cart-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Jogo</th>
                                <th>Plataforma</th>
                                <th>Preço</th>
                                <th>Quantidade</th>
                                <th>Subtotal</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itens as $item): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($item['titulo']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($item['plataforma']); ?></td>
                                    <td><?php echo formatPrice($item['preco']); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="acao" value="atualizar">
                                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                            <input type="number" name="quantidade" value="<?php echo $item['quantidade']; ?>" 
                                                   min="1" max="<?php echo $item['estoque']; ?>" 
                                                   style="width: 60px; padding: 5px;">
                                            <button type="submit" class="btn btn-secondary" style="padding: 5px 10px; font-size: 0.8rem;">Atualizar</button>
                                        </form>
                                    </td>
                                    <td><strong><?php echo formatPrice($item['preco'] * $item['quantidade']); ?></strong></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="acao" value="remover">
                                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem;">Remover</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="cart-summary">
                    <h3>Resumo do Pedido</h3>
                    <div class="summary-item">
                        <span>Subtotal:</span>
                        <span><?php echo formatPrice($total); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Frete:</span>
                        <span style="color: #28a745;">Grátis</span>
                    </div>
                    <div class="summary-item" style="border-top: 2px solid #667eea; padding-top: 15px;">
                        <span><strong>Total:</strong></span>
                        <span class="summary-total"><?php echo formatPrice($total); ?></span>
                    </div>
                    <a href="checkout.php" class="btn btn-success" style="width: 100%; margin-top: 20px;">Finalizar Compra</a>
                    <a href="catalogo.php" class="btn btn-secondary" style="width: 100%; margin-top: 10px;">Continuar Comprando</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
