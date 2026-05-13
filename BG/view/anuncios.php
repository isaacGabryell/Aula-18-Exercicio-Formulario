<?php
session_start();
require_once __DIR__ . '/../controller/AuthController.php';
require_once __DIR__ . '/../config/Database.php';

$auth = new AuthController();
$conn = Database::getInstance()->getConnection();

$stmt = $conn->query("SELECT a.*, f.nome as nome_loja, f.cidade, f.telefone
                       FROM anuncio a
                       JOIN floricultura f ON a.id_floricultura = f.id_floricultura
                       WHERE a.ativo = 1
                       AND (a.data_expiracao IS NULL OR a.data_expiracao >= CURDATE())
                       ORDER BY a.destaque DESC, a.data_criacao DESC");
$anuncios  = $stmt->fetchAll();
$destaques = array_filter($anuncios, fn($a) => $a['destaque']);
$normais   = array_filter($anuncios, fn($a) => !$a['destaque']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌸 FLORI | Anúncios</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/estilo.css">
    <style>
        .anuncios-hero { background: linear-gradient(135deg, var(--rosa-400), var(--rosa-700)); padding: 3rem 2rem; text-align: center; color: white; margin-bottom: 2.5rem; }
        .anuncios-hero h1 { font-family: 'Playfair Display', serif; font-size: 2.8rem; margin-bottom: 0.5rem; }
        .anuncios-hero p { font-size: 1.1rem; opacity: 0.9; }
        .anuncios-wrapper { max-width: 1200px; margin: 0 auto; padding: 0 1.5rem 4rem; }
        .secao-titulo { font-family: 'Playfair Display', serif; font-size: 1.8rem; color: var(--cinza-900); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.8rem; padding-left: 1rem; border-left: 4px solid var(--rosa-500); }
        .grid-destaques { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 1.8rem; margin-bottom: 3rem; }
        .card-destaque { background: var(--branco); border-radius: 20px; overflow: hidden; box-shadow: var(--sombra-lg); border: 2px solid var(--rosa-200); position: relative; transition: all 0.3s; }
        .card-destaque:hover { transform: translateY(-8px); box-shadow: 0 20px 50px rgba(255,77,109,0.2); }
        .badge-destaque { position: absolute; top: 1rem; left: 1rem; background: linear-gradient(135deg, #f6d365, #fda085); color: white; padding: 0.3rem 0.9rem; border-radius: 50px; font-size: 0.75rem; font-weight: 700; z-index: 2; display: flex; align-items: center; gap: 0.3rem; }
        .card-img-destaque { width: 100%; height: 220px; background: linear-gradient(135deg, var(--rosa-100), var(--rosa-200)); display: flex; align-items: center; justify-content: center; font-size: 5rem; overflow: hidden; }
        .card-img-destaque img { width: 100%; height: 100%; object-fit: cover; }
        .card-body { padding: 1.5rem; }
        .card-body h3 { font-size: 1.2rem; font-weight: 700; color: var(--cinza-900); margin-bottom: 0.5rem; }
        .card-body .descricao { color: var(--cinza-500); font-size: 0.88rem; line-height: 1.6; margin-bottom: 1rem; }
        .card-loja { display: flex; align-items: center; gap: 0.4rem; font-size: 0.82rem; color: var(--cinza-400); margin-bottom: 0.8rem; }
        .card-loja i { color: var(--rosa-400); }
        .card-preco { font-size: 1.5rem; font-weight: 700; color: var(--rosa-600); margin-bottom: 1rem; }
        .card-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid var(--cinza-100); }
        .card-contato { font-size: 0.82rem; color: var(--cinza-400); display: flex; align-items: center; gap: 0.3rem; }
        .btn-ver { background: linear-gradient(135deg, var(--rosa-400), var(--rosa-600)); color: white; padding: 0.5rem 1.2rem; border-radius: 50px; font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: all 0.2s; display: inline-flex; align-items: center; gap: 0.4rem; }
        .btn-ver:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(255,77,109,0.4); }
        .grid-anuncios { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
        .card-anuncio { background: var(--branco); border-radius: 16px; overflow: hidden; box-shadow: var(--sombra-sm); border: 1px solid var(--cinza-100); transition: all 0.3s; }
        .card-anuncio:hover { transform: translateY(-5px); box-shadow: var(--sombra-md); border-color: var(--rosa-200); }
        .card-anuncio-img { width: 100%; height: 160px; background: linear-gradient(135deg, var(--rosa-50), var(--rosa-100)); display: flex; align-items: center; justify-content: center; font-size: 3.5rem; overflow: hidden; }
        .card-anuncio-img img { width: 100%; height: 100%; object-fit: cover; }
        .card-anuncio-body { padding: 1.2rem; }
        .card-anuncio-body h3 { font-size: 1rem; font-weight: 600; color: var(--cinza-900); margin-bottom: 0.4rem; }
        .vazio { text-align: center; padding: 4rem; color: var(--cinza-400); }
        .vazio i { font-size: 4rem; display: block; margin-bottom: 1rem; color: var(--rosa-200); }
        .btn-anunciar { display: inline-flex; align-items: center; gap: 0.5rem; background: linear-gradient(135deg, var(--rosa-400), var(--rosa-600)); color: white; padding: 0.9rem 2rem; border-radius: 50px; font-weight: 600; text-decoration: none; transition: all 0.3s; box-shadow: 0 4px 15px rgba(255,77,109,0.3); margin-top: 1rem; }
        .btn-anunciar:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(255,77,109,0.5); }
    </style>
</head>
<body>

<header>
    <div class="container-header">
        <a href="index.php" class="logo"><i class="fas fa-seedling"></i><span>FLORI</span></a>
        <div class="icons">
            <a href="index.php" class="icon-link" title="Início"><i class="fas fa-home"></i></a>
            <?php if ($auth->isLoggedIn()): ?>
                <?php if ($auth->isVendedor()): ?>
                    <a href="vendedor/dashboard.php?aba=anuncios" class="icon-link"><i class="fas fa-plus-circle"></i> Meus Anúncios</a>
                <?php endif; ?>
                <?php if ($auth->isAdmin()): ?>
                    <a href="admin/dashboard.php" class="icon-link"><i class="fas fa-crown"></i></a>
                <?php endif; ?>
                <a href="carrinho.php" class="icon-link"><i class="fas fa-shopping-cart"></i></a>
                <a href="../controller/AuthController.php?action=logout" class="icon-link"><i class="fas fa-sign-out-alt"></i></a>
            <?php else: ?>
                <a href="login.php" class="icon-link"><i class="fas fa-sign-in-alt"></i> Entrar</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<div class="anuncios-hero">
    <h1>🌸 Anúncios em Destaque</h1>
    <p>Descubra ofertas especiais das nossas lojas parceiras</p>
    <?php if ($auth->isVendedor()): ?>
        <a href="vendedor/dashboard.php?aba=anuncios" class="btn-anunciar" style="margin-top:1.5rem;">
            <i class="fas fa-plus"></i> Criar Anúncio
        </a>
    <?php elseif (!$auth->isLoggedIn()): ?>
        <a href="login.php" class="btn-anunciar" style="margin-top:1.5rem;">
            <i class="fas fa-store"></i> Sou Vendedor — Anunciar
        </a>
    <?php endif; ?>
</div>

<div class="anuncios-wrapper">

    <?php if (!empty($destaques)): ?>
    <h2 class="secao-titulo"><i class="fas fa-star" style="color:#fda085;"></i> Em Destaque</h2>
    <div class="grid-destaques">
        <?php foreach ($destaques as $a): ?>
        <div class="card-destaque">
            <div class="badge-destaque"><i class="fas fa-star"></i> Destaque</div>
            <div class="card-img-destaque">
                <?php if ($a['imagem']): ?>
                    <img src="../assets/anuncios/<?= htmlspecialchars($a['imagem']) ?>" alt="<?= htmlspecialchars($a['titulo']) ?>">
                <?php else: ?>🌸<?php endif; ?>
            </div>
            <div class="card-body">
                <div class="card-loja"><i class="fas fa-store"></i> <?= htmlspecialchars($a['nome_loja']) ?><?= $a['cidade'] ? ' — '.htmlspecialchars($a['cidade']) : '' ?></div>
                <h3><?= htmlspecialchars($a['titulo']) ?></h3>
                <p class="descricao"><?= nl2br(htmlspecialchars($a['descricao'])) ?></p>
                <?php if ($a['preco']): ?><div class="card-preco">R$ <?= number_format($a['preco'], 2, ',', '.') ?></div><?php endif; ?>
                <div class="card-footer">
                    <span class="card-contato"><?= $a['telefone'] ? '<i class="fas fa-phone"></i> '.htmlspecialchars($a['telefone']) : '' ?></span>
                    <?php if ($a['link']): ?><a href="<?= htmlspecialchars($a['link']) ?>" class="btn-ver" target="_blank"><i class="fas fa-external-link-alt"></i> Ver Mais</a><?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($normais)): ?>
    <h2 class="secao-titulo"><i class="fas fa-bullhorn"></i> Todos os Anúncios</h2>
    <div class="grid-anuncios">
        <?php foreach ($normais as $a): ?>
        <div class="card-anuncio">
            <div class="card-anuncio-img">
                <?php if ($a['imagem']): ?>
                    <img src="../assets/anuncios/<?= htmlspecialchars($a['imagem']) ?>" alt="<?= htmlspecialchars($a['titulo']) ?>">
                <?php else: ?>🌺<?php endif; ?>
            </div>
            <div class="card-anuncio-body">
                <div class="card-loja"><i class="fas fa-store"></i> <?= htmlspecialchars($a['nome_loja']) ?></div>
                <h3><?= htmlspecialchars($a['titulo']) ?></h3>
                <p style="font-size:0.83rem;color:var(--cinza-500);margin:0.4rem 0 0.8rem;"><?= htmlspecialchars(substr($a['descricao'], 0, 80)) ?>...</p>
                <?php if ($a['preco']): ?><div style="font-weight:700;color:var(--rosa-600);font-size:1.1rem;margin-bottom:0.8rem;">R$ <?= number_format($a['preco'], 2, ',', '.') ?></div><?php endif; ?>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:0.78rem;color:var(--cinza-400);"><?= $a['telefone'] ? '<i class="fas fa-phone"></i> '.htmlspecialchars($a['telefone']) : '' ?></span>
                    <?php if ($a['link']): ?><a href="<?= htmlspecialchars($a['link']) ?>" class="btn-ver" target="_blank" style="font-size:0.78rem;padding:0.4rem 0.9rem;">Ver Mais</a><?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (empty($anuncios)): ?>
    <div class="vazio">
        <i class="fas fa-bullhorn"></i>
        <h2>Nenhum anúncio ainda</h2>
        <p>Seja o primeiro a anunciar sua loja aqui!</p>
        <?php if ($auth->isVendedor()): ?>
            <a href="vendedor/dashboard.php?aba=anuncios" class="btn-anunciar"><i class="fas fa-plus"></i> Criar Anúncio</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<footer>
    <div class="footer-content">
        <div class="footer-col"><h3><i class="fas fa-seedling"></i> FLORI</h3><p>Levamos beleza e emoção através de flores selecionadas.</p></div>
        <div class="footer-col"><h4>Links</h4><ul><li><a href="index.php">Início</a></li><li><a href="anuncios.php">Anúncios</a></li><li><a href="carrinho.php">Carrinho</a></li></ul></div>
        <div class="footer-col"><h4>Contato</h4><p><i class="fas fa-envelope"></i> contato@flori.com.br</p><p><i class="fas fa-phone"></i> (11) 3456-7890</p></div>
    </div>
    <div class="footer-bottom"><p>© <?= date('Y') ?> FLORI 🌸</p></div>
</footer>

</body>
</html>
