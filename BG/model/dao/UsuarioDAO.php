<?php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/dto/UsuarioDTO.php';

class UsuarioDAO {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function buscarPorEmail($email) {
        $sql = "SELECT u.*, p.nome as nome_perfil, l.email, l.senha, l.ativo,
                       f.id_floricultura, f.nome as nome_floricultura,
                       f.endereco, f.cidade, f.telefone as telefone_floricultura
                FROM usuario u
                JOIN perfil p ON u.id_perfil = p.id_perfil
                JOIN login l ON u.id_usuario = l.id_usuario
                LEFT JOIN floricultura f ON u.id_usuario = f.id_usuario
                WHERE l.email = :email";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();

        if ($row) {
            return $this->rowToDTO($row);
        }
        return null;
    }

    public function buscarPorId($id) {
        $sql = "SELECT u.*, p.nome as nome_perfil, l.email, l.ativo,
                       f.id_floricultura, f.nome as nome_floricultura
                FROM usuario u
                JOIN perfil p ON u.id_perfil = p.id_perfil
                JOIN login l ON u.id_usuario = l.id_usuario
                LEFT JOIN floricultura f ON u.id_usuario = f.id_usuario
                WHERE u.id_usuario = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if ($row) {
            return $this->rowToDTO($row);
        }
        return null;
    }

    public function criar(UsuarioDTO $usuario) {
        try {
            $this->conn->beginTransaction();

            // Inserir usuário
            $sqlUsuario = "INSERT INTO usuario (nome, telefone, id_perfil) 
                          VALUES (:nome, :telefone, :id_perfil)";
            $stmt = $this->conn->prepare($sqlUsuario);
            $stmt->execute([
                ':nome' => $usuario->getNome(),
                ':telefone' => $usuario->getTelefone(),
                ':id_perfil' => $usuario->getIdPerfil()
            ]);
            $idUsuario = $this->conn->lastInsertId();

            // Inserir login
            $sqlLogin = "INSERT INTO login (email, senha, id_usuario) 
                        VALUES (:email, :senha, :id_usuario)";
            $stmt = $this->conn->prepare($sqlLogin);
            $stmt->execute([
                ':email' => $usuario->getEmail(),
                ':senha' => password_hash($usuario->getSenha(), PASSWORD_DEFAULT),
                ':id_usuario' => $idUsuario
            ]);

            // Se for vendedor, criar floricultura
            if ($usuario->getIdPerfil() == 2) {
                $nomeFloricultura = $usuario->getNomeFloricultura() ?: 'Loja de ' . $usuario->getNome();
                $sqlFloricultura = "INSERT INTO floricultura (nome, id_usuario) 
                                   VALUES (:nome, :id_usuario)";
                $stmt = $this->conn->prepare($sqlFloricultura);
                $stmt->execute([
                    ':nome' => $nomeFloricultura,
                    ':id_usuario' => $idUsuario
                ]);
            }

            $this->conn->commit();
            return $idUsuario;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function atualizar(UsuarioDTO $usuario) {
        $sql = "UPDATE usuario SET nome = :nome, telefone = :telefone 
                WHERE id_usuario = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':nome' => $usuario->getNome(),
            ':telefone' => $usuario->getTelefone(),
            ':id' => $usuario->getIdUsuario()
        ]);
    }

    public function atualizarSenha($id, $senhaHash) {
        $sql = "UPDATE login SET senha = :senha WHERE id_usuario = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':senha' => $senhaHash, ':id' => $id]);
    }

    public function excluir($id) {
        $sql = "DELETE FROM usuario WHERE id_usuario = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function alterarStatus($id, $ativo) {
        $sql = "UPDATE login SET ativo = :ativo WHERE id_usuario = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':ativo' => $ativo, ':id' => $id]);
    }

    public function listarTodos() {
        $sql = "SELECT u.*, p.nome as nome_perfil, l.email, l.ativo
                FROM usuario u
                JOIN perfil p ON u.id_perfil = p.id_perfil
                JOIN login l ON u.id_usuario = l.id_usuario
                ORDER BY u.nome";
        
        $stmt = $this->conn->query($sql);
        $usuarios = [];
        while ($row = $stmt->fetch()) {
            $usuarios[] = $this->rowToDTO($row);
        }
        return $usuarios;
    }

    public function buscarPerfilPorNome($nome) {
        $sql = "SELECT id_perfil FROM perfil WHERE nome = :nome";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':nome' => $nome]);
        $row = $stmt->fetch();
        return $row ? $row['id_perfil'] : null;
    }

    public function listarPerfis() {
        $sql = "SELECT * FROM perfil ORDER BY id_perfil";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll();
    }

    private function rowToDTO($row) {
        $dto = new UsuarioDTO();
        $dto->setIdUsuario($row['id_usuario']);
        $dto->setNome($row['nome']);
        $dto->setTelefone($row['telefone'] ?? '');
        $dto->setIdPerfil($row['id_perfil']);
        $dto->setNomePerfil($row['nome_perfil'] ?? '');
        $dto->setEmail($row['email'] ?? '');
        $dto->setSenha($row['senha'] ?? '');
        $dto->setAtivo($row['ativo'] ?? true);
        $dto->setIdFloricultura($row['id_floricultura'] ?? null);
        $dto->setNomeFloricultura($row['nome_floricultura'] ?? '');
        $dto->setEndereco($row['endereco'] ?? '');
        $dto->setCidade($row['cidade'] ?? '');
        $dto->setTelefoneFloricultura($row['telefone_floricultura'] ?? '');
        return $dto;
    }
}
?>