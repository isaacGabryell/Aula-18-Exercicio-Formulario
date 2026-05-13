<?php
class ProdutoDTO {
    private $id_produto;
    private $nome;
    private $descricao;
    private $preco;
    private $estoque;
    private $id_floricultura;
    private $nome_floricultura;
    private $imagem;

    public function getIdProduto() { return $this->id_produto; }
    public function setIdProduto($id) { $this->id_produto = $id; }

    public function getNome() { return $this->nome; }
    public function setNome($nome) { $this->nome = $nome; }

    public function getDescricao() { return $this->descricao; }
    public function setDescricao($desc) { $this->descricao = $desc; }

    public function getPreco() { return $this->preco; }
    public function setPreco($preco) { $this->preco = $preco; }

    public function getEstoque() { return $this->estoque; }
    public function setEstoque($estoque) { $this->estoque = $estoque; }

    public function getIdFloricultura() { return $this->id_floricultura; }
    public function setIdFloricultura($id) { $this->id_floricultura = $id; }

    public function getNomeFloricultura() { return $this->nome_floricultura; }
    public function setNomeFloricultura($nome) { $this->nome_floricultura = $nome; }

    public function getImagem() { return $this->imagem; }
    public function setImagem($imagem) { $this->imagem = $imagem; }
}
?>