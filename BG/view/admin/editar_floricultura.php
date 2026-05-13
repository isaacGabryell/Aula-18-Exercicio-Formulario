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

$erro   = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("UPDATE floricultura SET nome = :nome, endereco = :endereco, cidade = :cidade, telefone = :telefone WHERE id_floricultura = :id");
    $ok = $stmt->execute([
        ':nome'     => $_POST['nome'],
        ':endereco' => $_POST['endereco'],
        ':cidade'   => $_POST['cidade'],
        ':telefone' => $_POST['telefone'],
        ':id'       => $id
    ]);
    if ($ok) {
        $sucesso = 'Loja atualizada com sucesso!';
        $loja['nome']     = $_POST['nome'];
        $loja['endereco'] = $_POST['endereco'];
        $loja['cidade']   = $_POST['cidade'];
        $loja['telefone'] = $_POST['telefone'];
    } else {
        $erro = 'Erro ao atualizar loja.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌸 FLORI | Editar Loja</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../assets/estilo.css">
</head>
<body>

<div class="admin-header">
    <h1><i class="fas fa-crown"></i> Painel Administrativo</h1>
    <div class="admin-nav">
        <span><i class="fas fa-user-shield"></i> <?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
        <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Voltar</a>
        <a href="../../controller/AuthController.php?action=logout"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>
</div>

<div class="admin-dashboard">
    <h2><i class="fas fa-store"></i> Editar Loja Parceira</h2>

    <?php if ($sucesso): ?>
        <div class="alert success"><i class="fas fa-check-circle"></i> <?= $sucesso ?></div>
    <?php endif; ?>
    <?php if ($erro): ?>
        <div class="alert error"><i class="fas fa-exclamation-circle"></i> <?= $erro ?></div>
    <?php endif; ?>

    <div class="form-produto">
        <div class="form-group">
            <label>Vendedor Responsável</label>
            <input type="text" value="<?= htmlspecialchars($loja['nome_vendedor']) ?>" disabled>
        </div>

        <form method="POST">
            <div class="form-group">
                <label>Nome da Loja</label>
                <input type="text" name="nome" value="<?= htmlspecialchars($loja['nome']) ?>" required>
            </div>
            <div class="form-group">
                <label>Endereço</label>
                <input type="text" name="endereco" value="<?= htmlspecialchars($loja['endereco'] ?? '') ?>" placeholder="Rua, número...">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Cidade</label>
                    <input type="text" name="cidade" value="<?= htmlspecialchars($loja['cidade'] ?? '') ?>" placeholder="Cidade">
                </div>
                <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" name="telefone" value="<?= htmlspecialchars($loja['telefone'] ?? '') ?>" placeholder="(00) 00000-0000">
                </div>
            </div>
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i> Salvar Alterações
            </button>
        </form>
    </div>
</div>
</body>
</html>
