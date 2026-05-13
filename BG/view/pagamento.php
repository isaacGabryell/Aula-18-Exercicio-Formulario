<?php
session_start();
require_once __DIR__ . '/../controller/AuthController.php';
require_once __DIR__ . '/../controller/ProdutoController.php';

$auth = new AuthController();
if (!$auth->isLoggedIn()) { header('Location: login.php'); exit(); }
if (empty($_SESSION['carrinho'])) { header('Location: carrinho.php'); exit(); }

$produtoDAO = new ProdutoDAO();
$itens = [];
$total = 0;
foreach ($_SESSION['carrinho'] as $id => $qtd) {
    $produto = $produtoDAO->buscarPorId($id);
    if (!$produto) continue;
    $subtotal = $produto->getPreco() * $qtd;
    $total   += $subtotal;
    $itens[]  = ['produto' => $produto, 'qtd' => $qtd, 'subtotal' => $subtotal];
}
if (empty($itens)) { header('Location: carrinho.php'); exit(); }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌸 FLORI | Pagamento</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/estilo.css">
    <style>
        .pag-wrapper { max-width: 1000px; margin: 2rem auto; padding: 0 1.5rem; display: grid; grid-template-columns: 1fr 340px; gap: 2rem; }
        .pag-titulo { font-family: 'Playfair Display', serif; font-size: 1.8rem; color: var(--cinza-900); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.8rem; }
        .pag-titulo i { color: var(--rosa-500); }
        .metodos { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.8rem; margin-bottom: 2rem; }
        .metodo-btn { background: var(--branco); border: 2px solid var(--cinza-200); border-radius: 14px; padding: 1rem 0.5rem; text-align: center; cursor: pointer; transition: all 0.25s; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; }
        .metodo-btn i { font-size: 1.6rem; color: var(--cinza-400); transition: color 0.25s; }
        .metodo-btn span { font-size: 0.78rem; font-weight: 600; color: var(--cinza-500); }
        .metodo-btn:hover, .metodo-btn.ativo { border-color: var(--rosa-400); background: var(--rosa-50); }
        .metodo-btn.ativo i { color: var(--rosa-500); }
        .metodo-btn.ativo span { color: var(--rosa-600); }
        .form-pag { background: var(--branco); border-radius: 20px; padding: 2rem; box-shadow: var(--sombra-md); border: 1px solid var(--rosa-100); }
        .form-pag h3 { font-size: 1.1rem; font-weight: 600; color: var(--cinza-800); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; }
        .form-pag h3 i { color: var(--rosa-500); }
        .cartao-visual { background: linear-gradient(135deg, var(--rosa-500), var(--rosa-800)); border-radius: 18px; padding: 1.8rem; color: white; margin-bottom: 1.5rem; position: relative; overflow: hidden; min-height: 180px; }
        .cartao-visual::before { content: ''; position: absolute; top: -40px; right: -40px; width: 180px; height: 180px; background: rgba(255,255,255,0.08); border-radius: 50%; }
        .cartao-visual::after { content: ''; position: absolute; bottom: -60px; left: -20px; width: 200px; height: 200px; background: rgba(255,255,255,0.05); border-radius: 50%; }
        .cartao-chip { width: 45px; height: 35px; background: linear-gradient(135deg, #f6d365, #fda085); border-radius: 6px; margin-bottom: 1.5rem; }
        .cartao-numero { font-size: 1.3rem; letter-spacing: 4px; font-weight: 300; margin-bottom: 1.2rem; font-family: monospace; }
        .cartao-bottom { display: flex; justify-content: space-between; align-items: flex-end; }
        .cartao-bottom label { font-size: 0.65rem; opacity: 0.7; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 0.2rem; }
        .cartao-bottom span { font-size: 0.95rem; font-weight: 500; }
        .pix-box { text-align: center; padding: 2rem; }
        .pix-qr { width: 180px; height: 180px; background: var(--cinza-100); border-radius: 16px; margin: 1rem auto; display: flex; align-items: center; justify-content: center; font-size: 5rem; border: 3px dashed var(--cinza-300); }
        .pix-chave { background: var(--cinza-50); border: 2px solid var(--cinza-200); border-radius: 12px; padding: 1rem; font-family: monospace; font-size: 0.9rem; color: var(--cinza-700); margin: 1rem 0; word-break: break-all; }
        .boleto-box { text-align: center; padding: 1.5rem; }
        .boleto-codigo { background: var(--cinza-50); border: 2px solid var(--cinza-200); border-radius: 12px; padding: 1rem; font-family: monospace; font-size: 0.85rem; color: var(--cinza-700); margin: 1rem 0; word-break: break-all; letter-spacing: 2px; }
        .debito-info { background: linear-gradient(135deg, #dbeafe, #ede9fe); border-radius: 14px; padding: 1.5rem; text-align: center; }
        .debito-info i { font-size: 3rem; color: #3730a3; margin-bottom: 0.8rem; display: block; }
        /* Resumo */
        .resumo-pag { background: var(--branco); border-radius: 20px; box-shadow: var(--sombra-md); padding: 1.8rem; border: 1px solid var(--rosa-100); position: sticky; top: 100px; height: fit-content; }
        .resumo-pag h2 { font-size: 1.1rem; font-weight: 700; color: var(--cinza-900); margin-bottom: 1.2rem; padding-bottom: 0.8rem; border-bottom: 2px solid var(--rosa-100); }
        .resumo-item { display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--cinza-600); margin-bottom: 0.6rem; }
        .resumo-item span:first-child { max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .resumo-total-pag { display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 700; color: var(--cinza-900); margin-top: 1rem; padding-top: 1rem; border-top: 2px solid var(--rosa-100); }
        .resumo-total-pag span:last-child { color: var(--rosa-600); }
        .btn-pagar { width: 100%; padding: 1.1rem; background: linear-gradient(135deg, var(--rosa-400), var(--rosa-600)); color: white; border: none; border-radius: 14px; font-size: 1.05rem; font-weight: 700; font-family: 'Poppins', sans-serif; cursor: pointer; margin-top: 1.2rem; display: flex; align-items: center; justify-content: center; gap: 0.6rem; transition: all 0.3s; box-shadow: 0 4px 15px rgba(255,77,109,0.3); }
        .btn-pagar:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(255,77,109,0.5); }
        .seguro-info { display: flex; align-items: center; justify-content: center; gap: 0.4rem; font-size: 0.78rem; color: var(--cinza-400); margin-top: 0.8rem; }
        .painel-metodo { display: none; }
        .painel-metodo.ativo { display: block; }
        @media (max-width: 768px) { .pag-wrapper { grid-template-columns: 1fr; } .metodos { grid-template-columns: repeat(2, 1fr); } }
    </style>
</head>
<body>

<header>
    <div class="container-header">
        <a href="index.php" class="logo"><i class="fas fa-seedling"></i><span>FLORI</span></a>
        <div class="icons">
            <a href="carrinho.php" class="icon-link"><i class="fas fa-arrow-left"></i> Voltar</a>
        </div>
    </div>
</header>

<div class="pag-wrapper">
    <div>
        <h1 class="pag-titulo"><i class="fas fa-lock"></i> Pagamento Seguro</h1>

        <!-- Métodos -->
        <div class="metodos">
            <div class="metodo-btn ativo" onclick="selecionarMetodo('credito', this)">
                <i class="fas fa-credit-card"></i>
                <span>Crédito</span>
            </div>
            <div class="metodo-btn" onclick="selecionarMetodo('debito', this)">
                <i class="fas fa-money-check"></i>
                <span>Débito</span>
            </div>
            <div class="metodo-btn" onclick="selecionarMetodo('pix', this)">
                <i class="fas fa-qrcode"></i>
                <span>PIX</span>
            </div>
            <div class="metodo-btn" onclick="selecionarMetodo('boleto', this)">
                <i class="fas fa-barcode"></i>
                <span>Boleto</span>
            </div>
        </div>

        <form method="POST" action="finalizar_pedido.php" id="formPagamento">
            <input type="hidden" name="metodo" id="metodoInput" value="credito">

            <!-- Cartão de Crédito -->
            <div class="form-pag painel-metodo ativo" id="painel-credito">
                <h3><i class="fas fa-credit-card"></i> Cartão de Crédito</h3>
                <div class="cartao-visual">
                    <div class="cartao-chip"></div>
                    <div class="cartao-numero" id="previewNumero">•••• •••• •••• ••••</div>
                    <div class="cartao-bottom">
                        <div><label>Nome</label><span id="previewNome">SEU NOME</span></div>
                        <div><label>Validade</label><span id="previewValidade">MM/AA</span></div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Número do Cartão</label>
                    <input type="text" name="numero_cartao" placeholder="0000 0000 0000 0000" maxlength="19" oninput="formatarCartao(this)" onkeyup="document.getElementById('previewNumero').textContent = this.value || '•••• •••• •••• ••••'">
                </div>
                <div class="form-group">
                    <label>Nome no Cartão</label>
                    <input type="text" name="nome_cartao" placeholder="Como está no cartão" oninput="document.getElementById('previewNome').textContent = this.value.toUpperCase() || 'SEU NOME'">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Validade</label>
                        <input type="text" name="validade" placeholder="MM/AA" maxlength="5" oninput="formatarValidade(this)" onkeyup="document.getElementById('previewValidade').textContent = this.value || 'MM/AA'">
                    </div>
                    <div class="form-group">
                        <label>CVV</label>
                        <input type="text" name="cvv" placeholder="•••" maxlength="3">
                    </div>
                </div>
                <div class="form-group">
                    <label>Parcelas</label>
                    <select name="parcelas">
                        <?php for ($i = 1; $i <= 12; $i++): $val = $total / $i; ?>
                        <option value="<?= $i ?>"><?= $i ?>x de R$ <?= number_format($val, 2, ',', '.') ?><?= $i === 1 ? ' (sem juros)' : ($i <= 3 ? ' (sem juros)' : '') ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <!-- Débito -->
            <div class="form-pag painel-metodo" id="painel-debito">
                <h3><i class="fas fa-money-check"></i> Cartão de Débito</h3>
                <div class="debito-info">
                    <i class="fas fa-mobile-alt"></i>
                    <p style="font-weight:600;color:#3730a3;margin-bottom:0.5rem;">Autenticação pelo App do Banco</p>
                    <p style="font-size:0.88rem;color:#6b7280;">Você será redirecionado para autenticar o pagamento no app do seu banco.</p>
                </div>
                <div class="form-group" style="margin-top:1.5rem;">
                    <label>Número do Cartão</label>
                    <input type="text" name="numero_debito" placeholder="0000 0000 0000 0000" maxlength="19" oninput="formatarCartao(this)">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Validade</label>
                        <input type="text" name="validade_debito" placeholder="MM/AA" maxlength="5" oninput="formatarValidade(this)">
                    </div>
                    <div class="form-group">
                        <label>CVV</label>
                        <input type="text" name="cvv_debito" placeholder="•••" maxlength="3">
                    </div>
                </div>
            </div>

            <!-- PIX -->
            <div class="form-pag painel-metodo" id="painel-pix">
                <h3><i class="fas fa-qrcode"></i> Pagamento via PIX</h3>
                <div class="pix-box">
                    <p style="color:var(--cinza-600);font-size:0.9rem;">Escaneie o QR Code ou copie a chave PIX</p>
                    <div class="pix-qr">📱</div>
                    <div class="pix-chave">flori@pagamentos.com.br</div>
                    <button type="button" onclick="copiarPix()" style="background:var(--rosa-50);border:2px solid var(--rosa-200);color:var(--rosa-600);padding:0.6rem 1.5rem;border-radius:50px;cursor:pointer;font-family:Poppins;font-weight:600;font-size:0.9rem;">
                        <i class="fas fa-copy"></i> Copiar Chave PIX
                    </button>
                    <p style="margin-top:1rem;font-size:0.82rem;color:var(--cinza-400);">O pagamento é confirmado em até 1 minuto</p>
                </div>
            </div>

            <!-- Boleto -->
            <div class="form-pag painel-metodo" id="painel-boleto">
                <h3><i class="fas fa-barcode"></i> Boleto Bancário</h3>
                <div class="boleto-box">
                    <i class="fas fa-file-invoice" style="font-size:4rem;color:var(--cinza-300);margin-bottom:1rem;display:block;"></i>
                    <p style="color:var(--cinza-600);font-size:0.9rem;">Vencimento em <strong>3 dias úteis</strong></p>
                    <div class="boleto-codigo">1234 5678 9012 3456 7890 1234 5678 9012 3456 7890 12</div>
                    <button type="button" onclick="copiarBoleto()" style="background:var(--cinza-100);border:2px solid var(--cinza-200);color:var(--cinza-700);padding:0.6rem 1.5rem;border-radius:50px;cursor:pointer;font-family:Poppins;font-weight:600;font-size:0.9rem;">
                        <i class="fas fa-copy"></i> Copiar Código
                    </button>
                    <p style="margin-top:1rem;font-size:0.82rem;color:var(--cinza-400);">Após o pagamento, a confirmação pode levar até 2 dias úteis</p>
                </div>
            </div>

            <button type="submit" class="btn-pagar" style="margin-top:1.5rem;">
                <i class="fas fa-lock"></i> Confirmar Pagamento — R$ <?= number_format($total, 2, ',', '.') ?>
            </button>
            <p class="seguro-info"><i class="fas fa-shield-alt"></i> Pagamento 100% seguro e criptografado</p>
        </form>
    </div>

    <!-- Resumo -->
    <div>
        <div class="resumo-pag">
            <h2><i class="fas fa-receipt" style="color:var(--rosa-500);margin-right:0.5rem;"></i> Resumo</h2>
            <?php foreach ($itens as $item): ?>
            <div class="resumo-item">
                <span><?= htmlspecialchars($item['produto']->getNome()) ?> x<?= $item['qtd'] ?></span>
                <span>R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></span>
            </div>
            <?php endforeach; ?>
            <div class="resumo-item" style="color:#065f46;font-weight:600;">
                <span><i class="fas fa-truck"></i> Frete</span>
                <span>Grátis</span>
            </div>
            <div class="resumo-total-pag">
                <span>Total</span>
                <span>R$ <?= number_format($total, 2, ',', '.') ?></span>
            </div>
        </div>
    </div>
</div>

<script>
function selecionarMetodo(metodo, el) {
    document.querySelectorAll('.metodo-btn').forEach(b => b.classList.remove('ativo'));
    document.querySelectorAll('.painel-metodo').forEach(p => p.classList.remove('ativo'));
    el.classList.add('ativo');
    document.getElementById('painel-' + metodo).classList.add('ativo');
    document.getElementById('metodoInput').value = metodo;
}
function formatarCartao(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 16);
    input.value = v.replace(/(.{4})/g, '$1 ').trim();
}
function formatarValidade(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 4);
    if (v.length >= 2) v = v.substring(0,2) + '/' + v.substring(2);
    input.value = v;
}
function copiarPix() {
    navigator.clipboard.writeText('flori@pagamentos.com.br');
    alert('Chave PIX copiada!');
}
function copiarBoleto() {
    navigator.clipboard.writeText('1234567890123456789012345678901234567890 12');
    alert('Código do boleto copiado!');
}
</script>
</body>
</html>
