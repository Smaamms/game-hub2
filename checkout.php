<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Buscar dados do usuário
try {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch();
    
    // Buscar itens do carrinho
    $stmt = $conn->prepare("
        SELECT c.*, j.titulo, j.preco, j.estoque 
        FROM carrinho c 
        JOIN jogos j ON c.jogo_id = j.id 
        WHERE c.usuario_id = ?
    ");
    $stmt->execute([$_SESSION['usuario_id']]);
    $itens = $stmt->fetchAll();
    
    if (empty($itens)) {
        redirect('carrinho.php');
    }
    
    // Calcular total
    $total = 0;
    foreach ($itens as $item) {
        $total += $item['preco'] * $item['quantidade'];
    }
} catch(PDOException $e) {
    redirect('carrinho.php');
}

$erro = '';
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $forma_pagamento = $_POST['forma_pagamento'] ?? '';
    $endereco = sanitize($_POST['endereco']);
    $cidade = sanitize($_POST['cidade']);
    $estado = sanitize($_POST['estado']);
    $cep = sanitize($_POST['cep']);
    
    if (empty($forma_pagamento) || empty($endereco) || empty($cidade) || empty($estado) || empty($cep)) {
        $erro = 'Por favor, preencha todos os campos.';
    } else {
        try {
            $conn->beginTransaction();
            
            // Criar pedido
            $endereco_completo = "$endereco, $cidade - $estado, CEP: $cep";
            $stmt = $conn->prepare("INSERT INTO pedidos (usuario_id, valor_total, forma_pagamento, endereco_entrega) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_SESSION['usuario_id'], $total, $forma_pagamento, $endereco_completo]);
            $pedido_id = $conn->lastInsertId();
            
            // Adicionar itens do pedido
            foreach ($itens as $item) {
                $subtotal = $item['preco'] * $item['quantidade'];
                $stmt = $conn->prepare("INSERT INTO itens_pedido (pedido_id, jogo_id, quantidade, preco_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$pedido_id, $item['jogo_id'], $item['quantidade'], $item['preco'], $subtotal]);
                
                // Atualizar estoque
                $stmt = $conn->prepare("UPDATE jogos SET estoque = estoque - ? WHERE id = ?");
                $stmt->execute([$item['quantidade'], $item['jogo_id']]);
            }
            
            // Limpar carrinho
            $stmt = $conn->prepare("DELETE FROM carrinho WHERE usuario_id = ?");
            $stmt->execute([$_SESSION['usuario_id']]);
            
            $conn->commit();
            $sucesso = true;
            
        } catch(PDOException $e) {
            $conn->rollBack();
            $erro = 'Erro ao processar pedido: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1 style="text-align: center; color: #667eea; margin: 30px 0;">Finalizar Compra</h1>
        
        <?php if ($sucesso): ?>
            <div style="max-width: 600px; margin: 40px auto; background: white; padding: 40px; border-radius: 10px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <div style="font-size: 4rem; color: #28a745; margin-bottom: 20px;">✓</div>
                <h2 style="color: #28a745; margin-bottom: 20px;">Pedido Realizado com Sucesso!</h2>
                <p style="font-size: 1.1rem; color: #666; margin-bottom: 30px;">
                    Seu pedido foi processado e em breve você receberá um e-mail de confirmação.
                </p>
                <div style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 30px;">
                    <p><strong>Número do Pedido:</strong> #<?php echo str_pad($pedido_id, 6, '0', STR_PAD_LEFT); ?></p>
                    <p><strong>Valor Total:</strong> <?php echo formatPrice($total); ?></p>
                </div>
                <a href="minha-conta.php" class="btn btn-primary">Ver Meus Pedidos</a>
                <a href="catalogo.php" class="btn btn-secondary" style="margin-left: 10px;">Continuar Comprando</a>
            </div>
        <?php else: ?>
            <?php if ($erro): ?>
                <div class="alert alert-error"><?php echo $erro; ?></div>
            <?php endif; ?>
            
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <h2 style="color: #667eea; margin-bottom: 20px;">Dados de Entrega</h2>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label>Endereço *</label>
                            <input type="text" name="endereco" required value="<?php echo htmlspecialchars($usuario['endereco'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Cidade *</label>
                                <input type="text" name="cidade" required value="<?php echo htmlspecialchars($usuario['cidade'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Estado *</label>
                                <select name="estado" required>
                                    <option value="">Selecione</option>
                                    <?php
                                    $estados = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                                    foreach ($estados as $uf) {
                                        $selected = ($usuario['estado'] ?? '') === $uf ? 'selected' : '';
                                        echo "<option value='$uf' $selected>$uf</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>CEP *</label>
                                <input type="text" name="cep" required placeholder="00000-000" value="<?php echo htmlspecialchars($usuario['cep'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <h2 style="color: #667eea; margin: 30px 0 20px;">Forma de Pagamento</h2>
                        
                        <div class="form-group">
                            <label>
                                <input type="radio" name="forma_pagamento" value="cartao_credito" required>
                                Cartão de Crédito
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="radio" name="forma_pagamento" value="cartao_debito" required>
                                Cartão de Débito
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="radio" name="forma_pagamento" value="pix" required>
                                PIX
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="radio" name="forma_pagamento" value="boleto" required>
                                Boleto Bancário
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-success" style="width: 100%; margin-top: 20px; font-size: 1.1rem;">
                            Confirmar Pedido
                        </button>
                    </form>
                </div>
                
                <div>
                    <div class="cart-summary">
                        <h3>Resumo do Pedido</h3>
                        
                        <?php foreach ($itens as $item): ?>
                            <div style="padding: 10px 0; border-bottom: 1px solid #ddd;">
                                <p><strong><?php echo htmlspecialchars($item['titulo']); ?></strong></p>
                                <p style="color: #666; font-size: 0.9rem;">
                                    <?php echo $item['quantidade']; ?>x <?php echo formatPrice($item['preco']); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="summary-item" style="margin-top: 20px;">
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
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
