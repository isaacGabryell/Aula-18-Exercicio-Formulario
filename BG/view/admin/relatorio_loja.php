<?php
session_start();
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../config/Database.php';

$auth = new AuthController();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$conn = Database::getInstance()->getConnection();
$id   = (int) ($_GET['id'] ?? 0);
$mes  = (int) ($_GET['mes'] ?? date('m'));
$ano  = (int) ($_GET['ano'] ?? date('Y'));

// Buscar dados da loja
$stmt = $conn->prepare("SELECT f.*, u.nome as nome_vendedor, u.id_usuario
                         FROM floricultura f
                         JOIN usuario u ON f.id_usuario = u.id_usuario
                         WHERE f.id_floricultura = :id");
$stmt->execute([':id' => $id]);
$loja = $stmt->fetch();

if (!$loja) {
    header('Location: dashboard.php');
    exit();
}

// Faturamento do mês
$stmt = $conn->prepare("SELECT COALESCE(SUM(ip.quantidade * ip.preco_venda), 0) as faturamento
                         FROM item_pedido ip
                         JOIN produto pr ON ip.id_produto = pr.id_produto
                         JOIN pedido p ON ip.id_pedido = p.id_pedido
                         WHERE pr.id_floricultura = :id
                         AND MONTH(p.data_pedido) = :mes
                         AND YEAR(p.data_pedido) = :ano
                         AND p.status != 'cancelado'");
$stmt->execute([':id' => $id, ':mes' => $mes, ':ano' => $ano]);
$faturamento = (float) $stmt->fetch()['faturamento'];

// Total de pedidos do mês
$stmt = $conn->prepare("SELECT COUNT(DISTINCT p.id_pedido) as total
                         FROM pedido p
                         JOIN item_pedido ip ON p.id_pedido = ip.id_pedido
                         JOIN produto pr ON ip.id_produto = pr.id_produto
                         WHERE pr.id_floricultura = :id
                         AND MONTH(p.data_pedido) = :mes
                         AND YEAR(p.data_pedido) = :ano");
$stmt->execute([':id' => $id, ':mes' => $mes, ':ano' => $ano]);
$totalPedidos = (int) $stmt->fetch()['total'];

// Itens vendidos no mês
$stmt = $conn->prepare("SELECT COALESCE(SUM(ip.quantidade), 0) as total
                         FROM item_pedido ip
                         JOIN produto pr ON ip.id_produto = pr.id_produto
                         JOIN pedido p ON ip.id_pedido = p.id_pedido
                         WHERE pr.id_floricultura = :id
                         AND MONTH(p.data_pedido) = :mes
                         AND YEAR(p.data_pedido) = :ano
                         AND p.status != 'cancelado'");
$stmt->execute([':id' => $id, ':mes' => $mes, ':ano' => $ano]);
$totalItens = (int) $stmt->fetch()['total'];

// Ticket médio
$ticketMedio = $totalPedidos > 0 ? $faturamento / $totalPedidos : 0;

// Produtos mais vendidos do mês
$stmt = $conn->prepare("SELECT pr.nome, SUM(ip.quantidade) as qtd_vendida, SUM(ip.quantidade * ip.preco_venda) as receita
                         FROM item_pedido ip
                         JOIN produto pr ON ip.id_produto = pr.id_produto
                         JOIN pedido p ON ip.id_pedido = p.id_pedido
                         WHERE pr.id_floricultura = :id
                         AND MONTH(p.data_pedido) = :mes
                         AND YEAR(p.data_pedido) = :ano
                         AND p.status != 'cancelado'
                         GROUP BY pr.id_produto, pr.nome
                         ORDER BY qtd_vendida DESC");
$stmt->execute([':id' => $id, ':mes' => $mes, ':ano' => $ano]);
$produtosMaisVendidos = $stmt->fetchAll();

// Pedidos do mês
$stmt = $conn->prepare("SELECT DISTINCT p.id_pedido, p.data_pedido, p.status, p.valor_total, u.nome as nome_cliente
                         FROM pedido p
                         JOIN item_pedido ip ON p.id_pedido = ip.id_pedido
                         JOIN produto pr ON ip.id_produto = pr.id_produto
                         JOIN usuario u ON p.id_usuario = u.id_usuario
                         WHERE pr.id_floricultura = :id
                         AND MONTH(p.data_pedido) = :mes
                         AND YEAR(p.data_pedido) = :ano
                         ORDER BY p.data_pedido DESC");
$stmt->execute([':id' => $id, ':mes' => $mes, ':ano' => $ano]);
$pedidosMes = $stmt->fetchAll();

// Faturamento dos últimos 6 meses para gráfico
$historico = [];
for ($i = 5; $i >= 0; $i--) {
    $m = date('m', strtotime("-$i months"));
    $a = date('Y', strtotime("-$i months"));
    $stmt = $conn->prepare("SELECT COALESCE(SUM(ip.quantidade * ip.preco_venda), 0) as fat
                             FROM item_pedido ip
                             JOIN produto pr ON ip.id_produto = pr.id_produto
                             JOIN pedido p ON ip.id_pedido = p.id_pedido
                             WHERE pr.id_floricultura = :id
                             AND MONTH(p.data_pedido) = :mes
                             AND YEAR(p.data_pedido) = :ano
                             AND p.status != 'cancelado'");
    $stmt->execute([':id' => $id, ':mes' => $m, ':ano' => $a]);
    $historico[] = [
        'label' => date('M/Y', strtotime("$a-$m-01")),
        'valor' => (float) $stmt->fetch()['fat']
    ];
}

$meses = ['', 'Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌸 FLORI | Relatório - <?= htmlspecialchars($loja['nome']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../assets/estilo.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="admin-header">
    <h1><i class="fas fa-chart-bar"></i> Relatório da Loja</h1>
    <div class="admin-nav">
        <span><i class="fas fa-store"></i> <?= htmlspecialchars($loja['nome']) ?></span>
        <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Voltar</a>
        <a href="../../controller/AuthController.php?action=logout"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>
</div>

<div class="admin-dashboard">

    <!-- Filtro de mês/ano -->
    <div class="filtro-relatorio">
        <form method="GET" style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
            <input type="hidden" name="id" value="<?= $id ?>">
            <div class="form-group" style="margin:0;">
                <select name="mes" style="padding:0.7rem 1rem; border-radius:8px; border:2px solid var(--rosa-200); font-family:Poppins,sans-serif;">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $m == $mes ? 'selected' : '' ?>><?= $meses[$m] ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group" style="margin:0;">
                <select name="ano" style="padding:0.7rem 1rem; border-radius:8px; border:2px solid var(--rosa-200); font-family:Poppins,sans-serif;">
                    <?php for ($a = date('Y'); $a >= date('Y') - 3; $a--): ?>
                        <option value="<?= $a ?>" <?= $a == $ano ? 'selected' : '' ?>><?= $a ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <button type="submit" class="btn-primary" style="width:auto; padding:0.7rem 1.5rem; margin:0;">
                <i class="fas fa-filter"></i> Filtrar
            </button>
        </form>
    </div>

    <h2 style="margin-top:1.5rem;"><i class="fas fa-calendar-alt"></i> <?= $meses[$mes] ?> / <?= $ano ?></h2>

    <!-- Cards do mês -->
    <div class="stats-cards">
        <div class="stat-card stat-card-destaque">
            <i class="fas fa-dollar-sign"></i>
            <h3>R$ <?= number_format($faturamento, 2, ',', '.') ?></h3>
            <p>Faturamento do Mês</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-shopping-bag"></i>
            <h3><?= $totalPedidos ?></h3>
            <p>Pedidos no Mês</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-box-open"></i>
            <h3><?= $totalItens ?></h3>
            <p>Itens Vendidos</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-receipt"></i>
            <h3>R$ <?= number_format($ticketMedio, 2, ',', '.') ?></h3>
            <p>Ticket Médio</p>
        </div>
    </div>

    <!-- Gráfico de histórico -->
    <h2><i class="fas fa-chart-line"></i> Faturamento — Últimos 6 Meses</h2>
    <div class="table-wrapper" style="padding:1.5rem;">
        <canvas id="graficoFaturamento" height="100"></canvas>
    </div>

    <!-- Produtos mais vendidos -->
    <?php if (!empty($produtosMaisVendidos)): ?>
    <h2><i class="fas fa-star"></i> Produtos Mais Vendidos</h2>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Qtd. Vendida</th>
                    <th>Receita</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtosMaisVendidos as $p): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($p['nome']) ?></strong></td>
                    <td><?= $p['qtd_vendida'] ?> unid.</td>
                    <td><span class="preco-tag">R$ <?= number_format($p['receita'], 2, ',', '.') ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Pedidos do mês -->
    <h2><i class="fas fa-list"></i> Pedidos do Mês</h2>
    <?php if (empty($pedidosMes)): ?>
        <div class="mensagem-vazio"><i class="fas fa-inbox" style="font-size:3rem;"></i><p>Nenhum pedido neste mês.</p></div>
    <?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Data</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidosMes as $pedido): ?>
                <tr>
                    <td><?= $pedido['id_pedido'] ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])) ?></td>
                    <td><strong><?= htmlspecialchars($pedido['nome_cliente']) ?></strong></td>
                    <td><span class="preco-tag">R$ <?= number_format($pedido['valor_total'], 2, ',', '.') ?></span></td>
                    <td><span class="status-<?= $pedido['status'] ?>"><?= ucfirst($pedido['status']) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</div>

<script>
const ctx = document.getElementById('graficoFaturamento').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($historico, 'label')) ?>,
        datasets: [{
            label: 'Faturamento (R$)',
            data: <?= json_encode(array_column($historico, 'valor')) ?>,
            backgroundColor: 'rgba(255, 77, 109, 0.2)',
            borderColor: 'rgba(255, 77, 109, 1)',
            borderWidth: 2,
            borderRadius: 8,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { callback: v => 'R$ ' + v.toLocaleString('pt-BR') } }
        }
    }
});
</script>
</body>
</html>
