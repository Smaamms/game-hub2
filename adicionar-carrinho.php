<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$jogo_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$jogo_id) {
    redirect('catalogo.php');
}

try {
    $conn = getConnection();
    
    // Verificar se o jogo existe e está disponível
    $stmt = $conn->prepare("SELECT id, titulo, estoque FROM jogos WHERE id = ? AND ativo = 1");
    $stmt->execute([$jogo_id]);
    $jogo = $stmt->fetch();
    
    if (!$jogo || $jogo['estoque'] <= 0) {
        $_SESSION['erro'] = 'Jogo não disponível.';
        redirect('catalogo.php');
    }
    
    // Verificar se já está no carrinho
    $stmt = $conn->prepare("SELECT id, quantidade FROM carrinho WHERE usuario_id = ? AND jogo_id = ?");
    $stmt->execute([$_SESSION['usuario_id'], $jogo_id]);
    $item_carrinho = $stmt->fetch();
    
    if ($item_carrinho) {
        // Atualizar quantidade
        $nova_quantidade = $item_carrinho['quantidade'] + 1;
        if ($nova_quantidade <= $jogo['estoque']) {
            $stmt = $conn->prepare("UPDATE carrinho SET quantidade = ? WHERE id = ?");
            $stmt->execute([$nova_quantidade, $item_carrinho['id']]);
            $_SESSION['sucesso'] = 'Quantidade atualizada no carrinho!';
        } else {
            $_SESSION['erro'] = 'Estoque insuficiente.';
        }
    } else {
        // Adicionar novo item
        $stmt = $conn->prepare("INSERT INTO carrinho (usuario_id, jogo_id, quantidade) VALUES (?, ?, 1)");
        $stmt->execute([$_SESSION['usuario_id'], $jogo_id]);
        $_SESSION['sucesso'] = 'Jogo adicionado ao carrinho!';
    }
    
} catch(PDOException $e) {
    $_SESSION['erro'] = 'Erro ao adicionar ao carrinho.';
}

redirect('carrinho.php');
?>
