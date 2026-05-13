<?php
session_start();
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/DashboardController.php';

$auth = new AuthController();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$dashboard = new DashboardController();
$estatisticas  = $dashboard->getEstatisticas();
$usuarios      = $dashboard->getUsuarios();
$produtos      = $dashboard->getProdutos();
$pedidos       = $dashboard->getPedidos();
$floriculturas = $dashboard->getFloriculturas();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌸 FLORI | Painel Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../assets/estilo.css">
</head>
<body>

<div class="admin-header">
    <h1><i class="fas fa-crown"></i> Painel Administrativo</h1>
    <div class="admin-nav">
        <span><i class="fas fa-user-shield"></i> <?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
        <a href="../index.php"><i class="fas fa-eye"></i> Ver Loja</a>
        <a href="anuncios.php"><i class="fas fa-bullhorn"></i> Anúncios</a>
        <a href="../../controller/AuthController.php?action=logout"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>
</div>

<div class="admin-dashboard">

    <!-- Cards de Estatísticas -->
    <div class="stats-cards">
        <div class="stat-card">
            <i class="fas fa-users"></i>
            <h3><?= $estatisticas['total_usuarios'] ?></h3>
            <p>Usuários</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-store"></i>
            <h3><?= count($floriculturas) ?></h3>
            <p>Lojas Parceiras</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-box-open"></i>
            <h3><?= $estatisticas['total_produtos'] ?></h3>
            <p>Produtos</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-shopping-bag"></i>
            <h3><?= $estatisticas['total_pedidos'] ?></h3>
            <p>Pedidos</p>
        </div>
        <div class="stat-card stat-card-destaque">
            <i class="fas fa-chart-line"></i>
            <h3>R$ <?= number_format($estatisticas['faturamento_mes'], 2, ',', '.') ?></h3>
            <p>Faturamento do Mês</p>
        </div>
    </div>

    <!-- Lojas Parceiras -->
    <h2><i class="fas fa-store"></i> Lojas Parceiras</h2>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome da Loja</th>
                    <th>Vendedor</th>
                    <th>Endereço</th>
                    <th>Cidade</th>
                    <th>Telefone</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($floriculturas as $loja): ?>
                <tr>
                    <td><?= $loja['id_floricultura'] ?></td>
                    <td><strong><?= htmlspecialchars($loja['nome']) ?></strong></td>
                    <td><?= htmlspecialchars($loja['nome_vendedor']) ?></td>
                    <td><?= htmlspecialchars($loja['endereco'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($loja['cidade'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($loja['telefone'] ?? '-') ?></td>
                    <td class="acoes">
                        <a href="relatorio_loja.php?id=<?= $loja['id_floricultura'] ?>" class="btn-acao btn-editar"><i class="fas fa-chart-bar"></i> Relatório</a>
                        <a href="editar_floricultura.php?id=<?= $loja['id_floricultura'] ?>" class="btn-acao" style="background:#e0e7ff;color:#3730a3;"><i class="fas fa-edit"></i> Editar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Usuários -->
    <h2><i class="fas fa-users"></i> Usuários Cadastrados</h2>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Perfil</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $user): ?>
                <tr>
                    <td><?= $user->getIdUsuario() ?></td>
                    <td><strong><?= htmlspecialchars($user->getNome()) ?></strong></td>
                    <td><?= htmlspecialchars($user->getEmail()) ?></td>
                    <td><span class="badge badge-<?= $user->getNomePerfil() ?>"><?= ucfirst($user->getNomePerfil()) ?></span></td>
                    <td><span class="status-pill <?= $user->getAtivo() ? 'pill-ativo' : 'pill-inativo' ?>"><?= $user->getAtivo() ? 'Ativo' : 'Inativo' ?></span></td>
                    <td class="acoes">
                        <a href="editar_usuario.php?id=<?= $user->getIdUsuario() ?>" class="btn-acao btn-editar"><i class="fas fa-edit"></i> Editar</a>
                        <a href="excluir_usuario.php?id=<?= $user->getIdUsuario() ?>" class="btn-acao btn-excluir" onclick="return confirm('Tem certeza?')"><i class="fas fa-trash"></i> Excluir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Produtos -->
    <h2><i class="fas fa-box-open"></i> Produtos</h2>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>Floricultura</th>
                    <th>Preço</th>
                    <th>Estoque</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $produto): ?>
                <tr>
                    <td><?= $produto->getIdProduto() ?></td>
                    <td><strong><?= htmlspecialchars($produto->getNome()) ?></strong></td>
                    <td><?= htmlspecialchars($produto->getNomeFloricultura()) ?></td>
                    <td><span class="preco-tag">R$ <?= number_format($produto->getPreco(), 2, ',', '.') ?></span></td>
                    <td>
                        <span class="estoque-tag <?= $produto->getEstoque() > 10 ? 'estoque-ok' : ($produto->getEstoque() > 0 ? 'estoque-baixo' : 'estoque-zero') ?>">
                            <?= $produto->getEstoque() ?> unid.
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pedidos -->
    <h2><i class="fas fa-shopping-bag"></i> Pedidos</h2>
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
                <?php foreach ($pedidos as $pedido): ?>
                <tr>
                    <td><?= $pedido->getIdPedido() ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($pedido->getDataPedido())) ?></td>
                    <td><strong><?= htmlspecialchars($pedido->getNomeCliente()) ?></strong></td>
                    <td><span class="preco-tag">R$ <?= number_format($pedido->getValorTotal(), 2, ',', '.') ?></span></td>
                    <td><span class="status-<?= $pedido->getStatus() ?>"><?= ucfirst($pedido->getStatus()) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>
