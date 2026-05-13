<?php
session_start();
require_once __DIR__ . '/../controller/AuthController.php';
require_once __DIR__ . '/../controller/ProdutoController.php';

$auth = new AuthController();
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION['carrinho'])) $_SESSION['carrinho'] = [];

// Controles de quantidade via GET
if (isset($_GET['remover_um'])) {
    $id = (int) $_GET['remover_um'];
    if (isset($_SESSION['carrinho'][$id])) {
        $_SESSION['carrinho'][$id]--;
        if ($_SESSION['carrinho'][$id] <= 0) unset($_SESSION['carrinho'][$id]);
    }
    header('Location: carrinho.php'); exit();
}
if (isset($_GET['adicionar_um'])) {
    $id = (int) $_GET['adicionar_um'];
    $_SESSION['carrinho'][$id] = ($_SESSION['carrinho'][$id] ?? 0) + 1;
    header('Location: carrinho.php'); exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['produto_id'])) {
    $id  = (int) $_POST['produto_id'];
    $qtd = max(1, (int) ($_POST['quantidade'] ?? 1));
    $_SESSION['carrinho'][$id] = ($_SESSION['carrinho'][$id] ?? 0) + $qtd;
    header('Location: carrinho.php');
    exit();
}

if (isset($_GET['remover'])) {
    unset($_SESSION['carrinho'][(int) $_GET['remover']]);
    header('Location: carrinho.php');
    exit();
}

if (isset($_GET['atualizar']) && isset($_POST['qtd'])) {
    $id  = (int) $_GET['atualizar'];
    $qtd = max(1, (int) $_POST['qtd']);
    $_SESSION['carrinho'][$id] = $qtd;
    header('Location: carrinho.php');
    exit();
}

