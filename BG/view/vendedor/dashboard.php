<?php
session_start();
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/ProdutoController.php';
require_once __DIR__ . '/../../config/Database.php';

$auth = new AuthController();
if (!$auth->isLoggedIn() || !$auth->isVendedor()) {
    header('Location: ../login.php');
    exit();
}

$conn        = Database::getInstance()->getConnection();
$florId      = $_SESSION['floricultura_id'];
$usuarioId   = $_SESSION['usuario_id'];

// Processar ações POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_produto') {
        $imagem = null;
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                $nomeArquivo = uniqid('produto_') . '.' . $ext;
                if (move_uploaded_file($_FILES['imagem']['tmp_name'], __DIR__ . '/../../assets/uploads/' . $nomeArquivo)) {
                    $imagem = $nomeArquivo;
                }
            }
        }
        $produtoController = new ProdutoController();
        $produtoController->cadastrar([
            'nome'           => $_POST['nome'],
            'descricao'      => $_POST['descricao'],
            'preco'          => $_POST['preco'],
            'estoque'        => $_POST['estoque'],
            'id_floricultura'=> $florId,
            'imagem'         => $imagem
        ]);
        header('Location: dashboard.php?sucesso=produto');
        exit();
    }

    if ($action === 'editar_loja') {
        $conn->prepare("UPDATE floricultura SET nome=:nome, endereco=:endereco, cidade=:cidade, telefone=:telefone WHERE id_floricultura=:id")
             ->execute([':nome'=>$_POST['nome'],':endereco'=>$_POST['endereco'],':cidade'=>$_POST['cidade'],':telefone'=>$_POST['telefone'],':id'=>$florId]);
        header('Location: dashboard.php?sucesso=loja');
        exit();
    }

    if ($action === 'excluir_produto') {
        $idProduto = (int) $_POST['id_produto'];
        $conn->prepare("DELETE FROM produto WHERE id_produto=:id AND id_floricultura=:fid")
             ->execute([':id'=>$idProduto,':fid'=>$florId]);
        header('Location: dashboard.php?sucesso=excluido');
        exit();
    }

    if ($action === 'add_anuncio') {
        $imagem = null;
        if (isset($_FILES['imagem_anuncio']) && $_FILES['imagem_anuncio']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['imagem_anuncio']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                $nomeArquivo = uniqid('anuncio_') . '.' . $ext;
                if (move_uploaded_file($_FILES['imagem_anuncio']['tmp_name'], __DIR__ . '/../../assets/anuncios/' . $nomeArquivo)) {
                    $imagem = $nomeArquivo;
                }
            }
        }
        $conn->prepare("INSERT INTO anuncio (titulo, descricao, preco, imagem, link, data_expiracao, id_floricultura) VALUES (:titulo,:descricao,:preco,:imagem,:link,:expiracao,:fid)")
             ->execute([':titulo'=>$_POST['titulo'],':descricao'=>$_POST['descricao'],':preco'=>$_POST['preco']?:null,':imagem'=>$imagem,':link'=>$_POST['link']?:null,':expiracao'=>$_POST['data_expiracao']?:null,':fid'=>$florId]);
        header('Location: dashboard.php?aba=anuncios&sucesso=anuncio');
        exit();
    }

    if ($action === 'excluir_anuncio') {
        $conn->prepare("DELETE FROM anuncio WHERE id_anuncio=:id AND id_floricultura=:fid")
             ->execute([':id'=>(int)$_POST['id_anuncio'],':fid'=>$florId]);
        header('Location: dashboard.php?aba=anuncios&sucesso=anuncio_excluido');
        exit();
    }
}

// Dados da loja
$stmt = $conn->prepare("SELECT * FROM floricultura WHERE id_floricultura = :id");
$stmt->execute([':id' => $florId]);
$loja = $stmt->fetch();

// Produtos
$produtoDAO   = new ProdutoDAO();
$meusProdutos = $produtoDAO->listarPorFloricultura($florId);

// Estatísticas do mês atual
$mes = date('m');
$ano = date('Y');

