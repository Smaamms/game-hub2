<header>
    <div class="header-content">
        <a href="index.php" class="logo"> Game Hub</a>
        
        <nav>
            <ul>
                <li><a href="index.php">Início</a></li>
                <li><a href="catalogo.php">Catálogo</a></li>
                <li><a href="trocas.php">Trocas</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="minha-conta.php">Minha Conta</a></li>
                    <li><a href="carrinho.php"> Carrinho</a></li>
                    <?php if (isAdmin()): ?>
                        <li><a href="admin/index.php">Admin</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Sair</a></li>
                <?php else: ?>
                    <li><a href="login.php">Entrar</a></li>
                    <li><a href="registro.php">Cadastrar</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
