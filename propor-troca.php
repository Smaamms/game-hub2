<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$jogo_desejado_id = isset($_GET['jogo_id']) ? (int)$_GET['jogo_id'] : 0;
$jogo_desejado = null;

// Buscar jogo desejado se especificado
if ($jogo_desejado_id) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT * FROM jogos WHERE id = ? AND ativo = 1");
        $stmt->execute([$jogo_desejado_id]);
        $jogo_desejado = $stmt->fetch();
    } catch(PDOException $e) {
        $jogo_desejado = null;
    }
}

// Buscar jogos disponíveis para troca
try {
    $conn = getConnection();
    $stmt = $conn->query("SELECT * FROM jogos WHERE ativo = 1 AND preco_troca IS NOT NULL ORDER BY titulo");
    $jogos_disponiveis = $stmt->fetchAll();
} catch(PDOException $e) {
    $jogos_disponiveis = [];
}

$erro = '';
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jogo_oferecido_id = (int)$_POST['jogo_oferecido_id'];
    $jogo_desejado_id = !empty($_POST['jogo_desejado_id']) ? (int)$_POST['jogo_desejado_id'] : null;
    $mensagem = sanitize($_POST['mensagem']);
    
    if (!$jogo_oferecido_id) {
        $erro = 'Por favor, selecione o jogo que você deseja oferecer.';
    } else {
        try {
            $conn = getConnection();
            
            // Inserir proposta de troca
            $stmt = $conn->prepare("INSERT INTO trocas (usuario_ofertante_id, jogo_oferecido_id, jogo_desejado_id, mensagem) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_SESSION['usuario_id'], $jogo_oferecido_id, $jogo_desejado_id, $mensagem]);
            
            $sucesso = true;
        } catch(PDOException $e) {
            $erro = 'Erro ao enviar proposta: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Propor Troca - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="form-container">
            <h2>Propor Troca de Jogo</h2>
            
            <?php if ($sucesso): ?>
                <div class="alert alert-success">
                    Proposta de troca enviada com sucesso! Você pode acompanhar o status na página de trocas.
                </div>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="trocas.php" class="btn btn-primary">Ver Minhas Trocas</a>
                    <a href="propor-troca.php" class="btn btn-secondary">Propor Outra Troca</a>
                </div>
            <?php else: ?>
                <?php if ($erro): ?>
                    <div class="alert alert-error"><?php echo $erro; ?></div>
                <?php endif; ?>
                
                <p style="color: #666; margin-bottom: 20px;">
                    Preencha o formulário abaixo para propor uma troca de jogos. Você pode oferecer um jogo que possui e especificar qual jogo deseja receber em troca.
                </p>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Jogo que você oferece *</label>
                        <select name="jogo_oferecido_id" required>
                            <option value="">Selecione um jogo</option>
                            <?php foreach ($jogos_disponiveis as $jogo): ?>
                                <option value="<?php echo $jogo['id']; ?>">
                                    <?php echo htmlspecialchars($jogo['titulo']); ?> - <?php echo htmlspecialchars($jogo['plataforma']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color: #666;">Selecione o jogo que você possui e deseja trocar</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Jogo que você deseja (opcional)</label>
                        <select name="jogo_desejado_id">
                            <option value="">Qualquer jogo / A combinar</option>
                            <?php foreach ($jogos_disponiveis as $jogo): ?>
                                <option value="<?php echo $jogo['id']; ?>" <?php echo ($jogo_desejado && $jogo['id'] == $jogo_desejado['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($jogo['titulo']); ?> - <?php echo htmlspecialchars($jogo['plataforma']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color: #666;">Deixe em branco se aceita qualquer jogo</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Mensagem (opcional)</label>
                        <textarea name="mensagem" rows="5" placeholder="Adicione detalhes sobre a condição do jogo, preferências de troca, etc."></textarea>
                    </div>
                    
                    <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                        <strong>⚠️ Importante:</strong>
                        <ul style="margin: 10px 0 0 20px;">
                            <li>Esta é uma proposta de troca entre usuários</li>
                            <li>Após aceita, vocês devem combinar os detalhes da troca</li>
                            <li>O Game Hub não se responsabiliza pela transação</li>
                            <li>Sempre verifique a condição do jogo antes de aceitar</li>
                        </ul>
                    </div>
                    
                    <button type="submit" class="btn btn-success">Enviar Proposta</button>
                    <a href="trocas.php" class="btn btn-secondary">Cancelar</a>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