$stmt = $conn->prepare("SELECT COALESCE(SUM(ip.quantidade * ip.preco_venda),0) as fat
                         FROM item_pedido ip
                         JOIN produto pr ON ip.id_produto = pr.id_produto
                         JOIN pedido p ON ip.id_pedido = p.id_pedido
                         WHERE pr.id_floricultura=:id AND MONTH(p.data_pedido)=:mes AND YEAR(p.data_pedido)=:ano AND p.status!='cancelado'");
$stmt->execute([':id'=>$florId,':mes'=>$mes,':ano'=>$ano]);
$faturamentoMes = (float) $stmt->fetch()['fat'];

$stmt = $conn->prepare("SELECT COUNT(DISTINCT p.id_pedido) as total
                         FROM pedido p
                         JOIN item_pedido ip ON p.id_pedido=ip.id_pedido
                         JOIN produto pr ON ip.id_produto=pr.id_produto
                         WHERE pr.id_floricultura=:id AND MONTH(p.data_pedido)=:mes AND YEAR(p.data_pedido)=:ano");
$stmt->execute([':id'=>$florId,':mes'=>$mes,':ano'=>$ano]);
$pedidosMes = (int) $stmt->fetch()['total'];

$stmt = $conn->prepare("SELECT COALESCE(SUM(ip.quantidade),0) as total
                         FROM item_pedido ip
                         JOIN produto pr ON ip.id_produto=pr.id_produto
                         JOIN pedido p ON ip.id_pedido=p.id_pedido
                         WHERE pr.id_floricultura=:id AND MONTH(p.data_pedido)=:mes AND YEAR(p.data_pedido)=:ano AND p.status!='cancelado'");
$stmt->execute([':id'=>$florId,':mes'=>$mes,':ano'=>$ano]);
$itensMes = (int) $stmt->fetch()['total'];

// Histórico 6 meses
$historico = [];
for ($i = 5; $i >= 0; $i--) {
    $m = date('m', strtotime("-$i months"));
    $a = date('Y', strtotime("-$i months"));
    $stmt = $conn->prepare("SELECT COALESCE(SUM(ip.quantidade*ip.preco_venda),0) as fat
                             FROM item_pedido ip JOIN produto pr ON ip.id_produto=pr.id_produto
                             JOIN pedido p ON ip.id_pedido=p.id_pedido
                             WHERE pr.id_floricultura=:id AND MONTH(p.data_pedido)=:mes AND YEAR(p.data_pedido)=:ano AND p.status!='cancelado'");
    $stmt->execute([':id'=>$florId,':mes'=>$m,':ano'=>$a]);
    $historico[] = ['label' => date('M/y', strtotime("$a-$m-01")), 'valor' => (float)$stmt->fetch()['fat']];
}

// Aba ativa
$aba = $_GET['aba'] ?? 'visao-geral';

// Anúncios do vendedor
$meusAnuncios = $conn->prepare("SELECT * FROM anuncio WHERE id_floricultura=:id ORDER BY data_criacao DESC");
$meusAnuncios->execute([':id'=>$florId]);
$meusAnuncios = $meusAnuncios->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌸 FLORI | Painel Vendedor</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../assets/estilo.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="vendedor-header">
    <h1><i class="fas fa-store"></i> <?= htmlspecialchars($loja['nome'] ?? 'Minha Loja') ?></h1>
    <div class="vendedor-nav">
        <span><i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
        <a href="../index.php"><i class="fas fa-eye"></i> Ver Loja</a>
        <a href="../../controller/AuthController.php?action=logout"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>
</div>

<!-- Abas de navegação -->
<div class="vendedor-tabs">
    <a href="?aba=visao-geral" class="tab <?= $aba==='visao-geral'?'tab-ativa':'' ?>"><i class="fas fa-chart-pie"></i> Visão Geral</a>
    <a href="?aba=produtos" class="tab <?= $aba==='produtos'?'tab-ativa':'' ?>"><i class="fas fa-box-open"></i> Produtos</a>
    <a href="?aba=anuncios" class="tab <?= $aba==='anuncios'?'tab-ativa':'' ?>"><i class="fas fa-bullhorn"></i> Anúncios</a>
    <a href="?aba=minha-loja" class="tab <?= $aba==='minha-loja'?'tab-ativa':'' ?>"><i class="fas fa-store"></i> Minha Loja</a>
</div>

<div class="vendedor-dashboard">

    <?php if (isset($_GET['sucesso'])): ?>
        <div class="alert success">
            <i class="fas fa-check-circle"></i>
            <?php
                $msgs = ['produto'=>'Produto adicionado!','loja'=>'Loja atualizada!','excluido'=>'Produto excluído!','anuncio'=>'Anúncio publicado!','anuncio_excluido'=>'Anúncio excluído!'];
                echo $msgs[$_GET['sucesso']] ?? 'Operação realizada!';
            ?>
        </div>
    <?php endif; ?>

    <!-- ABA: VISÃO GERAL -->
    <?php if ($aba === 'visao-geral'): ?>

        <div class="stats-cards">
            <div class="stat-card stat-card-destaque">
                <i class="fas fa-dollar-sign"></i>
                <h3>R$ <?= number_format($faturamentoMes, 2, ',', '.') ?></h3>
                <p>Faturamento do Mês</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-shopping-bag"></i>
                <h3><?= $pedidosMes ?></h3>
                <p>Pedidos no Mês</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-box-open"></i>
                <h3><?= $itensMes ?></h3>
                <p>Itens Vendidos</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-tags"></i>
                <h3><?= count($meusProdutos) ?></h3>
                <p>Produtos Cadastrados</p>
            </div>
        </div>

        <h2><i class="fas fa-chart-bar"></i> Faturamento — Últimos 6 Meses</h2>
        <div class="table-wrapper" style="padding:1.5rem;">
            <canvas id="grafico" height="100"></canvas>
        </div>

    <!-- ABA: PRODUTOS -->
    <?php elseif ($aba === 'produtos'): ?>

        <h2><i class="fas fa-plus-circle"></i> Adicionar Produto</h2>
        <form method="POST" enctype="multipart/form-data" class="form-produto">
            <input type="hidden" name="action" value="add_produto">
            <div class="form-row">
                <div class="form-group">
                    <label>Nome do Produto</label>
                    <input type="text" name="nome" required placeholder="Ex: Buquê de Rosas">
                </div>
                <div class="form-group">
                    <label>Imagem</label>
                    <input type="file" name="imagem" accept="image/jpg,image/jpeg,image/png,image/webp">
                </div>
            </div>
            <div class="form-group">
                <label>Descrição</label>
                <textarea name="descricao" rows="2" placeholder="Descreva o produto..."></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Preço (R$)</label>
                    <input type="number" name="preco" step="0.01" min="0" required placeholder="0,00">
                </div>
                <div class="form-group">
                    <label>Estoque</label>
                    <input type="number" name="estoque" min="0" required placeholder="0">
                </div>
            </div>
            <button type="submit" class="btn-primary" style="width:auto;padding:0.8rem 2rem;">
                <i class="fas fa-plus"></i> Adicionar Produto
            </button>
        </form>

        <h2><i class="fas fa-box-open"></i> Meus Produtos (<?= count($meusProdutos) ?>)</h2>

        <?php if (empty($meusProdutos)): ?>
            <div class="mensagem-vazio"><i class="fas fa-box-open" style="font-size:3rem;"></i><p>Nenhum produto cadastrado ainda.</p></div>
        <?php else: ?>
            <div class="produtos-grid">
                <?php foreach ($meusProdutos as $produto): ?>
                <div class="produto-card-vendedor">
                    <div class="produto-card-img">
                        <?php if ($produto->getImagem()): ?>
                            <img src="../../assets/uploads/<?= htmlspecialchars($produto->getImagem()) ?>" alt="<?= htmlspecialchars($produto->getNome()) ?>">
                        <?php else: ?>
                            <span class="sem-imagem">🌸</span>
                        <?php endif; ?>
                    </div>
                    <div class="produto-card-info">
                        <h3><?= htmlspecialchars($produto->getNome()) ?></h3>
                        <p class="descricao"><?= htmlspecialchars($produto->getDescricao()) ?></p>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:0.5rem;">
                            <span class="preco-tag">R$ <?= number_format($produto->getPreco(), 2, ',', '.') ?></span>
                            <span class="estoque-tag <?= $produto->getEstoque()>10?'estoque-ok':($produto->getEstoque()>0?'estoque-baixo':'estoque-zero') ?>">
                                <?= $produto->getEstoque() ?> unid.
                            </span>
                        </div>
                        <form method="POST" style="margin-top:0.8rem;" onsubmit="return confirm('Excluir produto?')">
                            <input type="hidden" name="action" value="excluir_produto">
                            <input type="hidden" name="id_produto" value="<?= $produto->getIdProduto() ?>">
                            <button type="submit" class="btn-acao btn-excluir" style="width:100%;justify-content:center;">
                                <i class="fas fa-trash"></i> Excluir
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <!-- ABA: ANÚCIOS -->
    <?php elseif ($aba === 'anuncios'): ?>

        <h2><i class="fas fa-plus-circle"></i> Criar Novo Anúncio</h2>
        <form method="POST" enctype="multipart/form-data" class="form-produto">
            <input type="hidden" name="action" value="add_anuncio">
            <div class="form-group">
                <label>Título do Anúncio</label>
                <input type="text" name="titulo" required placeholder="Ex: Promoção de Rosas — 50% OFF">
            </div>
            <div class="form-group">
                <label>Descrição</label>
                <textarea name="descricao" rows="3" placeholder="Descreva sua oferta..."></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Preço (opcional)</label>
                    <input type="number" name="preco" step="0.01" min="0" placeholder="0,00">
                </div>
                <div class="form-group">
                    <label>Validade do Anúncio</label>
                    <input type="date" name="data_expiracao" min="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Imagem</label>
                    <input type="file" name="imagem_anuncio" accept="image/jpg,image/jpeg,image/png,image/webp">
                </div>
                <div class="form-group">
                    <label>Link (opcional)</label>
                    <input type="url" name="link" placeholder="https://...">
                </div>
            </div>
            <button type="submit" class="btn-primary" style="width:auto;padding:0.8rem 2rem;">
                <i class="fas fa-bullhorn"></i> Publicar Anúncio
            </button>
        </form>

        <h2><i class="fas fa-list"></i> Meus Anúncios (<?= count($meusAnuncios) ?>)</h2>

        <?php if (empty($meusAnuncios)): ?>
            <div class="mensagem-vazio"><i class="fas fa-bullhorn" style="font-size:3rem;"></i><p>Nenhum anúncio publicado ainda.</p></div>
        <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Título</th><th>Preço</th><th>Validade</th><th>Status</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($meusAnuncios as $an): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($an['titulo']) ?></strong></td>
                    <td><?= $an['preco'] ? 'R$ '.number_format($an['preco'],2,',','.') : '-' ?></td>
                    <td><?= $an['data_expiracao'] ? date('d/m/Y', strtotime($an['data_expiracao'])) : 'Sem validade' ?></td>
                    <td>
                        <?php if (!$an['ativo']): ?>
                            <span class="status-pill pill-inativo">Inativo</span>
                        <?php elseif ($an['data_expiracao'] && $an['data_expiracao'] < date('Y-m-d')): ?>
                            <span class="status-pill pill-inativo">Expirado</span>
                        <?php else: ?>
                            <span class="status-pill pill-ativo"><?= $an['destaque'] ? '⭐ Destaque' : 'Ativo' ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Excluir anúncio?')">
                            <input type="hidden" name="action" value="excluir_anuncio">
                            <input type="hidden" name="id_anuncio" value="<?= $an['id_anuncio'] ?>">
                            <button type="submit" class="btn-acao btn-excluir"><i class="fas fa-trash"></i> Excluir</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <div style="margin-top:1rem;">
            <a href="../anuncios.php" class="btn-primary" style="width:auto;padding:0.8rem 2rem;display:inline-flex;">
                <i class="fas fa-eye"></i> Ver Página de Anúncios
            </a>
        </div>

    <!-- ABA: MINHA LOJA -->
    <?php elseif ($aba === 'minha-loja'): ?>

        <h2><i class="fas fa-store"></i> Informações da Loja</h2>
        <form method="POST" class="form-produto">
            <input type="hidden" name="action" value="editar_loja">
            <div class="form-group">
                <label><i class="fas fa-store"></i> Nome da Loja</label>
                <input type="text" name="nome" value="<?= htmlspecialchars($loja['nome'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-map-marker-alt"></i> Endereço</label>
                <input type="text" name="endereco" value="<?= htmlspecialchars($loja['endereco'] ?? '') ?>" placeholder="Rua, número...">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fas fa-city"></i> Cidade</label>
                    <input type="text" name="cidade" value="<?= htmlspecialchars($loja['cidade'] ?? '') ?>" placeholder="Sua cidade">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-phone"></i> Telefone</label>
                    <input type="text" name="telefone" value="<?= htmlspecialchars($loja['telefone'] ?? '') ?>" placeholder="(00) 00000-0000">
                </div>
            </div>
            <button type="submit" class="btn-primary" style="width:auto;padding:0.8rem 2rem;">
                <i class="fas fa-save"></i> Salvar Alterações
            </button>
        </form>

    <?php endif; ?>

</div>

<?php if ($aba === 'visao-geral'): ?>
<script>
new Chart(document.getElementById('grafico').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($historico, 'label')) ?>,
        datasets: [{
            label: 'Faturamento (R$)',
            data: <?= json_encode(array_column($historico, 'valor')) ?>,
            backgroundColor: 'rgba(255,77,109,0.2)',
            borderColor: 'rgba(255,77,109,1)',
            borderWidth: 2,
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { callback: v => 'R$ ' + v.toLocaleString('pt-BR') } } }
    }
});
</script>
<?php endif; ?>

</body>
</html>