$produtoDAO = new ProdutoDAO();
$itens = [];
$total = 0;
foreach ($_SESSION['carrinho'] as $id => $qtd) {
    $produto = $produtoDAO->buscarPorId($id);
    if (!$produto) continue;
    $subtotal = $produto->getPreco() * $qtd;
    $total   += $subtotal;
    $itens[]  = ['produto' => $produto, 'qtd' => $qtd, 'subtotal' => $subtotal];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌸 FLORI | Carrinho</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/estilo.css">
    <style>
        .carrinho-wrapper { max-width: 1100px; margin: 2rem auto; padding: 0 1.5rem; display: grid; grid-template-columns: 1fr 360px; gap: 2rem; }
        .carrinho-titulo { font-family: 'Playfair Display', serif; font-size: 2rem; color: var(--cinza-900); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.8rem; }
        .carrinho-titulo i { color: var(--rosa-500); }
        .carrinho-lista { background: var(--branco); border-radius: 20px; box-shadow: var(--sombra-md); overflow: hidden; border: 1px solid var(--rosa-100); }
        .carrinho-item { display: grid; grid-template-columns: 90px 1fr auto; gap: 1.2rem; align-items: center; padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--cinza-100); transition: background 0.2s; }
        .carrinho-item:last-child { border-bottom: none; }
        .carrinho-item:hover { background: var(--rosa-50); }
        .item-img { width: 90px; height: 90px; border-radius: 12px; object-fit: cover; background: linear-gradient(135deg, var(--rosa-50), var(--rosa-100)); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; overflow: hidden; }
        .item-img img { width: 100%; height: 100%; object-fit: cover; }
        .item-info h3 { font-size: 1rem; font-weight: 600; color: var(--cinza-900); margin-bottom: 0.3rem; }
        .item-info .item-loja { font-size: 0.82rem; color: var(--cinza-500); display: flex; align-items: center; gap: 0.3rem; }
        .item-info .item-preco-unit { font-size: 0.88rem; color: var(--cinza-500); margin-top: 0.3rem; }
        .item-acoes { display: flex; flex-direction: column; align-items: flex-end; gap: 0.8rem; }
        .item-subtotal { font-size: 1.1rem; font-weight: 700; color: var(--rosa-600); }
        .qtd-control { display: flex; align-items: center; gap: 0.5rem; background: var(--cinza-100); border-radius: 50px; padding: 0.3rem 0.8rem; }
        .qtd-control button { background: none; border: none; cursor: pointer; color: var(--cinza-600); font-size: 1rem; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: all 0.2s; }
        .qtd-control button:hover { background: var(--rosa-500); color: white; }
        .qtd-control span { font-weight: 600; min-width: 24px; text-align: center; font-size: 0.95rem; }
        .btn-remover-item { background: none; border: none; cursor: pointer; color: var(--cinza-400); font-size: 0.85rem; display: flex; align-items: center; gap: 0.3rem; transition: color 0.2s; padding: 0; }
        .btn-remover-item:hover { color: #e74c3c; }
        .carrinho-vazio { text-align: center; padding: 4rem 2rem; background: var(--branco); border-radius: 20px; box-shadow: var(--sombra-md); }
        .carrinho-vazio i { font-size: 5rem; color: var(--rosa-200); margin-bottom: 1rem; display: block; }
        .carrinho-vazio h2 { color: var(--cinza-600); font-size: 1.4rem; margin-bottom: 0.5rem; }
        .carrinho-vazio p { color: var(--cinza-400); margin-bottom: 1.5rem; }
        /* Resumo */
        .resumo-card { background: var(--branco); border-radius: 20px; box-shadow: var(--sombra-md); padding: 1.8rem; border: 1px solid var(--rosa-100); position: sticky; top: 100px; height: fit-content; }
        .resumo-card h2 { font-size: 1.2rem; font-weight: 700; color: var(--cinza-900); margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid var(--rosa-100); }
        .resumo-linha { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.8rem; font-size: 0.9rem; color: var(--cinza-600); }
        .resumo-total { display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; padding-top: 1rem; border-top: 2px solid var(--rosa-100); font-size: 1.2rem; font-weight: 700; color: var(--cinza-900); }
        .resumo-total span:last-child { color: var(--rosa-600); font-size: 1.4rem; }
        .btn-finalizar { width: 100%; padding: 1.1rem; background: linear-gradient(135deg, var(--rosa-400), var(--rosa-600)); color: white; border: none; border-radius: 14px; font-size: 1.05rem; font-weight: 700; font-family: 'Poppins', sans-serif; cursor: pointer; margin-top: 1.2rem; display: flex; align-items: center; justify-content: center; gap: 0.6rem; transition: all 0.3s; box-shadow: 0 4px 15px rgba(255,77,109,0.3); text-decoration: none; }
        .btn-finalizar:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(255,77,109,0.5); }
        .btn-continuar { display: flex; align-items: center; justify-content: center; gap: 0.5rem; width: 100%; padding: 0.9rem; background: var(--cinza-100); color: var(--cinza-700); border: none; border-radius: 14px; font-size: 0.95rem; font-weight: 500; font-family: 'Poppins', sans-serif; cursor: pointer; margin-top: 0.8rem; text-decoration: none; transition: all 0.2s; }
        .btn-continuar:hover { background: var(--cinza-200); }
        .frete-gratis { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #065f46; padding: 0.6rem 1rem; border-radius: 10px; font-size: 0.82rem; font-weight: 600; text-align: center; margin: 0.8rem 0; display: flex; align-items: center; justify-content: center; gap: 0.4rem; }
        @media (max-width: 768px) { .carrinho-wrapper { grid-template-columns: 1fr; } .resumo-card { position: static; } .carrinho-item { grid-template-columns: 70px 1fr; } .item-acoes { flex-direction: row; align-items: center; grid-column: 1/-1; justify-content: space-between; } }
    </style>
</head>
<body>

<header>
    <div class="container-header">
        <a href="index.php" class="logo"><i class="fas fa-seedling"></i><span>FLORI</span></a>
        <div class="icons">
            <a href="index.php" class="icon-link"><i class="fas fa-arrow-left"></i></a>
            <span class="user-info"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
        </div>
    </div>
</header>

<div class="carrinho-wrapper">

    <div>
        <h1 class="carrinho-titulo"><i class="fas fa-shopping-cart"></i> Meu Carrinho <?php if (!empty($itens)): ?><span style="font-size:1rem;color:var(--cinza-400);font-family:Poppins;">(<?= count($itens) ?> <?= count($itens)===1?'item':'itens' ?>)</span><?php endif; ?></h1>

        <?php if (empty($itens)): ?>
        <div class="carrinho-vazio">
            <i class="fas fa-shopping-cart"></i>
            <h2>Seu carrinho está vazio</h2>
            <p>Adicione produtos para continuar comprando</p>
            <a href="index.php" class="btn-finalizar" style="width:auto;padding:0.9rem 2rem;display:inline-flex;">
                <i class="fas fa-store"></i> Ver Produtos
            </a>
        </div>
        <?php else: ?>
        <div class="carrinho-lista">
            <?php foreach ($itens as $item): $p = $item['produto']; ?>
            <div class="carrinho-item">
                <div class="item-img">
                    <?php if ($p->getImagem()): ?>
                        <img src="../assets/uploads/<?= htmlspecialchars($p->getImagem()) ?>" alt="<?= htmlspecialchars($p->getNome()) ?>">
                    <?php else: ?>
                        🌸
                    <?php endif; ?>
                </div>
                <div class="item-info">
                    <h3><?= htmlspecialchars($p->getNome()) ?></h3>
                    <p class="item-loja"><i class="fas fa-store"></i> <?= htmlspecialchars($p->getNomeFloricultura()) ?></p>
                    <p class="item-preco-unit">R$ <?= number_format($p->getPreco(), 2, ',', '.') ?> / unid.</p>
                </div>
                <div class="item-acoes">
                    <span class="item-subtotal">R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></span>
                    <div class="qtd-control">
                        <a href="?remover_um=<?= $p->getIdProduto() ?>" style="all:unset;cursor:pointer;display:flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;transition:all 0.2s;" onmouseover="this.style.background='var(--rosa-500)';this.style.color='white'" onmouseout="this.style.background='';this.style.color=''">
                            <i class="fas fa-minus" style="font-size:0.7rem;"></i>
                        </a>
                        <span><?= $item['qtd'] ?></span>
                        <a href="?adicionar_um=<?= $p->getIdProduto() ?>" style="all:unset;cursor:pointer;display:flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;transition:all 0.2s;" onmouseover="this.style.background='var(--rosa-500)';this.style.color='white'" onmouseout="this.style.background='';this.style.color=''">
                            <i class="fas fa-plus" style="font-size:0.7rem;"></i>
                        </a>
                    </div>
                    <a href="?remover=<?= $p->getIdProduto() ?>" class="btn-remover-item"><i class="fas fa-trash-alt"></i> Remover</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($itens)): ?>
    <div>
        <div class="resumo-card">
            <h2><i class="fas fa-receipt" style="color:var(--rosa-500);margin-right:0.5rem;"></i> Resumo do Pedido</h2>
            <?php foreach ($itens as $item): ?>
            <div class="resumo-linha">
                <span><?= htmlspecialchars($item['produto']->getNome()) ?> x<?= $item['qtd'] ?></span>
                <span>R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></span>
            </div>
            <?php endforeach; ?>
            <div class="frete-gratis"><i class="fas fa-truck"></i> Frete grátis incluído!</div>
            <div class="resumo-total">
                <span>Total</span>
                <span>R$ <?= number_format($total, 2, ',', '.') ?></span>
            </div>
            <a href="pagamento.php" class="btn-finalizar">
                <i class="fas fa-lock"></i> Finalizar Compra
            </a>
            <a href="index.php" class="btn-continuar">
                <i class="fas fa-arrow-left"></i> Continuar Comprando
            </a>
        </div>
    </div>
    <?php endif; ?>

</div>

</body>
</html>
