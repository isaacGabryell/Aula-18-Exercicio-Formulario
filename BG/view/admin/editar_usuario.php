<?php
session_start();
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../model/dao/UsuarioDAO.php';

$auth = new AuthController();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$usuarioDAO = new UsuarioDAO();
$id = (int) ($_GET['id'] ?? 0);
$usuario = $usuarioDAO->buscarPorId($id);

if (!$usuario) {
    header('Location: dashboard.php');
    exit();
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario->setNome($_POST['nome']);
    $usuario->setTelefone($_POST['telefone']);

    if ($usuarioDAO->atualizar($usuario)) {
        if (!empty($_POST['senha'])) {
            $usuarioDAO->atualizarSenha($id, password_hash($_POST['senha'], PASSWORD_DEFAULT));
        }
        $usuarioDAO->alterarStatus($id, isset($_POST['ativo']) ? 1 : 0);
        $sucesso = 'Usuário atualizado com sucesso!';
        $usuario = $usuarioDAO->buscarPorId($id);
    } else {
        $erro = 'Erro ao atualizar usuário.';
    }
}

$perfis = $usuarioDAO->listarPerfis();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌸 FLORI | Editar Usuário</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../assets/estilo.css">
</head>
<body>
    <div class="admin-header">
        <h1><i class="fas fa-crown"></i> Painel Administrativo - FLORI</h1>
        <div class="admin-nav">
            <span>👑 <?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
            <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Voltar</a>
            <a href="../../controller/AuthController.php?action=logout"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </div>
    </div>

    <div class="admin-dashboard">
        <h2><i class="fas fa-user-edit"></i> Editar Usuário</h2>

        <?php if ($sucesso): ?>
            <div class="alert success"><?= htmlspecialchars($sucesso) ?></div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="alert error"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="POST" class="form-produto">
            <div class="form-group">
                <label>Nome</label>
                <input type="text" name="nome" value="<?= htmlspecialchars($usuario->getNome()) ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" value="<?= htmlspecialchars($usuario->getEmail()) ?>" disabled>
            </div>
            <div class="form-group">
                <label>Telefone</label>
                <input type="text" name="telefone" value="<?= htmlspecialchars($usuario->getTelefone()) ?>">
            </div>
            <div class="form-group">
                <label>Nova Senha <small>(deixe em branco para não alterar)</small></label>
                <input type="password" name="senha" placeholder="Nova senha">
            </div>
            <div class="form-group">
                <label>Perfil</label>
                <input type="text" value="<?= ucfirst(htmlspecialchars($usuario->getNomePerfil())) ?>" disabled>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="ativo" value="1" <?= $usuario->getAtivo() ? 'checked' : '' ?>>
                    Usuário Ativo
                </label>
            </div>
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i> Salvar Alterações
            </button>
        </form>
    </div>
</body>
</html>
