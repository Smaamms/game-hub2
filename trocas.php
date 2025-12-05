<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Buscar propostas de troca do usuário
try {
    $conn = getConnection();
    
    // Propostas enviadas
    $stmt = $conn->prepare("
        SELECT t.*, 
               j1.titulo as jogo_oferecido_titulo, 
               j2.titulo as jogo_desejado_titulo,
               u.nome as destinatario_nome
        FROM trocas t
        JOIN jogos j1 ON t.jogo_oferecido_id = j1.id
        LEFT JOIN jogos j2 ON t.jogo_desejado_id = j2.id
        LEFT JOIN usuarios u ON t.usuario_destinatario_id = u.id
        WHERE t.usuario_ofertante_id = ?
        ORDER BY t.data_proposta DESC
    ");
    $stmt->execute([$_SESSION['usuario_id']]);
    $propostas_enviadas = $stmt->fetchAll();
    
    // Propostas recebidas
    $stmt = $conn->prepare("
        SELECT t.*, 
               j1.titulo as jogo_oferecido_titulo, 
               j2.titulo as jogo_desejado_titulo,
               u.nome as ofertante_nome
        FROM trocas t
        JOIN jogos j1 ON t.jogo_oferecido_id = j1.id
        LEFT JOIN jogos j2 ON t.jogo_desejado_id = j2.id
        JOIN usuarios u ON t.usuario_ofertante_id = u.id
        WHERE t.usuario_destinatario_id = ?
        ORDER BY t.data_proposta DESC
    ");
    $stmt->execute([$_SESSION['usuario_id']]);
    $propostas_recebidas = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $propostas_enviadas = [];
    $propostas_recebidas = [];
}

// Processar resposta à proposta
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $troca_id = (int)($_POST['troca_id'] ?? 0);
    
    try {
        $conn = getConnection();
        
        if ($acao === 'aceitar') {
            $stmt = $conn->prepare("UPDATE trocas SET status = 'aceita', data_resposta = NOW() WHERE id = ? AND usuario_destinatario_id = ?");
            $stmt->execute([$troca_id, $_SESSION['usuario_id']]);
            $_SESSION['sucesso'] = 'Proposta aceita! Entre em contato com o usuário para combinar a troca.';
        } elseif ($acao === 'recusar') {
            $stmt = $conn->prepare("UPDATE trocas SET status = 'recusada', data_resposta = NOW() WHERE id = ? AND usuario_destinatario_id = ?");
            $stmt->execute([$troca_id, $_SESSION['usuario_id']]);
            $_SESSION['sucesso'] = 'Proposta recusada.';
        } elseif ($acao === 'cancelar') {
            $stmt = $conn->prepare("UPDATE trocas SET status = 'cancelada' WHERE id = ? AND usuario_ofertante_id = ?");
            $stmt->execute([$troca_id, $_SESSION['usuario_id']]);
            $_SESSION['sucesso'] = 'Proposta cancelada.';
        }
        
        redirect('trocas.php');
    } catch(PDOException $e) {
        $_SESSION['erro'] = 'Erro ao processar ação.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Trocas - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1 style="text-align: center; color: #667eea; margin: 30px 0;">Sistema de Trocas</h1>
        
        <?php if (isset($_SESSION['sucesso'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['sucesso']; unset($_SESSION['sucesso']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['erro'])): ?>
            <div class="alert alert-error"><?php echo $_SESSION['erro']; unset($_SESSION['erro']); ?></div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-bottom: 30px;">
            <a href="propor-troca.php" class="btn btn-success">🔄 Propor Nova Troca</a>
        </div>
        
        <!-- Propostas Recebidas -->
        <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 30px;">
            <h2 style="color: #667eea; margin-bottom: 20px;">Propostas Recebidas</h2>
            
            <?php if (empty($propostas_recebidas)): ?>
                <p style="color: #666; text-align: center;">Nenhuma proposta recebida.</p>
            <?php else: ?>
                <?php foreach ($propostas_recebidas as $troca): ?>
                    <div style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; margin-bottom: 15px;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                            <div>
                                <p><strong>De:</strong> <?php echo htmlspecialchars($troca['ofertante_nome']); ?></p>
                                <p><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($troca['data_proposta'])); ?></p>
                            </div>
                            <span style="background: <?php 
                                echo $troca['status'] === 'pendente' ? '#ffc107' : 
                                    ($troca['status'] === 'aceita' ? '#28a745' : 
                                    ($troca['status'] === 'recusada' ? '#dc3545' : '#6c757d')); 
                            ?>; color: white; padding: 5px 15px; border-radius: 5px;">
                                <?php echo ucfirst($troca['status']); ?>
                            </span>
                        </div>
                        
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                            <p><strong>Oferece:</strong> <?php echo htmlspecialchars($troca['jogo_oferecido_titulo']); ?></p>
                            <?php if ($troca['jogo_desejado_titulo']): ?>
                                <p><strong>Deseja:</strong> <?php echo htmlspecialchars($troca['jogo_desejado_titulo']); ?></p>
                            <?php endif; ?>
                            <?php if ($troca['mensagem']): ?>
                                <p style="margin-top: 10px;"><strong>Mensagem:</strong><br><?php echo nl2br(htmlspecialchars($troca['mensagem'])); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($troca['status'] === 'pendente'): ?>
                            <div style="display: flex; gap: 10px;">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="acao" value="aceitar">
                                    <input type="hidden" name="troca_id" value="<?php echo $troca['id']; ?>">
                                    <button type="submit" class="btn btn-success">Aceitar</button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="acao" value="recusar">
                                    <input type="hidden" name="troca_id" value="<?php echo $troca['id']; ?>">
                                    <button type="submit" class="btn btn-danger">Recusar</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Propostas Enviadas -->
        <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="color: #667eea; margin-bottom: 20px;">Propostas Enviadas</h2>
            
            <?php if (empty($propostas_enviadas)): ?>
                <p style="color: #666; text-align: center;">Nenhuma proposta enviada.</p>
            <?php else: ?>
                <?php foreach ($propostas_enviadas as $troca): ?>
                    <div style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; margin-bottom: 15px;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                            <div>
                                <?php if ($troca['destinatario_nome']): ?>
                                    <p><strong>Para:</strong> <?php echo htmlspecialchars($troca['destinatario_nome']); ?></p>
                                <?php endif; ?>
                                <p><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($troca['data_proposta'])); ?></p>
                            </div>
                            <span style="background: <?php 
                                echo $troca['status'] === 'pendente' ? '#ffc107' : 
                                    ($troca['status'] === 'aceita' ? '#28a745' : 
                                    ($troca['status'] === 'recusada' ? '#dc3545' : '#6c757d')); 
                            ?>; color: white; padding: 5px 15px; border-radius: 5px;">
                                <?php echo ucfirst($troca['status']); ?>
                            </span>
                        </div>
                        
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                            <p><strong>Você oferece:</strong> <?php echo htmlspecialchars($troca['jogo_oferecido_titulo']); ?></p>
                            <?php if ($troca['jogo_desejado_titulo']): ?>
                                <p><strong>Deseja:</strong> <?php echo htmlspecialchars($troca['jogo_desejado_titulo']); ?></p>
                            <?php endif; ?>
                            <?php if ($troca['mensagem']): ?>
                                <p style="margin-top: 10px;"><strong>Mensagem:</strong><br><?php echo nl2br(htmlspecialchars($troca['mensagem'])); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($troca['status'] === 'pendente'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="acao" value="cancelar">
                                <input type="hidden" name="troca_id" value="<?php echo $troca['id']; ?>">
                                <button type="submit" class="btn btn-secondary">Cancelar Proposta</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
