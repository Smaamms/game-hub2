<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Buscar pedidos do usuário
try {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY data_pedido DESC");
    $stmt->execute([$_SESSION['usuario_id']]);
    $pedidos = $stmt->fetchAll();
} catch(PDOException $e) {
    $pedidos = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1 style="text-align: center; color: #667eea; margin: 30px 0;">Minha Conta</h1>
        
        <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 30px;">
            <h2 style="color: #667eea; margin-bottom: 20px;">Informações da Conta</h2>
            <p><strong>Nome:</strong> <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></p>
            <p><strong>E-mail:</strong> <?php echo htmlspecialchars($_SESSION['usuario_email']); ?></p>
            <p><strong>Tipo de Conta:</strong> <?php echo $_SESSION['usuario_tipo'] === 'admin' ? 'Administrador' : 'Cliente'; ?></p>
        </div>
        
        <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="color: #667eea; margin-bottom: 20px;">Meus Pedidos</h2>
            
            <?php if (empty($pedidos)): ?>
                <p style="color: #666; text-align: center; padding: 40px 0;">Você ainda não fez nenhum pedido.</p>
                <div style="text-align: center;">
                    <a href="catalogo.php" class="btn btn-primary">Começar a Comprar</a>
                </div>
            <?php else: ?>
                <div class="admin-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Pedido</th>
                                <th>Data</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th>Pagamento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos as $pedido): ?>
                                <tr>
                                    <td><strong>#<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></td>
                                    <td><?php echo formatPrice($pedido['valor_total']); ?></td>
                                    <td>
                                        <?php
                                        $status_colors = [
                                            'pendente' => '#ffc107',
                                            'processando' => '#007bff',
                                            'enviado' => '#17a2b8',
                                            'entregue' => '#28a745',
                                            'cancelado' => '#dc3545'
                                        ];
                                        $color = $status_colors[$pedido['status']] ?? '#666';
                                        ?>
                                        <span style="background: <?php echo $color; ?>; color: white; padding: 5px 10px; border-radius: 5px; font-size: 0.85rem;">
                                            <?php echo ucfirst($pedido['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo str_replace('_', ' ', ucfirst($pedido['forma_pagamento'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
