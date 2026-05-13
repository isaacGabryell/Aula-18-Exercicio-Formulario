<?php
session_start();
require_once __DIR__ . '/../controller/AuthController.php';
require_once __DIR__ . '/../controller/ProdutoController.php';
require_once __DIR__ . '/../config/Database.php';

$auth = new AuthController();
if (!$auth->isLoggedIn()) { header('Location: login.php'); exit(); }
if (empty($_SESSION['carrinho'])) { header('Location: carrinho.php'); exit(); }

$conn       = Database::getInstance()->getConnection();
$produtoDAO = new ProdutoDAO();

$itens  = [];
$total  = 0;
foreach ($_SESSION['carrinho'] as $id => $qtd) {
    $produto = $produtoDAO->buscarPorId($id);
    if (!$produto) continue;
    $subtotal = $produto->getPreco() * $qtd;
    $total   += $subtotal;
    $itens[]  = ['produto' => $produto, 'qtd' => $qtd, 'subtotal' => $subtotal];
}

$metodo    = $_POST['metodo'] ?? 'credito';
$usuarioId = $_SESSION['usuario_id'];

// Criar pedido no banco
try {
    $conn->beginTransaction();
    $stmt = $conn->prepare("INSERT INTO pedido (valor_total, id_usuario, status) VALUES (:total, :uid, 'confirmado')");
    $stmt->execute([':total' => $total, ':uid' => $usuarioId]);
    $idPedido = $conn->lastInsertId();

    foreach ($itens as $item) {
        $stmt = $conn->prepare("INSERT INTO item_pedido (quantidade, preco_venda, id_pedido, id_produto) VALUES (:qtd, :preco, :pid, :prodid)");
        $stmt->execute([':qtd'=>$item['qtd'],':preco'=>$item['produto']->getPreco(),':pid'=>$idPedido,':prodid'=>$item['produto']->getIdProduto()]);
        $conn->prepare("UPDATE produto SET estoque = estoque - :qtd WHERE id_produto = :id AND estoque >= :qtd2")
             ->execute([':qtd'=>$item['qtd'],':id'=>$item['produto']->getIdProduto(),':qtd2'=>$item['qtd']]);
    }
    $conn->commit();
} catch (Exception $e) {
    $conn->rollBack();
    header('Location: carrinho.php');
    exit();
}

// Limpar carrinho
$_SESSION['carrinho'] = [];

$metodos = ['credito'=>'Cartão de Crédito','debito'=>'Cartão de Débito','pix'=>'PIX','boleto'=>'Boleto Bancário'];
$metodoNome = $metodos[$metodo] ?? 'Não informado';
$dataHora   = date('d/m/Y H:i:s');
$protocolo  = strtoupper(substr(md5($idPedido . $usuarioId . time()), 0, 12));

