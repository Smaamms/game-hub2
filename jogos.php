<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$erro = '';
$sucesso = '';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    
    try {
        $conn = getConnection();
        
        if ($acao === 'adicionar') {
            $titulo = sanitize($_POST['titulo']);
            $descricao = sanitize($_POST['descricao']);
            $plataforma = $_POST['plataforma'];
            $categoria_id = (int)$_POST['categoria_id'];
            $preco = (float)$_POST['preco'];
            $preco_troca = !empty($_POST['preco_troca']) ? (float)$_POST['preco_troca'] : null;
            $condicao = $_POST['condicao'];
            $estoque = (int)$_POST['estoque'];
            $desenvolvedor = sanitize($_POST['desenvolvedor']);
            $ano_lancamento = !empty($_POST['ano_lancamento']) ? (int)$_POST['ano_lancamento'] : null;
            $classificacao = sanitize($_POST['classificacao']);
            $destaque = isset($_POST['destaque']) ? 1 : 0;
            
            $stmt = $conn->prepare("INSERT INTO jogos (titulo, descricao, plataforma, categoria_id, preco, preco_troca, condicao, estoque, desenvolvedor, ano_lancamento, classificacao, destaque) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$titulo, $descricao, $plataforma, $categoria_id, $preco, $preco_troca, $condicao, $estoque, $desenvolvedor, $ano_lancamento, $classificacao, $destaque]);
            
            $sucesso = 'Jogo adicionado com sucesso!';
            
        } elseif ($acao === 'editar') {
            $id = (int)$_POST['id'];
            $titulo = sanitize($_POST['titulo']);
            $descricao = sanitize($_POST['descricao']);
            $plataforma = $_POST['plataforma'];
            $categoria_id = (int)$_POST['categoria_id'];
            $preco = (float)$_POST['preco'];
            $preco_troca = !empty($_POST['preco_troca']) ? (float)$_POST['preco_troca'] : null;
            $condicao = $_POST['condicao'];
            $estoque = (int)$_POST['estoque'];
            $desenvolvedor = sanitize($_POST['desenvolvedor']);
            $ano_lancamento = !empty($_POST['ano_lancamento']) ? (int)$_POST['ano_lancamento'] : null;
            $classificacao = sanitize($_POST['classificacao']);
            $destaque = isset($_POST['destaque']) ? 1 : 0;
            
            $stmt = $conn->prepare("UPDATE jogos SET titulo = ?, descricao = ?, plataforma = ?, categoria_id = ?, preco = ?, preco_troca = ?, condicao = ?, estoque = ?, desenvolvedor = ?, ano_lancamento = ?, classificacao = ?, destaque = ? WHERE id = ?");
            $stmt->execute([$titulo, $descricao, $plataforma, $categoria_id, $preco, $preco_troca, $condicao, $estoque, $desenvolvedor, $ano_lancamento, $classificacao, $destaque, $id]);
            
            $sucesso = 'Jogo atualizado com sucesso!';
            
        } elseif ($acao === 'excluir') {
            $id = (int)$_POST['id'];
            $stmt = $conn->prepare("UPDATE jogos SET ativo = 0 WHERE id = ?");
            $stmt->execute([$id]);
            
            $sucesso = 'Jogo removido com sucesso!';
        }
    } catch(PDOException $e) {
        $erro = 'Erro ao processar ação: ' . $e->getMessage();
    }
}

// Buscar jogos
try {
    $conn = getConnection();
    $stmt = $conn->query("SELECT j.*, c.nome as categoria_nome FROM jogos j LEFT JOIN categorias c ON j.categoria_id = c.id WHERE j.ativo = 1 ORDER BY j.id DESC");
    $jogos = $stmt->fetchAll();
    
    // Buscar categorias
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
    <title>Gerenciar Jogos - <?php echo SITE_NAME; ?></title>
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
            <h1 style="color: #667eea; margin-bottom: 30px;">Gerenciar Jogos</h1>
            
            <?php if ($erro): ?>
                <div class="alert alert-error"><?php echo $erro; ?></div>
            <?php endif; ?>
            
            <?php if ($sucesso): ?>
                <div class="alert alert-success"><?php echo $sucesso; ?></div>
            <?php endif; ?>
            
            <button onclick="document.getElementById('formAdicionar').style.display='block'" class="btn btn-success" style="margin-bottom: 20px;">
                + Adicionar Novo Jogo
            </button>
            
            <!-- Formulário de Adicionar -->
            <div id="formAdicionar" style="display: none; background: #f8f9fa; padding: 30px; border-radius: 10px; margin-bottom: 30px;">
                <h2 style="color: #667eea; margin-bottom: 20px;">Adicionar Novo Jogo</h2>
                <form method="POST">
                    <input type="hidden" name="acao" value="adicionar">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Título *</label>
                            <input type="text" name="titulo" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Plataforma *</label>
                            <select name="plataforma" required>
                                <option value="Xbox One">Xbox One</option>
                                <option value="Xbox Series X/S">Xbox Series X/S</option>
                                <option value="PlayStation 4">PlayStation 4</option>
                                <option value="PlayStation 5">PlayStation 5</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Descrição</label>
                        <textarea name="descricao" rows="3"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Categoria *</label>
                            <select name="categoria_id" required>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nome']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Condição *</label>
                            <select name="condicao" required>
                                <option value="novo">Novo</option>
                                <option value="seminovo">Seminovo</option>
                                <option value="usado">Usado</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Preço (R$) *</label>
                            <input type="number" name="preco" step="0.01" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Preço para Troca (R$)</label>
                            <input type="number" name="preco_troca" step="0.01">
                        </div>
                        
                        <div class="form-group">
                            <label>Estoque *</label>
                            <input type="number" name="estoque" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Desenvolvedor</label>
                            <input type="text" name="desenvolvedor">
                        </div>
                        
                        <div class="form-group">
                            <label>Ano de Lançamento</label>
                            <input type="number" name="ano_lancamento" min="1980" max="2030">
                        </div>
                        
                        <div class="form-group">
                            <label>Classificação</label>
                            <input type="text" name="classificacao" placeholder="Ex: 18 anos">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="destaque">
                            Destacar na página inicial
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-success">Adicionar Jogo</button>
                    <button type="button" onclick="document.getElementById('formAdicionar').style.display='none'" class="btn btn-secondary">Cancelar</button>
                </form>
            </div>
            
            <!-- Lista de Jogos -->
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Plataforma</th>
                        <th>Categoria</th>
                        <th>Preço</th>
                        <th>Estoque</th>
                        <th>Destaque</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jogos as $jogo): ?>
                        <tr>
                            <td><?php echo $jogo['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($jogo['titulo']); ?></strong></td>
                            <td><?php echo htmlspecialchars($jogo['plataforma']); ?></td>
                            <td><?php echo htmlspecialchars($jogo['categoria_nome']); ?></td>
                            <td><?php echo formatPrice($jogo['preco']); ?></td>
                            <td><?php echo $jogo['estoque']; ?></td>
                            <td><?php echo $jogo['destaque'] ? '⭐' : '-'; ?></td>
                            <td>
                                <a href="editar-jogo.php?id=<?php echo $jogo['id']; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.85rem;">Editar</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir este jogo?');">
                                    <input type="hidden" name="acao" value="excluir">
                                    <input type="hidden" name="id" value="<?php echo $jogo['id']; ?>">
                                    <button type="submit" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.85rem;">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
