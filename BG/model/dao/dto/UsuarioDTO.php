<?php
class UsuarioDTO {
    private $id_usuario;
    private $nome;
    private $telefone;
    private $id_perfil;
    private $nome_perfil;
    private $email;
    private $senha;
    private $ativo;
    private $id_floricultura;
    private $nome_floricultura;
    private $endereco;
    private $cidade;
    private $telefone_floricultura;

    // Getters e Setters
    public function getIdUsuario() { return $this->id_usuario; }
    public function setIdUsuario($id) { $this->id_usuario = $id; }

    public function getNome() { return $this->nome; }
    public function setNome($nome) { $this->nome = $nome; }

    public function getTelefone() { return $this->telefone; }
    public function setTelefone($tel) { $this->telefone = $tel; }

    public function getIdPerfil() { return $this->id_perfil; }
    public function setIdPerfil($id) { $this->id_perfil = $id; }

    public function getNomePerfil() { return $this->nome_perfil; }
    public function setNomePerfil($nome) { $this->nome_perfil = $nome; }

    public function getEmail() { return $this->email; }
    public function setEmail($email) { $this->email = $email; }

    public function getSenha() { return $this->senha; }
    public function setSenha($senha) { $this->senha = $senha; }

    public function getAtivo() { return $this->ativo; }
    public function setAtivo($ativo) { $this->ativo = $ativo; }

    public function getIdFloricultura() { return $this->id_floricultura; }
    public function setIdFloricultura($id) { $this->id_floricultura = $id; }

    public function getNomeFloricultura() { return $this->nome_floricultura; }
    public function setNomeFloricultura($nome) { $this->nome_floricultura = $nome; }

    public function getEndereco() { return $this->endereco; }
    public function setEndereco($end) { $this->endereco = $end; }

    public function getCidade() { return $this->cidade; }
    public function setCidade($cidade) { $this->cidade = $cidade; }

    public function getTelefoneFloricultura() { return $this->telefone_floricultura; }
    public function setTelefoneFloricultura($tel) { $this->telefone_floricultura = $tel; }
}
?>