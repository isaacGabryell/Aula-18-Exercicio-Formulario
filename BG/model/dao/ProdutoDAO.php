<?php
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/dto/ProdutoDTO.php';

class ProdutoDAO {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function buscarPorId($id) {
        $sql = "SELECT p.*, f.nome as nome_floricultura 
                FROM produto p
                JOIN floricultura f ON p.id_floricultura = f.id_floricultura
                WHERE p.id_produto = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->rowToDTO($row) : null;
    }

    public function listarTodos() {
        $sql = "SELECT p.*, f.nome as nome_floricultura 
                FROM produto p
                JOIN floricultura f ON p.id_floricultura = f.id_floricultura
                WHERE p.estoque > 0
                ORDER BY p.nome";
        $stmt = $this->conn->query($sql);
        $produtos = [];
        while ($row = $stmt->fetch()) {
            $produtos[] = $this->rowToDTO($row);
        }
        return $produtos;
    }

    public function listarPorFloricultura($idFloricultura) {
        $sql = "SELECT p.*, f.nome as nome_floricultura 
                FROM produto p
                JOIN floricultura f ON p.id_floricultura = f.id_floricultura
                WHERE p.id_floricultura = :id_floricultura
                ORDER BY p.nome";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_floricultura' => $idFloricultura]);
        $produtos = [];
        while ($row = $stmt->fetch()) {
            $produtos[] = $this->rowToDTO($row);
        }
        return $produtos;
    }

    public function criar(ProdutoDTO $produto) {
        $sql = "INSERT INTO produto (nome, descricao, preco, estoque, id_floricultura, imagem) 
                VALUES (:nome, :descricao, :preco, :estoque, :id_floricultura, :imagem)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':nome' => $produto->getNome(),
            ':descricao' => $produto->getDescricao(),
            ':preco' => $produto->getPreco(),
            ':estoque' => $produto->getEstoque(),
            ':id_floricultura' => $produto->getIdFloricultura(),
            ':imagem' => $produto->getImagem()
        ]);
    }

    public function atualizar(ProdutoDTO $produto) {
        $sql = "UPDATE produto SET nome = :nome, descricao = :descricao, 
                preco = :preco, estoque = :estoque 
                WHERE id_produto = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':nome' => $produto->getNome(),
            ':descricao' => $produto->getDescricao(),
            ':preco' => $produto->getPreco(),
            ':estoque' => $produto->getEstoque(),
            ':id' => $produto->getIdProduto()
        ]);
    }

    public function excluir($id) {
        $sql = "DELETE FROM produto WHERE id_produto = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function buscarPorNome($termo) {
        $sql = "SELECT p.*, f.nome as nome_floricultura 
                FROM produto p
                JOIN floricultura f ON p.id_floricultura = f.id_floricultura
                WHERE p.nome LIKE :termo OR p.descricao LIKE :termo2
                ORDER BY p.nome";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':termo' => "%{$termo}%",
            ':termo2' => "%{$termo}%"
        ]);
        $produtos = [];
        while ($row = $stmt->fetch()) {
            $produtos[] = $this->rowToDTO($row);
        }
        return $produtos;
    }

    private function rowToDTO($row) {
        $dto = new ProdutoDTO();
        $dto->setIdProduto($row['id_produto']);
        $dto->setNome($row['nome']);
        $dto->setDescricao($row['descricao'] ?? '');
        $dto->setPreco($row['preco']);
        $dto->setEstoque($row['estoque']);
        $dto->setIdFloricultura($row['id_floricultura']);
        $dto->setNomeFloricultura($row['nome_floricultura'] ?? '');
        $dto->setImagem($row['imagem'] ?? null);
        return $dto;
    }
}
?>