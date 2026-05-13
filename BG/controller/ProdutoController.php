<?php
require_once __DIR__ . '/../model/dao/ProdutoDAO.php';
require_once __DIR__ . '/../model/dao/dto/ProdutoDTO.php';

class ProdutoController {
    private $produtoDAO;

    public function __construct() {
        $this->produtoDAO = new ProdutoDAO();
    }

    public function listar() {
        return $this->produtoDAO->listarTodos();
    }

    public function listarPorFloricultura($idFloricultura) {
        return $this->produtoDAO->listarPorFloricultura($idFloricultura);
    }

    public function buscar($id) {
        return $this->produtoDAO->buscarPorId($id);
    }

    public function cadastrar($dados) {
        $produto = new ProdutoDTO();
        $produto->setNome($dados['nome']);
        $produto->setDescricao($dados['descricao'] ?? '');
        $produto->setPreco($dados['preco']);
        $produto->setEstoque($dados['estoque']);
        $produto->setIdFloricultura($dados['id_floricultura']);
        $produto->setImagem($dados['imagem'] ?? null);

        return $this->produtoDAO->criar($produto);
    }

    public function atualizar($dados) {
        $produto = new ProdutoDTO();
        $produto->setIdProduto($dados['id']);
        $produto->setNome($dados['nome']);
        $produto->setDescricao($dados['descricao'] ?? '');
        $produto->setPreco($dados['preco']);
        $produto->setEstoque($dados['estoque']);

        return $this->produtoDAO->atualizar($produto);
    }

    public function excluir($id) {
        return $this->produtoDAO->excluir($id);
    }
}
?>