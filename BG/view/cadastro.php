<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌸 FLORI | Cadastro</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            min-height: 100vh;
            display: flex;
            font-family: 'Poppins', sans-serif;
            background: #fff5f7;
        }

        /* LADO ESQUERDO */
        .cad-left {
            flex: 1;
            background: linear-gradient(145deg, #ff8fa3 0%, #ff4d6d 50%, #c9184a 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }
        .cad-left::before {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
            top: -150px; left: -150px;
        }
        .cad-left::after {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            background: rgba(255,255,255,0.04);
            border-radius: 50%;
            bottom: -100px; right: -100px;
        }
        .left-content { position: relative; z-index: 1; text-align: center; color: white; }
        .left-logo {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        .left-tagline { font-size: 1.1rem; opacity: 0.88; margin-bottom: 2.5rem; font-weight: 300; }

        .flores-deco {
            display: flex; gap: 1.5rem; justify-content: center;
            font-size: 3rem; margin-bottom: 2.5rem;
            animation: floatFlores 4s ease-in-out infinite;
        }
        @keyframes floatFlores {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }

        .tipo-cards { display: flex; flex-direction: column; gap: 1rem; width: 100%; max-width: 340px; }
        .tipo-card {
            display: flex; align-items: center; gap: 1rem;
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(10px);
            padding: 1.1rem 1.4rem;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
        }
        .tipo-card-icon { font-size: 1.8rem; }
        .tipo-card-text strong { display: block; font-size: 0.95rem; font-weight: 600; }
        .tipo-card-text span { font-size: 0.78rem; opacity: 0.8; }

        /* LADO DIREITO */
        .cad-right {
            width: 520px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 2.5rem;
            background: white;
            box-shadow: -10px 0 40px rgba(0,0,0,0.06);
            overflow-y: auto;
        }

        .cad-form-wrapper { width: 100%; max-width: 420px; }

        .form-header { margin-bottom: 2rem; }
        .form-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: #1a1a2e;
            margin-bottom: 0.4rem;
        }
        .form-header p { color: #9e9e9e; font-size: 0.9rem; }

        /* TIPO SELECTOR */
        .tipo-selector { display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem; margin-bottom: 1.5rem; }
        .tipo-opt {
            border: 2px solid #eeeeee;
            border-radius: 14px;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s;
            background: #fafafa;
        }
        .tipo-opt:hover { border-color: #ffb3c1; background: #fff5f7; }
        .tipo-opt.selected { border-color: #ff4d6d; background: #fff5f7; box-shadow: 0 0 0 3px rgba(255,77,109,0.1); }
        .tipo-opt i { font-size: 1.6rem; display: block; margin-bottom: 0.4rem; color: #bdbdbd; transition: color 0.25s; }
        .tipo-opt.selected i { color: #ff4d6d; }
        .tipo-opt span { font-size: 0.85rem; font-weight: 600; color: #616161; }
        .tipo-opt.selected span { color: #ff4d6d; }
        .tipo-opt input { display: none; }

        .input-group { margin-bottom: 1.1rem; }
        .input-group label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: #424242;
            margin-bottom: 0.45rem;
        }
        .input-wrap { position: relative; }
        .input-wrap i.icon-left {
            position: absolute; left: 1.1rem; top: 50%;
            transform: translateY(-50%);
            color: #bdbdbd; font-size: 0.9rem; transition: color 0.2s;
            pointer-events: none;
        }
        .input-wrap:focus-within i.icon-left { color: #ff4d6d; }
        .input-wrap input,
        .input-wrap select {
            width: 100%;
            padding: 0.9rem 1rem 0.9rem 2.7rem;
            border: 2px solid #eeeeee;
            border-radius: 12px;
            font-size: 0.92rem;
            font-family: 'Poppins', sans-serif;
            color: #212121;
            background: #fafafa;
            outline: none;
            transition: all 0.25s;
            appearance: none;
        }
        .input-wrap input:focus,
        .input-wrap select:focus {
            border-color: #ff4d6d;
            background: white;
            box-shadow: 0 0 0 4px rgba(255,77,109,0.08);
        }
        .toggle-senha {
            position: absolute; right: 1rem; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: #bdbdbd; font-size: 0.9rem; padding: 0; transition: color 0.2s;
        }
        .toggle-senha:hover { color: #ff4d6d; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem; }

        .floricultura-group { display: none; }
        .floricultura-group.show { display: block; }

        .erro-msg {
            background: #fff5f5; border: 1px solid #fecaca;
            border-left: 4px solid #ef4444; color: #dc2626;
            padding: 0.8rem 1rem; border-radius: 10px;
            font-size: 0.83rem; margin-bottom: 1.2rem;
            display: flex; align-items: center; gap: 0.5rem;
        }

        .btn-cadastrar {
            width: 100%; padding: 1.05rem;
            background: linear-gradient(135deg, #ff4d6d, #c9184a);
            color: white; border: none; border-radius: 12px;
            font-size: 1rem; font-weight: 700;
            font-family: 'Poppins', sans-serif;
            cursor: pointer; transition: all 0.3s;
            display: flex; align-items: center; justify-content: center; gap: 0.6rem;
            box-shadow: 0 4px 20px rgba(255,77,109,0.35);
            margin-top: 0.8rem;
        }
        .btn-cadastrar:hover { transform: translateY(-3px); box-shadow: 0 8px 30px rgba(255,77,109,0.5); }
        .btn-cadastrar:active { transform: translateY(0); }
        .btn-cadastrar:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }

        .divider {
            display: flex; align-items: center; gap: 1rem;
            margin: 1.5rem 0; color: #bdbdbd; font-size: 0.82rem;
        }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #eeeeee; }

        .form-footer { text-align: center; }
        .form-footer p { color: #9e9e9e; font-size: 0.88rem; margin-bottom: 0.5rem; }
        .form-footer a { color: #ff4d6d; font-weight: 600; text-decoration: none; transition: color 0.2s; }
        .form-footer a:hover { color: #c9184a; text-decoration: underline; }
        .btn-voltar {
            display: inline-flex; align-items: center; gap: 0.4rem;
            color: #9e9e9e; font-size: 0.85rem; text-decoration: none;
            transition: color 0.2s; margin-top: 0.8rem;
        }
        .btn-voltar:hover { color: #ff4d6d; }

        /* FORÇA SENHA */
        .forca-senha { margin-top: 0.4rem; }
        .forca-barra { height: 4px; border-radius: 4px; background: #eeeeee; overflow: hidden; }
        .forca-fill { height: 100%; border-radius: 4px; transition: all 0.3s; width: 0; }
        .forca-texto { font-size: 0.75rem; margin-top: 0.3rem; font-weight: 500; }

        @media (max-width: 960px) { .cad-left { display: none; } .cad-right { width: 100%; box-shadow: none; } }
        @media (max-width: 480px) { .cad-right { padding: 2rem 1.5rem; } .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<!-- LADO ESQUERDO -->
<div class="cad-left">
    <div class="left-content">
        <div class="left-logo">🌸 FLORI</div>
        <p class="left-tagline">Junte-se à maior rede de floriculturas</p>
        <div class="flores-deco">
            <span>🌸</span><span>🌺</span><span>🌷</span><span>🌼</span>
        </div>
        <div class="tipo-cards">
            <div class="tipo-card">
                <div class="tipo-card-icon">🛍️</div>
                <div class="tipo-card-text">
                    <strong>Sou Cliente</strong>
                    <span>Compre flores das melhores lojas</span>
                </div>
            </div>
            <div class="tipo-card">
                <div class="tipo-card-icon">🏪</div>
                <div class="tipo-card-text">
                    <strong>Sou Vendedor</strong>
                    <span>Venda suas flores para milhares de clientes</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- LADO DIREITO -->
<div class="cad-right">
    <div class="cad-form-wrapper">

        <div class="form-header">
            <h2>Criar sua conta 🌸</h2>
            <p>Preencha os dados abaixo para começar</p>
        </div>

        <div id="erroMsg" class="erro-msg" style="display:none;">
            <i class="fas fa-exclamation-circle"></i>
            <span id="erroTexto"></span>
        </div>

        <!-- TIPO DE CONTA -->
        <div class="tipo-selector">
            <label class="tipo-opt selected" id="optCliente">
                <input type="radio" name="tipo_visual" value="cliente" checked>
                <i class="fas fa-user"></i>
                <span>Cliente</span>
            </label>
            <label class="tipo-opt" id="optVendedor">
                <input type="radio" name="tipo_visual" value="vendedor">
                <i class="fas fa-store"></i>
                <span>Vendedor</span>
            </label>
        </div>

        <form id="cadastroForm">
            <input type="hidden" id="tipoInput" name="tipo" value="cliente">

            <div class="form-row">
                <div class="input-group">
                    <label>Nome Completo</label>
                    <div class="input-wrap">
                        <i class="fas fa-user icon-left"></i>
                        <input type="text" id="nome" name="nome" required placeholder="Seu nome">
                    </div>
                </div>
                <div class="input-group">
                    <label>Telefone</label>
                    <div class="input-wrap">
                        <i class="fas fa-phone icon-left"></i>
                        <input type="text" id="telefone" name="telefone" placeholder="(00) 00000-0000" oninput="mascaraTel(this)">
                    </div>
                </div>
            </div>

            <div class="input-group">
                <label>Email</label>
                <div class="input-wrap">
                    <i class="fas fa-envelope icon-left"></i>
                    <input type="email" id="email" name="email" required placeholder="seu@email.com">
                </div>
            </div>

            <div class="input-group floricultura-group" id="grupoFloricultura">
                <label>Nome da Floricultura</label>
                <div class="input-wrap">
                    <i class="fas fa-store icon-left"></i>
                    <input type="text" id="nome_floricultura" name="nome_floricultura" placeholder="Ex: Flores da Maria">
                </div>
            </div>

            <div class="form-row">
                <div class="input-group">
                    <label>Senha</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock icon-left"></i>
                        <input type="password" id="senha" name="senha" required placeholder="Mín. 6 caracteres" oninput="verificarForca(this.value)">
                        <button type="button" class="toggle-senha" onclick="toggleSenha('senha','olho1')">
                            <i class="fas fa-eye" id="olho1"></i>
                        </button>
                    </div>
                    <div class="forca-senha">
                        <div class="forca-barra"><div class="forca-fill" id="forcaFill"></div></div>
                        <p class="forca-texto" id="forcaTexto" style="color:#bdbdbd;"></p>
                    </div>
                </div>
                <div class="input-group">
                    <label>Confirmar Senha</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock icon-left"></i>
                        <input type="password" id="confirmar_senha" name="confirmar_senha" required placeholder="Repita a senha">
                        <button type="button" class="toggle-senha" onclick="toggleSenha('confirmar_senha','olho2')">
                            <i class="fas fa-eye" id="olho2"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-cadastrar" id="btnCadastrar">
                <i class="fas fa-user-plus"></i> Criar Conta
            </button>
        </form>

        <div class="divider">ou</div>

        <div class="form-footer">
            <p>Já tem conta? <a href="login.php">Faça login</a></p>
            <a href="index.php" class="btn-voltar">
                <i class="fas fa-arrow-left"></i> Voltar para a loja
            </a>
        </div>

    </div>
</div>

<script>
// Tipo de conta
document.querySelectorAll('.tipo-opt').forEach(opt => {
    opt.addEventListener('click', function() {
        document.querySelectorAll('.tipo-opt').forEach(o => o.classList.remove('selected'));
        this.classList.add('selected');
        const val = this.querySelector('input').value;
        document.getElementById('tipoInput').value = val;
        const grupo = document.getElementById('grupoFloricultura');
        grupo.classList.toggle('show', val === 'vendedor');
    });
});

// Toggle senha
function toggleSenha(id, iconId) {
    const input = document.getElementById(id);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Força da senha
function verificarForca(senha) {
    const fill  = document.getElementById('forcaFill');
    const texto = document.getElementById('forcaTexto');
    let forca = 0;
    if (senha.length >= 6)  forca++;
    if (senha.length >= 10) forca++;
    if (/[A-Z]/.test(senha)) forca++;
    if (/[0-9]/.test(senha)) forca++;
    if (/[^A-Za-z0-9]/.test(senha)) forca++;

    const niveis = [
        { w:'0%',   cor:'#eeeeee', txt:'' },
        { w:'25%',  cor:'#ef4444', txt:'Muito fraca' },
        { w:'50%',  cor:'#f97316', txt:'Fraca' },
        { w:'75%',  cor:'#eab308', txt:'Boa' },
        { w:'90%',  cor:'#22c55e', txt:'Forte' },
        { w:'100%', cor:'#16a34a', txt:'Muito forte' },
    ];
    const n = niveis[Math.min(forca, 5)];
    fill.style.width      = n.w;
    fill.style.background = n.cor;
    texto.textContent     = n.txt;
    texto.style.color     = n.cor;
}

// Máscara telefone
function mascaraTel(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 11);
    if (v.length > 6) v = '(' + v.substring(0,2) + ') ' + v.substring(2,7) + '-' + v.substring(7);
    else if (v.length > 2) v = '(' + v.substring(0,2) + ') ' + v.substring(2);
    else if (v.length > 0) v = '(' + v;
    input.value = v;
}

// Submit
document.getElementById('cadastroForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const senha     = document.getElementById('senha').value;
    const confirmar = document.getElementById('confirmar_senha').value;
    const erroMsg   = document.getElementById('erroMsg');
    const erroTexto = document.getElementById('erroTexto');

    const mostrarErro = (msg) => {
        erroTexto.textContent = msg;
        erroMsg.style.display = 'flex';
        erroMsg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    };

    erroMsg.style.display = 'none';

    if (senha.length < 6)      return mostrarErro('A senha deve ter no mínimo 6 caracteres.');
    if (senha !== confirmar)   return mostrarErro('As senhas não coincidem.');

    const btn = document.getElementById('btnCadastrar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Criando conta...';

    const formData = new FormData();
    formData.append('action', 'cadastrar');
    formData.append('nome',   document.getElementById('nome').value);
    formData.append('email',  document.getElementById('email').value);
    formData.append('telefone', document.getElementById('telefone').value);
    formData.append('tipo',   document.getElementById('tipoInput').value);
    formData.append('senha',  senha);
    if (document.getElementById('tipoInput').value === 'vendedor') {
        formData.append('nome_floricultura', document.getElementById('nome_floricultura').value);
    }

    try {
        const response = await fetch('../controller/AuthController.php', { method: 'POST', body: formData });
        const data     = await response.json();

        if (data.success) {
            btn.innerHTML = '<i class="fas fa-check"></i> Conta criada!';
            btn.style.background = 'linear-gradient(135deg,#22c55e,#16a34a)';
            setTimeout(() => {
                window.location.href = data.user?.perfil === 'vendedor' ? 'vendedor/dashboard.php' : 'index.php';
            }, 800);
        } else {
            mostrarErro(data.message || 'Erro ao cadastrar.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-user-plus"></i> Criar Conta';
        }
    } catch {
        mostrarErro('Erro de conexão. Tente novamente.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-user-plus"></i> Criar Conta';
    }
});
</script>

</body>
</html>
