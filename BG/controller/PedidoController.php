<?php
require_once __DIR__ . '/../model/dao/PedidoDAO.php';
require_once __DIR__ . '/../model/dao/ProdutoDAO.php';
require_once __DIR__ . '/../model/dao/dto/PedidoDTO.php';

class PedidoController {
    private $pedidoDAO;
    private $produtoDAO;

    public function __construct() {
        $this->pedidoDAO = new PedidoDAO();
        $this->produtoDAO = new ProdutoDAO();
    }

    public function finalizarPedido($dados) {
        $pedido = new PedidoDTO();
        $pedido->setIdUsuario($dados['id_usuario']);
        
        $valorTotal = 0;
        $itens = [];

        foreach ($dados['itens'] as $item) {
            $produto = $this->produtoDAO->buscarPorId($item['id_produto']);
            if (!$produto || $produto->getEstoque() < $item['quantidade']) {
                return ['success' => false, 'message' => 'Produto indisponível: ' . $produto->getNome()];
            }

            $itemDTO = new ItemPedidoDTO();
            $itemDTO->setIdProduto($item['id_produto']);
            $itemDTO->setQuantidade($item['quantidade']);
            $itemDTO->setPrecoVenda($produto->getPreco());
            
            $itens[] = $itemDTO;
            $valorTotal += $produto->getPreco() * $item['quantidade'];
        }

        $pedido->setValorTotal($valorTotal);
        $pedido->setItens($itens);

        $resultado = $this->pedidoDAO->criar($pedido);
        
        if ($resultado) {
            return ['success' => true, 'pedido_id' => $resultado];
        }
        
        return ['success' => false, 'message' => 'Erro ao finalizar pedido'];
    }

    public function listarPedidosUsuario($idUsuario) {
        return $this->pedidoDAO->listarPorUsuario($idUsuario);
    }

    public function listarTodos() {
        return $this->pedidoDAO->listarTodos();
    }

    public function buscarItens($idPedido) {
        return $this->pedidoDAO->buscarItensPedido($idPedido);
    }
}
?>