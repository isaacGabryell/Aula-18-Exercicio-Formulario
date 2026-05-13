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

// Ações do admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle_ativo') {
        $id    = (int) $_POST['id'];
        $ativo = (int) $_POST['ativo'];
        $conn->prepare("UPDATE anuncio SET ativo=:ativo WHERE id_anuncio=:id")->execute([':ativo'=>$ativo,':id'=>$id]);
    }
    if ($action === 'toggle_destaque') {
        $id       = (int) $_POST['id'];
        $destaque = (int) $_POST['destaque'];
        $conn->prepare("UPDATE anuncio SET destaque=:d WHERE id_anuncio=:id")->execute([':d'=>$destaque,':id'=>$id]);
    }
    if ($action === 'excluir') {
        $conn->prepare("DELETE FROM anuncio WHERE id_anuncio=:id")->execute([':id'=>(int)$_POST['id']]);
    }
    header('Location: anuncios.php');
    exit();
}

// Buscar todos os anúncios
$anuncios = $conn->query("SELECT a.*, f.nome as nome_loja, u.nome as nome_vendedor
                           FROM anuncio a
                           JOIN floricultura f ON a.id_floricultura = f.id_floricultura
                           JOIN usuario u ON f.id_usuario = u.id_usuario
                           ORDER BY a.destaque DESC, a.data_criacao DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌸 FLORI | Gerenciar Anúncios</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../assets/estilo.css">
</head>
<body>

<div class="admin-header">
    <h1><i class="fas fa-bullhorn"></i> Gerenciar Anúncios</h1>
    <div class="admin-nav">
        <span><i class="fas fa-user-shield"></i> <?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
        <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Voltar</a>
        <a href="../anuncios.php" target="_blank"><i class="fas fa-eye"></i> Ver Página</a>
        <a href="../../controller/AuthController.php?action=logout"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>
</div>

<div class="admin-dashboard">

    <div class="stats-cards">
        <div class="stat-card">
            <i class="fas fa-bullhorn"></i>
            <h3><?= count($anuncios) ?></h3>
            <p>Total de Anúncios</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-check-circle"></i>
            <h3><?= count(array_filter($anuncios, fn($a) => $a['ativo'])) ?></h3>
            <p>Ativos</p>
        </div>
        <div class="stat-card stat-card-destaque">
            <i class="fas fa-star"></i>
            <h3><?= count(array_filter($anuncios, fn($a) => $a['destaque'])) ?></h3>
            <p>Em Destaque</p>
        </div>
    </div>

    <h2><i class="fas fa-list"></i> Todos os Anúncios</h2>

    <?php if (empty($anuncios)): ?>
        <div class="mensagem-vazio"><i class="fas fa-bullhorn" style="font-size:3rem;"></i><p>Nenhum anúncio cadastrado.</p></div>
    <?php else: ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Imagem</th>
                    <th>Título</th>
                    <th>Loja / Vendedor</th>
                    <th>Preço</th>
                    <th>Validade</th>
                    <th>Destaque</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($anuncios as $a): ?>
            <tr>
                <td><?= $a['id_anuncio'] ?></td>
                <td>
                    <?php if ($a['imagem']): ?>
                        <img src="../../assets/anuncios/<?= htmlspecialchars($a['imagem']) ?>" style="width:55px;height:45px;object-fit:cover;border-radius:8px;">
                    <?php else: ?>
                        <span style="font-size:1.8rem;">🌸</span>
                    <?php endif; ?>
                </td>
                <td>
                    <strong><?= htmlspecialchars($a['titulo']) ?></strong>
                    <?php if ($a['descricao']): ?>
                        <p style="font-size:0.78rem;color:var(--cinza-400);margin:0;"><?= htmlspecialchars(substr($a['descricao'],0,60)) ?>...</p>
                    <?php endif; ?>
                </td>
                <td>
                    <strong><?= htmlspecialchars($a['nome_loja']) ?></strong>
                    <p style="font-size:0.78rem;color:var(--cinza-400);margin:0;"><?= htmlspecialchars($a['nome_vendedor']) ?></p>
                </td>
                <td><?= $a['preco'] ? '<span class="preco-tag">R$ '.number_format($a['preco'],2,',','.').'</span>' : '-' ?></td>
                <td style="font-size:0.85rem;"><?= $a['data_expiracao'] ? date('d/m/Y', strtotime($a['data_expiracao'])) : 'Sem validade' ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="toggle_destaque">
                        <input type="hidden" name="id" value="<?= $a['id_anuncio'] ?>">
                        <input type="hidden" name="destaque" value="<?= $a['destaque'] ? 0 : 1 ?>">
                        <button type="submit" style="background:<?= $a['destaque'] ? '#fef3c7' : 'var(--cinza-100)' ?>;border:none;cursor:pointer;padding:0.4rem 0.8rem;border-radius:50px;font-size:0.8rem;font-weight:600;color:<?= $a['destaque'] ? '#92400e' : 'var(--cinza-500)' ?>;">
                            <?= $a['destaque'] ? '⭐ Destaque' : '☆ Normal' ?>
                        </button>
                    </form>
                </td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="toggle_ativo">
                        <input type="hidden" name="id" value="<?= $a['id_anuncio'] ?>">
                        <input type="hidden" name="ativo" value="<?= $a['ativo'] ? 0 : 1 ?>">
                        <button type="submit" class="status-pill <?= $a['ativo'] ? 'pill-ativo' : 'pill-inativo' ?>" style="border:none;cursor:pointer;font-family:Poppins;">
                            <?= $a['ativo'] ? 'Ativo' : 'Inativo' ?>
                        </button>
                    </form>
                </td>
                <td class="acoes">
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Excluir anúncio?')">
                        <input type="hidden" name="action" value="excluir">
                        <input type="hidden" name="id" value="<?= $a['id_anuncio'] ?>">
                        <button type="submit" class="btn-acao btn-excluir"><i class="fas fa-trash"></i> Excluir</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</div>
</body>
</html>