// Buscar dados do usuário
$stmt = $conn->prepare("SELECT u.nome, l.email FROM usuario u JOIN login l ON u.id_usuario = l.id_usuario WHERE u.id_usuario = :id");
$stmt->execute([':id' => $usuarioId]);
$usuario = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌸 FLORI | Pedido Confirmado</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/estilo.css">
    <style>
        .confirmacao-wrapper { max-width: 750px; margin: 3rem auto; padding: 0 1.5rem; }
        .confirmacao-header { text-align: center; margin-bottom: 2.5rem; animation: fadeInUp 0.8s ease; }
        .icone-sucesso { width: 90px; height: 90px; background: linear-gradient(135deg, #d1fae5, #6ee7b7); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.2rem; font-size: 2.5rem; animation: popIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        @keyframes popIn { from { transform: scale(0); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .confirmacao-header h1 { font-family: 'Playfair Display', serif; font-size: 2.2rem; color: var(--cinza-900); margin-bottom: 0.5rem; }
        .confirmacao-header p { color: var(--cinza-500); font-size: 1rem; }
        .nota-fiscal { background: var(--branco); border-radius: 20px; box-shadow: var(--sombra-lg); overflow: hidden; border: 1px solid var(--cinza-200); }
        .nota-topo { background: linear-gradient(135deg, var(--rosa-500), var(--rosa-700)); padding: 2rem; color: white; display: flex; justify-content: space-between; align-items: center; }
        .nota-topo h2 { font-family: 'Playfair Display', serif; font-size: 1.8rem; }
        .nota-topo .protocolo { text-align: right; }
        .nota-topo .protocolo label { font-size: 0.7rem; opacity: 0.8; text-transform: uppercase; letter-spacing: 1px; display: block; }
        .nota-topo .protocolo span { font-size: 1.1rem; font-weight: 700; font-family: monospace; letter-spacing: 2px; }
        .nota-corpo { padding: 2rem; }
        .nota-secao { margin-bottom: 1.8rem; }
        .nota-secao h3 { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1.5px; color: var(--cinza-400); font-weight: 600; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--cinza-100); }
        .nota-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .nota-campo label { font-size: 0.78rem; color: var(--cinza-400); display: block; margin-bottom: 0.2rem; }
        .nota-campo span { font-size: 0.95rem; font-weight: 500; color: var(--cinza-800); }
        .nota-itens { width: 100%; border-collapse: collapse; }
        .nota-itens th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: var(--cinza-400); font-weight: 600; padding: 0.6rem 0; border-bottom: 1px solid var(--cinza-100); text-align: left; }
        .nota-itens td { padding: 0.8rem 0; border-bottom: 1px solid var(--cinza-50); font-size: 0.9rem; color: var(--cinza-700); }
        .nota-itens tr:last-child td { border-bottom: none; }
        .nota-total { background: linear-gradient(135deg, var(--rosa-50), var(--rosa-100)); border-radius: 14px; padding: 1.2rem 1.5rem; display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; }
        .nota-total span:first-child { font-size: 0.9rem; color: var(--cinza-600); font-weight: 500; }
        .nota-total span:last-child { font-size: 1.5rem; font-weight: 700; color: var(--rosa-600); }
        .nota-rodape { background: var(--cinza-50); padding: 1.2rem 2rem; text-align: center; font-size: 0.8rem; color: var(--cinza-400); border-top: 1px solid var(--cinza-100); }
        .status-confirmado { display: inline-flex; align-items: center; gap: 0.4rem; background: #d1fae5; color: #065f46; padding: 0.4rem 1rem; border-radius: 50px; font-size: 0.82rem; font-weight: 600; }
        .acoes-nota { display: flex; gap: 1rem; margin-top: 2rem; justify-content: center; flex-wrap: wrap; }
        .btn-imprimir { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.9rem 2rem; background: var(--cinza-800); color: white; border: none; border-radius: 14px; font-family: 'Poppins', sans-serif; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; }
        .btn-imprimir:hover { background: var(--cinza-900); transform: translateY(-2px); }
        .btn-voltar-loja { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.9rem 2rem; background: linear-gradient(135deg, var(--rosa-400), var(--rosa-600)); color: white; border: none; border-radius: 14px; font-family: 'Poppins', sans-serif; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; box-shadow: 0 4px 15px rgba(255,77,109,0.3); }
        .btn-voltar-loja:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(255,77,109,0.5); }
        @media print {
            header, .acoes-nota { display: none !important; }
            .nota-fiscal { box-shadow: none; border: 1px solid #ccc; }
            body { background: white; }
        }
    </style>
</head>
<body>

<header>
    <div class="container-header">
        <a href="index.php" class="logo"><i class="fas fa-seedling"></i><span>FLORI</span></a>
    </div>
</header>

<div class="confirmacao-wrapper">

    <div class="confirmacao-header">
        <div class="icone-sucesso">✅</div>
        <h1>Pedido Confirmado!</h1>
        <p>Obrigado pela sua compra, <strong><?= htmlspecialchars($usuario['nome']) ?></strong>! 🌸</p>
    </div>

    <div class="nota-fiscal" id="notaFiscal">

        <div class="nota-topo">
            <div>
                <h2>🌸 FLORI</h2>
                <p style="opacity:0.85;font-size:0.88rem;margin-top:0.3rem;">Nota Fiscal Eletrônica</p>
            </div>
            <div class="protocolo">
                <label>Protocolo</label>
                <span><?= $protocolo ?></span>
            </div>
        </div>

        <div class="nota-corpo">

            <!-- Informações do Pedido -->
            <div class="nota-secao">
                <h3>Informações do Pedido</h3>
                <div class="nota-grid">
                    <div class="nota-campo">
                        <label>Número do Pedido</label>
                        <span>#<?= str_pad($idPedido, 6, '0', STR_PAD_LEFT) ?></span>
                    </div>
                    <div class="nota-campo">
                        <label>Data e Hora</label>
                        <span><?= $dataHora ?></span>
                    </div>
                    <div class="nota-campo">
                        <label>Status</label>
                        <span class="status-confirmado"><i class="fas fa-check-circle"></i> Confirmado</span>
                    </div>
                    <div class="nota-campo">
                        <label>Forma de Pagamento</label>
                        <span><?= $metodoNome ?></span>
                    </div>
                </div>
            </div>

            <!-- Dados do Cliente -->
            <div class="nota-secao">
                <h3>Dados do Cliente</h3>
                <div class="nota-grid">
                    <div class="nota-campo">
                        <label>Nome</label>
                        <span><?= htmlspecialchars($usuario['nome']) ?></span>
                    </div>
                    <div class="nota-campo">
                        <label>Email</label>
                        <span><?= htmlspecialchars($usuario['email']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Itens -->
            <div class="nota-secao">
                <h3>Itens do Pedido</h3>
                <table class="nota-itens">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Loja</th>
                            <th style="text-align:center;">Qtd.</th>
                            <th style="text-align:right;">Preço Unit.</th>
                            <th style="text-align:right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($itens as $item): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($item['produto']->getNome()) ?></strong></td>
                            <td style="color:var(--cinza-400);font-size:0.82rem;"><?= htmlspecialchars($item['produto']->getNomeFloricultura()) ?></td>
                            <td style="text-align:center;"><?= $item['qtd'] ?></td>
                            <td style="text-align:right;">R$ <?= number_format($item['produto']->getPreco(), 2, ',', '.') ?></td>
                            <td style="text-align:right;font-weight:600;color:var(--rosa-600);">R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="nota-total">
                    <span>Total Pago</span>
                    <span>R$ <?= number_format($total, 2, ',', '.') ?></span>
                </div>
            </div>

        </div>

        <div class="nota-rodape">
            <p>🌸 FLORI — Flores que Encantam | contato@flori.com.br | (11) 3456-7890</p>
            <p style="margin-top:0.3rem;">Este documento é uma confirmação de compra gerada eletronicamente em <?= $dataHora ?></p>
        </div>

    </div>

    <div class="acoes-nota">
        <button onclick="window.print()" class="btn-imprimir">
            <i class="fas fa-print"></i> Imprimir Nota Fiscal
        </button>
        <a href="index.php" class="btn-voltar-loja">
            <i class="fas fa-store"></i> Continuar Comprando
        </a>
    </div>

</div>

</body>
</html>
