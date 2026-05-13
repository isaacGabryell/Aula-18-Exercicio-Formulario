<?php
class PedidoDTO {
    private $id_pedido;
    private $data_pedido;
    private $valor_total;
    private $status;
    private $id_usuario;
    private $nome_cliente;
    private $itens = [];

    public function getIdPedido() { return $this->id_pedido; }
    public function setIdPedido($id) { $this->id_pedido = $id; }

    public function getDataPedido() { return $this->data_pedido; }
    public function setDataPedido($data) { $this->data_pedido = $data; }

    public function getValorTotal() { return $this->valor_total; }
    public function setValorTotal($valor) { $this->valor_total = $valor; }

    public function getStatus() { return $this->status; }
    public function setStatus($status) { $this->status = $status; }

    public function getIdUsuario() { return $this->id_usuario; }
    public function setIdUsuario($id) { $this->id_usuario = $id; }

    public function getNomeCliente() { return $this->nome_cliente; }
    public function setNomeCliente($nome) { $this->nome_cliente = $nome; }

    public function getItens() { return $this->itens; }
    public function setItens($itens) { $this->itens = $itens; }
    public function adicionarItem($item) { $this->itens[] = $item; }
}

class ItemPedidoDTO {
    private $id_item;
    private $quantidade;
    private $preco_venda;
    private $id_pedido;
    private $id_produto;
    private $nome_produto;

    public function getIdItem() { return $this->id_item; }
    public function setIdItem($id) { $this->id_item = $id; }

    public function getQuantidade() { return $this->quantidade; }
    public function setQuantidade($qtd) { $this->quantidade = $qtd; }

    public function getPrecoVenda() { return $this->preco_venda; }
    public function setPrecoVenda($preco) { $this->preco_venda = $preco; }

    public function getIdPedido() { return $this->id_pedido; }
    public function setIdPedido($id) { $this->id_pedido = $id; }

    public function getIdProduto() { return $this->id_produto; }
    public function setIdProduto($id) { $this->id_produto = $id; }

    public function getNomeProduto() { return $this->nome_produto; }
    public function setNomeProduto($nome) { $this->nome_produto = $nome; }
}
?>