<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../model/dao/UsuarioDAO.php';
require_once __DIR__ . '/../model/dao/dto/UsuarioDTO.php';

class AuthController {

    public function login($email, $senha) {
        $usuarioDAO = new UsuarioDAO();
        $usuario = $usuarioDAO->buscarPorEmail($email);

        if (!$usuario) {
            return ['success' => false, 'message' => 'Usuário não encontrado'];
        }
        if (!$usuario->getAtivo()) {
            return ['success' => false, 'message' => 'Usuário inativo'];
        }
        if (!password_verify($senha, $usuario->getSenha())) {
            return ['success' => false, 'message' => 'Email ou senha incorretos'];
        }

        $_SESSION['usuario_id']       = $usuario->getIdUsuario();
        $_SESSION['usuario_nome']     = $usuario->getNome();
        $_SESSION['usuario_perfil']   = $usuario->getNomePerfil();
        $_SESSION['usuario_perfil_id']= $usuario->getIdPerfil();
        $_SESSION['floricultura_id']  = $usuario->getIdFloricultura();

        return ['success' => true, 'user' => [
            'id'     => $usuario->getIdUsuario(),
            'nome'   => $usuario->getNome(),
            'perfil' => $usuario->getNomePerfil()
        ]];
    }

    public function cadastrar($dados) {
        $usuarioDAO = new UsuarioDAO();
        $usuario = new UsuarioDTO();

        $usuario->setNome($dados['nome']);
        $usuario->setTelefone($dados['telefone'] ?? '');
        $usuario->setEmail($dados['email']);
        $usuario->setSenha($dados['senha']);

        $idPerfil = $usuarioDAO->buscarPerfilPorNome($dados['tipo'] ?? 'cliente');
        if (!$idPerfil) {
            return ['success' => false, 'message' => 'Tipo de usuário inválido'];
        }
        $usuario->setIdPerfil($idPerfil);

        if (isset($dados['nome_floricultura'])) {
            $usuario->setNomeFloricultura($dados['nome_floricultura']);
        }

        $resultado = $usuarioDAO->criar($usuario);
        if ($resultado) {
            $loginResult = $this->login($dados['email'], $dados['senha']);
            if ($loginResult['success']) {
                return ['success' => true, 'message' => 'Cadastro realizado com sucesso!', 'user' => $loginResult['user']];
            }
            return ['success' => false, 'message' => 'Cadastro realizado, mas erro no login: ' . $loginResult['message']];
        }
        return ['success' => false, 'message' => 'Erro ao cadastrar usuário'];
    }

    public function logout() {
        session_destroy();
        header('Location: ../view/login.php');
        exit();
    }

    public function isLoggedIn() { return isset($_SESSION['usuario_id']); }
    public function isAdmin()    { return ($_SESSION['usuario_perfil'] ?? '') === 'admin'; }
    public function isVendedor() { return ($_SESSION['usuario_perfil'] ?? '') === 'vendedor'; }
    public function isCliente()  { return ($_SESSION['usuario_perfil'] ?? '') === 'cliente'; }
}

// Roteamento — só executa quando este arquivo é o script principal
if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        header('Content-Type: application/json');
        $auth = new AuthController();
        switch ($_POST['action']) {
            case 'login':
                echo json_encode($auth->login($_POST['email'], $_POST['senha']));
                break;
            case 'cadastrar':
                echo json_encode($auth->cadastrar($_POST));
                break;
        }
        exit();
    }

    if (isset($_GET['action']) && $_GET['action'] === 'logout') {
        (new AuthController())->logout();
    }
}
