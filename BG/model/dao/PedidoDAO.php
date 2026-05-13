<?php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/dto/PedidoDTO.php';

class PedidoDAO {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function criar(PedidoDTO $pedido) {
        try {
            $this->conn->beginTransaction();

            // Inserir pedido
            $sqlPedido = "INSERT INTO pedido (valor_total, id_usuario) 
                         VALUES (:valor_total, :id_usuario)";
            $stmt = $this->conn->prepare($sqlPedido);
            $stmt->execute([
                ':valor_total' => $pedido->getValorTotal(),
                ':id_usuario' => $pedido->getIdUsuario()
            ]);
            $idPedido = $this->conn->lastInsertId();

            // Inserir itens
            foreach ($pedido->getItens() as $item) {
                $sqlItem = "INSERT INTO item_pedido (quantidade, preco_venda, id_pedido, id_produto) 
                           VALUES (:quantidade, :preco_venda, :id_pedido, :id_produto)";
                $stmt = $this->conn->prepare($sqlItem);
                $stmt->execute([
                    ':quantidade' => $item->getQuantidade(),
                    ':preco_venda' => $item->getPrecoVenda(),
                    ':id_pedido' => $idPedido,
                    ':id_produto' => $item->getIdProduto()
                ]);

                // Atualizar estoque
                $sqlEstoque = "UPDATE produto SET estoque = estoque - :qtd 
                              WHERE id_produto = :id AND estoque >= :qtd2";
                $stmt = $this->conn->prepare($sqlEstoque);
                $stmt->execute([
                    ':qtd' => $item->getQuantidade(),
                    ':id' => $item->getIdProduto(),
                    ':qtd2' => $item->getQuantidade()
                ]);
            }

            $this->conn->commit();
            return $idPedido;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function buscarPorId($id) {
        $sql = "SELECT p.*, u.nome as nome_cliente
                FROM pedido p
                JOIN usuario u ON p.id_usuario = u.id_usuario
                WHERE p.id_pedido = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->rowToDTO($row) : null;
    }

    public function listarPorUsuario($idUsuario) {
        $sql = "SELECT p.*, u.nome as nome_cliente
                FROM pedido p
                JOIN usuario u ON p.id_usuario = u.id_usuario
                WHERE p.id_usuario = :id_usuario
                ORDER BY p.data_pedido DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_usuario' => $idUsuario]);
        $pedidos = [];
        while ($row = $stmt->fetch()) {
            $pedidos[] = $this->rowToDTO($row);
        }
        return $pedidos;
    }

    public function listarTodos() {
        $sql = "SELECT p.*, u.nome as nome_cliente
                FROM pedido p
                JOIN usuario u ON p.id_usuario = u.id_usuario
                ORDER BY p.data_pedido DESC";
        $stmt = $this->conn->query($sql);
        $pedidos = [];
        while ($row = $stmt->fetch()) {
            $pedidos[] = $this->rowToDTO($row);
        }
        return $pedidos;
    }

    public function buscarItensPedido($idPedido) {
        $sql = "SELECT ip.*, pr.nome as nome_produto
                FROM item_pedido ip
                JOIN produto pr ON ip.id_produto = pr.id_produto
                WHERE ip.id_pedido = :id_pedido";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_pedido' => $idPedido]);
        return $stmt->fetchAll();
    }

    public function atualizarStatus($id, $status) {
        $sql = "UPDATE pedido SET status = :status WHERE id_pedido = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    public function faturamentoMes() {
        $sql = "SELECT COALESCE(SUM(valor_total), 0) as total
                FROM pedido
                WHERE MONTH(data_pedido) = MONTH(NOW())
                AND YEAR(data_pedido) = YEAR(NOW())
                AND status != 'cancelado'";
        $stmt = $this->conn->query($sql);
        return (float) $stmt->fetch()['total'];
    }

    private function rowToDTO($row) {
        $dto = new PedidoDTO();
        $dto->setIdPedido($row['id_pedido']);
        $dto->setDataPedido($row['data_pedido']);
        $dto->setValorTotal($row['valor_total']);
        $dto->setStatus($row['status'] ?? 'pendente');
        $dto->setIdUsuario($row['id_usuario']);
        $dto->setNomeCliente($row['nome_cliente'] ?? '');
        return $dto;
    }
}
?>