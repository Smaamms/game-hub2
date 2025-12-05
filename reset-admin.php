<?php
// Script para redefinir a senha do administrador para 'admin123'
// Uso CLI: php scripts\reset-admin.php
// Uso web local (somente para desenvolvimento): acessar scripts/reset-admin.php?confirm=1

require_once __DIR__ . '/../config.php';

if (PHP_SAPI !== 'cli') {
    if (!isset($_GET['confirm']) || $_GET['confirm'] != '1') {
        echo "Por segurança, execute este script via CLI ou adicione ?confirm=1 na URL local.";
        exit();
    }
}

$newPassword = 'admin123';
$hash = password_hash($newPassword, PASSWORD_DEFAULT);

try {
    $db = getConnection();
    $stmt = $db->prepare("UPDATE usuarios SET senha = :hash, ativo = 1 WHERE email = :email");
    $stmt->execute([':hash' => $hash, ':email' => 'admin@gamehub.com']);
    $count = $stmt->rowCount();

    if ($count > 0) {
        echo "Senha do admin atualizada para 'admin123'. Linhas afetadas: {$count}\n";
    } else {
        echo "Nenhum usuário encontrado com email 'admin@gamehub.com'.\n";
    }
} catch (PDOException $e) {
    echo "Erro ao atualizar senha: " . $e->getMessage() . "\n";
}

// Dica: após rodar, remova ou proteja este arquivo para não expor a redefinição de senha em produção.

?>
