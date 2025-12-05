<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Estatísticas
try {
    $conn = getConnection();
    
    $total_jogos = $conn->query("SELECT COUNT(*) FROM jogos WHERE ativo = 1")->fetchColumn();
    $total_usuarios = $conn->query("SELECT COUNT(*) FROM usuarios WHERE tipo = 'cliente'")->fetchColumn();
    $total_pedidos = $conn->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();
    $total_vendas = $conn->query("SELECT SUM(valor_total) FROM pedidos WHERE status != 'cancelado'")->fetchColumn();
    
    // Pedidos recentes
    $stmt = $conn->query("SELECT p.*, u.nome as usuario_nome FROM pedidos p JOIN usuarios u ON p.usuario_id = u.id ORDER BY p.data_pedido DESC LIMIT 10");
    $pedidos_recentes = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $total_jogos = $total_usuarios = $total_pedidos = 0;
    $total_vendas = 0;
    $pedidos_recentes = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="admin-header">
        <div class="header-content">
            <a href="index.php" class="logo">🎮 Game Hub - Admin</a>
            <div class="user-menu">
                <span style="color: white;">Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
                <a href="../index.php" style="color: white;">Ver Site</a>
                <a href="../logout.php" style="color: white;">Sair</a>
            </div>
        </div>
    </div>
    
    <nav class="admin-nav">
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="jogos.php">Gerenciar Jogos</a></li>
            <li><a href="pedidos.php">Pedidos</a></li>
            <li><a href="usuarios.php">Usuários</a></li>
            <li><a href="trocas.php">Trocas</a></li>
        </ul>
    </nav>
    
    <div class="container">
        <div class="admin-content">
            <h1 style="color: #667eea; margin-bottom: 30px;">Dashboard</h1>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px;">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 10px;">
                    <h3 style="margin-bottom: 10px; font-size: 1rem;">Total de Jogos</h3>
                    <p style="font-size: 2.5rem; font-weight: bold;"><?php echo $total_jogos; ?></p>
                </div>
                
                <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 30px; border-radius: 10px;">
                    <h3 style="margin-bottom: 10px; font-size: 1rem;">Total de Usuários</h3>
                    <p style="font-size: 2.5rem; font-weight: bold;"><?php echo $total_usuarios; ?></p>
                </div>
                
                <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 30px; border-radius: 10px;">
                    <h3 style="margin-bottom: 10px; font-size: 1rem;">Total de Pedidos</h3>
                    <p style="font-size: 2.5rem; font-weight: bold;"><?php echo $total_pedidos; ?></p>
                </div>
                
                <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 30px; border-radius: 10px;">
                    <h3 style="margin-bottom: 10px; font-size: 1rem;">Total de Vendas</h3>
                    <p style="font-size: 2.5rem; font-weight: bold;"><?php echo formatPrice($total_vendas); ?></p>
                </div>
            </div>
            
            <h2 style="color: #667eea; margin-bottom: 20px;">Pedidos Recentes</h2>
            
            <?php if (empty($pedidos_recentes)): ?>
                <p style="color: #666;">Nenhum pedido registrado ainda.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Cliente</th>
                            <th>Data</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos_recentes as $pedido): ?>
                            <tr>
                                <td><strong>#<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo htmlspecialchars($pedido['usuario_nome']); ?></td>
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
                                <td>
                                    <a href="pedido-detalhes.php?id=<?php echo $pedido['id']; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.85rem;">Ver Detalhes</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
