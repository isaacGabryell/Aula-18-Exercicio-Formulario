<?php
session_start();
require_once __DIR__ . '/../controller/ProdutoController.php';
require_once __DIR__ . '/../controller/AuthController.php';
require_once __DIR__ . '/../model/dao/ProdutoDAO.php';

$auth             = new AuthController();
$produtoController = new ProdutoController();
$produtos         = $produtoController->listar();
$termo            = $_GET['busca'] ?? '';
$categoria        = $_GET['categoria'] ?? 'todos';

if ($termo) {
    $produtoDAO = new ProdutoDAO();
    $produtos   = $produtoDAO->buscarPorNome($termo);
} elseif ($categoria && $categoria !== 'todos') {
    $produtoDAO = new ProdutoDAO();
    $produtos   = $produtoDAO->buscarPorNome($categoria);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌸 FLORI | Flores que Encantam</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/estilo.css">
</head>
<body>
    <header>
        <div class="container-header">
            <a href="index.php" class="logo">
                <i class="fas fa-seedling"></i>
                <span>FLORI</span>
            </a>

            <form class="pesquisa" method="GET" action="index.php">
                <i class="fas fa-search"></i>
                <input type="text" name="busca" placeholder="Buscar flores, buquês..." value="<?= htmlspecialchars($termo) ?>">
            </form>

            <nav style="display:flex;align-items:center;gap:0.5rem;">
                <a href="index.php" class="icon-link" style="font-size:0.9rem;padding:0.5rem 1rem;border-radius:50px;border:1px solid var(--cinza-200);"><i class="fas fa-home"></i> Início</a>
                <a href="anuncios.php" class="icon-link" style="font-size:0.9rem;padding:0.5rem 1rem;border-radius:50px;border:1px solid var(--rosa-200);color:var(--rosa-600);background:var(--rosa-50);"><i class="fas fa-bullhorn"></i> Anúncios</a>
            </nav>

            <div class="icons">
                <?php if ($auth->isLoggedIn()): ?>
                    <span class="user-info">
                        <i class="fas fa-user-circle"></i>
                        <?= htmlspecialchars($_SESSION['usuario_nome']) ?>
                    </span>
                    <?php if ($auth->isAdmin()): ?>
                        <a href="admin/dashboard.php" class="icon-link" title="Painel Admin"><i class="fas fa-crown"></i></a>
                    <?php endif; ?>
                    <?php if ($auth->isVendedor()): ?>
                        <a href="vendedor/dashboard.php" class="icon-link" title="Minha Loja"><i class="fas fa-store"></i></a>
                    <?php endif; ?>
                    <a href="carrinho.php" class="icon-link" title="Carrinho"><i class="fas fa-shopping-cart"></i></a>
                    <a href="../controller/AuthController.php?action=logout" class="icon-link" title="Sair"><i class="fas fa-sign-out-alt"></i></a>
                <?php else: ?>
                    <a href="login.php" class="icon-link" title="Entrar"><i class="fas fa-sign-in-alt"></i></a>
                    <a href="cadastro.php" class="icon-link" title="Cadastrar"><i class="fas fa-user-plus"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="container-flex">
        <aside class="sidebar-categorias">
            <div class="titulo-categorias">
                <i class="fas fa-th-large"></i> Categorias
            </div>
            <ul>
                <li class="<?= ($categoria === 'todos' && !$termo) ? 'ativo' : '' ?>">
                    <a href="index.php"><i class="fas fa-star"></i> Todos</a>
                </li>
                <li class="<?= $categoria === 'rosas' ? 'ativo' : '' ?>">
                    <a href="index.php?categoria=rosas"><i class="fas fa-spa"></i> Rosas 🌹</a>
                </li>
                <li class="<?= $categoria === 'buques' ? 'ativo' : '' ?>">
                    <a href="index.php?categoria=buques"><i class="fas fa-gift"></i> Buquês 💐</a>
                </li>
                <li class="<?= $categoria === 'promocoes' ? 'ativo' : '' ?>">
                    <a href="index.php?categoria=promocoes"><i class="fas fa-tags"></i> Promoções 🏷️</a>
                </li>
            </ul>
        </aside>

        <main class="conteudo-principal">
            <div class="banner">
                <span class="flor-decorativa" style="top:10%;left:5%;">🌸</span>
                <span class="flor-decorativa" style="top:20%;right:10%;">🌺</span>
                <span class="flor-decorativa" style="bottom:15%;left:15%;">🌷</span>
                <h1>🌸 Flores que Encantam</h1>
                <p>Transforme momentos em memórias inesquecíveis</p>
            </div>

            <h2 class="titulo">
                <i class="fas fa-star"></i>
                <?php if ($termo): ?>
                    Resultados para "<?= htmlspecialchars($termo) ?>"
                <?php elseif ($categoria && $categoria !== 'todos'): ?>
                    Categoria: <?= ucfirst($categoria) ?>
                <?php else: ?>
                    Produtos em Destaque
                <?php endif; ?>
            </h2>

            <?php if (empty($produtos)): ?>
                <div class="mensagem-vazio">
                    <i class="fas fa-search" style="font-size:4rem;"></i>
                    <p>Nenhum produto encontrado.</p>
                    <?php if ($termo || ($categoria && $categoria !== 'todos')): ?>
                        <a href="index.php" class="btn-comprar" style="display:inline-block;margin-top:1rem;width:auto;">
                            <i class="fas fa-arrow-left"></i> Ver todos os produtos
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
            <div class="produtos">
                <?php foreach ($produtos as $produto):
                    $imagemProduto = $produto->getImagem() ?? '';
                ?>
                <div class="produto-card">
                    <div class="produto-imagem">
                        <?php if ($imagemProduto): ?>
                            <img src="../assets/uploads/<?= htmlspecialchars($imagemProduto) ?>" alt="<?= htmlspecialchars($produto->getNome()) ?>">
                        <?php else: ?>
                            <span class="sem-imagem">🌸</span>
                        <?php endif; ?>
                    </div>
                    <div class="produto-info">
                        <h3><?= htmlspecialchars($produto->getNome()) ?></h3>
                        <p class="floricultura">
                            <i class="fas fa-store"></i>
                            <?= htmlspecialchars($produto->getNomeFloricultura()) ?>
                        </p>
                        <?php if ($produto->getDescricao()): ?>
                            <p class="descricao"><?= htmlspecialchars($produto->getDescricao()) ?></p>
                        <?php endif; ?>
                        <div class="preco">
                            <span class="preco-atual">R$ <?= number_format($produto->getPreco(), 2, ',', '.') ?></span>
                        </div>
                        <p class="estoque <?= $produto->getEstoque() > 10 ? 'estoque-em-alta' : ($produto->getEstoque() > 0 ? 'estoque-medio' : 'estoque-esgotado') ?>">
                            <i class="fas fa-box"></i>
                            <?php if ($produto->getEstoque() > 10): ?>
                                Em estoque: <?= $produto->getEstoque() ?> unid.
                            <?php elseif ($produto->getEstoque() > 0): ?>
                                Últimas unidades: <?= $produto->getEstoque() ?> unid.
                            <?php else: ?>
                                Fora de estoque
                            <?php endif; ?>
                        </p>
                        <?php if ($auth->isLoggedIn() && $auth->isCliente() && $produto->getEstoque() > 0): ?>
                        <form method="POST" action="carrinho.php" style="margin-top:auto;">
                            <input type="hidden" name="produto_id" value="<?= $produto->getIdProduto() ?>">
                            <input type="hidden" name="quantidade" value="1">
                            <button type="submit" class="btn-comprar">
                                <i class="fas fa-shopping-bag"></i> Adicionar ao Carrinho
                            </button>
                        </form>
                        <?php elseif ($auth->isLoggedIn() && $auth->isVendedor()): ?>
                            <span class="estoque" style="color:#f39c12;">
                                <i class="fas fa-info-circle"></i> Você é vendedor
                            </span>
                        <?php elseif ($auth->isLoggedIn() && $auth->isAdmin()): ?>
                            <span class="estoque" style="color:#6c5ce7;">
                                <i class="fas fa-crown"></i> Modo Administrador
                            </span>
                        <?php elseif (!$auth->isLoggedIn()): ?>
                            <a href="login.php" class="btn-login-link">
                                <i class="fas fa-sign-in-alt"></i> Faça login para comprar
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <footer>
        <div class="footer-content">
            <div class="footer-col">
                <h3><i class="fas fa-seedling"></i> FLORI</h3>
                <p>Levamos beleza e emoção através de flores selecionadas.</p>
            </div>
            <div class="footer-col">
                <h4>Links Rápidos</h4>
                <ul>
                    <li><a href="index.php">Início</a></li>
                    <li><a href="anuncios.php">Anúncios</a></li>
                    <li><a href="carrinho.php">Carrinho</a></li>
                    <li><a href="login.php">Entrar</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Contato</h4>
                <p><i class="fas fa-envelope"></i> contato@flori.com.br</p>
                <p><i class="fas fa-phone"></i> (11) 3456-7890</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© <?= date('Y') ?> FLORI - Todos os direitos reservados 🌸</p>
        </div>
    </footer>
</body>
</html>
