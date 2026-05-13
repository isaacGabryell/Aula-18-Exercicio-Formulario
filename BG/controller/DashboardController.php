<?php
require_once __DIR__ . '/../model/dao/UsuarioDAO.php';
require_once __DIR__ . '/../model/dao/ProdutoDAO.php';
require_once __DIR__ . '/../model/dao/PedidoDAO.php';

class DashboardController {
    private $usuarioDAO;
    private $produtoDAO;
    private $pedidoDAO;

    public function __construct() {
        $this->usuarioDAO = new UsuarioDAO();
        $this->produtoDAO = new ProdutoDAO();
        $this->pedidoDAO = new PedidoDAO();
    }

    public function getEstatisticas() {
        return [
            'total_usuarios' => count($this->usuarioDAO->listarTodos()),
            'total_produtos' => count($this->produtoDAO->listarTodos()),
            'total_pedidos'  => count($this->pedidoDAO->listarTodos()),
            'faturamento_mes' => $this->pedidoDAO->faturamentoMes()
        ];
    }

    public function getUsuarios() {
        return $this->usuarioDAO->listarTodos();
    }

    public function getProdutos() {
        return $this->produtoDAO->listarTodos();
    }

    public function getPedidos() {
        return $this->pedidoDAO->listarTodos();
    }

    public function getFloriculturas() {
        $conn = Database::getInstance()->getConnection();
        $sql = "SELECT f.*, u.nome as nome_vendedor
                FROM floricultura f
                JOIN usuario u ON f.id_usuario = u.id_usuario
                ORDER BY f.nome";
        return $conn->query($sql)->fetchAll();
    }
}
?>