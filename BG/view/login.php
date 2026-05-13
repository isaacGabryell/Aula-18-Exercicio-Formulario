<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

require_once __DIR__ . '/../controller/AuthController.php';

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth   = new AuthController();
    $result = $auth->login($_POST['email'] ?? '', $_POST['senha'] ?? '');
    if ($result['success']) {
        $perfil = $result['user']['perfil'];
        if ($perfil === 'admin')        header('Location: admin/dashboard.php');
        elseif ($perfil === 'vendedor') header('Location: vendedor/dashboard.php');
        else                            header('Location: index.php');
        exit();
    } else {
        $erro = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌸 FLORI | Entrar</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

        body {
            min-height: 100vh;
            display: flex;
            font-family: 'Poppins', sans-serif;
            background: #0d0d0d;
            overflow: hidden;
        }

        /* ── LADO ESQUERDO ── */
        .left {
            flex: 1;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            overflow: hidden;
            background: linear-gradient(160deg, #1a0010 0%, #3d0020 40%, #6b0030 100%);
        }

        /* Orbs de luz */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.5;
            animation: orbFloat 8s ease-in-out infinite;
        }
        .orb-1 { width:400px; height:400px; background:#ff4d6d; top:-100px; left:-100px; animation-delay:0s; }
        .orb-2 { width:300px; height:300px; background:#c9184a; bottom:-80px; right:-80px; animation-delay:3s; }
        .orb-3 { width:200px; height:200px; background:#ff8fa3; top:50%; left:50%; transform:translate(-50%,-50%); animation-delay:1.5s; }

        @keyframes orbFloat {
            0%,100% { transform: translate(0,0) scale(1); }
            33%      { transform: translate(30px,-20px) scale(1.05); }
            66%      { transform: translate(-20px,30px) scale(0.95); }
        }
        .orb-3 { animation-name: orbFloat3; }
        @keyframes orbFloat3 {
            0%,100% { transform: translate(-50%,-50%) scale(1); }
            50%      { transform: translate(-50%,-50%) scale(1.3); }
        }

        /* Partículas de flores */
        .particles { position:absolute; inset:0; pointer-events:none; }
        .p {
            position: absolute;
            font-size: 1.8rem;
            opacity: 0;
            animation: particleFly 10s ease-in-out infinite;
        }
        .p:nth-child(1)  { left:10%; animation-delay:0s;    font-size:2.5rem; }
        .p:nth-child(2)  { left:25%; animation-delay:1.5s;  font-size:1.5rem; }
        .p:nth-child(3)  { left:40%; animation-delay:3s;    font-size:2rem;   }
        .p:nth-child(4)  { left:55%; animation-delay:4.5s;  font-size:1.8rem; }
        .p:nth-child(5)  { left:70%; animation-delay:2s;    font-size:2.2rem; }
        .p:nth-child(6)  { left:85%; animation-delay:5s;    font-size:1.6rem; }
        .p:nth-child(7)  { left:18%; animation-delay:6s;    font-size:2.8rem; }
        .p:nth-child(8)  { left:62%; animation-delay:7s;    font-size:1.4rem; }
        .p:nth-child(9)  { left:78%; animation-delay:0.8s;  font-size:2rem;   }
        .p:nth-child(10) { left:35%; animation-delay:8s;    font-size:1.9rem; }

        @keyframes particleFly {
            0%   { bottom:-5%; opacity:0;   transform: translateX(0)    rotate(0deg)   scale(0.5); }
            10%  { opacity:0.7; }
            80%  { opacity:0.4; }
            100% { bottom:105%; opacity:0;  transform: translateX(40px) rotate(360deg) scale(1.2); }
        }

        /* Conteúdo esquerdo */
        .left-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
            max-width: 420px;
        }

        .brand {
            margin-bottom: 2.5rem;
        }
        .brand-icon {
            font-size: 5rem;
            display: block;
            margin-bottom: 0.5rem;
            animation: brandPulse 3s ease-in-out infinite;
            filter: drop-shadow(0 0 20px rgba(255,143,163,0.8));
        }
        @keyframes brandPulse {
            0%,100% { transform: scale(1);    filter: drop-shadow(0 0 20px rgba(255,143,163,0.8)); }
            50%      { transform: scale(1.08); filter: drop-shadow(0 0 35px rgba(255,77,109,1)); }
        }
        .brand-name {
            font-family: 'Playfair Display', serif;
            font-size: 4.5rem;
            font-weight: 700;
            line-height: 1;
            background: linear-gradient(135deg, #fff 0%, #ffb3c1 50%, #ff8fa3 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -1px;
        }
        .brand-tagline {
            font-size: 1rem;
            color: rgba(255,255,255,0.6);
            font-style: italic;
            font-family: 'Playfair Display', serif;
            margin-top: 0.5rem;
        }

        /* Cards de features */
        .features { display:flex; flex-direction:column; gap:0.9rem; margin-top:2.5rem; }
        .feat {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.12);
            backdrop-filter: blur(20px);
            padding: 1rem 1.4rem;
            border-radius: 16px;
            text-align: left;
            transition: all 0.3s;
            cursor: default;
        }
        .feat:hover {
            background: rgba(255,255,255,0.13);
            border-color: rgba(255,143,163,0.4);
            transform: translateX(6px);
        }
        .feat-icon {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, rgba(255,77,109,0.4), rgba(201,24,74,0.4));
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
            border: 1px solid rgba(255,143,163,0.3);
        }
        .feat-text strong { display:block; font-size:0.88rem; color:white; font-weight:600; }
        .feat-text span   { font-size:0.75rem; color:rgba(255,255,255,0.5); }

        /* ── LADO DIREITO ── */
        .right {
            width: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 2.5rem;
            background: #ffffff;
            position: relative;
            overflow: hidden;
        }

        /* Detalhe decorativo no canto */
        .right::before {
            content: '';
            position: absolute;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(255,143,163,0.08) 0%, transparent 70%);
            top: -100px; right: -100px;
            border-radius: 50%;
        }
        .right::after {
            content: '';
            position: absolute;
            width: 200px; height: 200px;
            background: radial-gradient(circle, rgba(255,77,109,0.06) 0%, transparent 70%);
            bottom: -60px; left: -60px;
            border-radius: 50%;
        }

        .form-wrap {
            width: 100%;
            max-width: 380px;
            position: relative;
            z-index: 1;
            animation: slideIn 0.6s cubic-bezier(0.16,1,0.3,1);
        }
        @keyframes slideIn {
            from { opacity:0; transform:translateY(30px); }
            to   { opacity:1; transform:translateY(0); }
        }

        .form-top { margin-bottom: 2.5rem; }
        .form-top .greeting {
            font-size: 0.85rem;
            color: #ff4d6d;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 0.6rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .form-top .greeting::before {
            content: '';
            width: 24px; height: 2px;
            background: #ff4d6d;
            border-radius: 2px;
        }
        .form-top h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.4rem;
            color: #111;
            line-height: 1.2;
            margin-bottom: 0.5rem;
        }
        .form-top p { color: #9e9e9e; font-size: 0.9rem; }

        /* Inputs */
        .field { margin-bottom: 1.4rem; }
        .field label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .field-wrap { position: relative; }
        .field-wrap .fi {
            position: absolute;
            left: 1.1rem; top: 50%;
            transform: translateY(-50%);
            color: #ccc;
            font-size: 0.9rem;
            transition: color 0.25s;
            pointer-events: none;
        }
        .field-wrap:focus-within .fi { color: #ff4d6d; }
        .field-wrap input {
            width: 100%;
            padding: 1rem 1rem 1rem 2.8rem;
            border: 2px solid #f0f0f0;
            border-radius: 14px;
            font-size: 0.95rem;
            font-family: 'Poppins', sans-serif;
            color: #111;
            background: #fafafa;
            outline: none;
            transition: all 0.25s;
        }
        .field-wrap input:focus {
            border-color: #ff4d6d;
            background: #fff;
            box-shadow: 0 0 0 5px rgba(255,77,109,0.07);
        }
        .field-wrap input::placeholder { color: #ccc; }
        .eye-btn {
            position: absolute;
            right: 1rem; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            cursor: pointer; color: #ccc;
            font-size: 0.9rem; padding: 0;
            transition: color 0.2s;
        }
        .eye-btn:hover { color: #ff4d6d; }

        /* Erro */
        .erro-box {
            background: #fff5f5;
            border: 1.5px solid #fecaca;
            border-left: 4px solid #ef4444;
            color: #dc2626;
            padding: 0.85rem 1rem;
            border-radius: 12px;
            font-size: 0.84rem;
            margin-bottom: 1.4rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            animation: shake 0.4s ease;
        }
        @keyframes shake {
            0%,100% { transform:translateX(0); }
            20%      { transform:translateX(-6px); }
            40%      { transform:translateX(6px); }
            60%      { transform:translateX(-4px); }
            80%      { transform:translateX(4px); }
        }

        /* Botão */
        .btn-login {
            width: 100%;
            padding: 1.1rem;
            background: linear-gradient(135deg, #ff4d6d 0%, #c9184a 100%);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 1rem;
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.7rem;
            box-shadow: 0 6px 25px rgba(255,77,109,0.4);
            position: relative;
            overflow: hidden;
            margin-top: 0.5rem;
        }
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transition: left 0.5s;
        }
        .btn-login:hover { transform:translateY(-3px); box-shadow:0 12px 35px rgba(255,77,109,0.55); }
        .btn-login:hover::before { left: 100%; }
        .btn-login:active { transform:translateY(0); }

        /* Divider */
        .div-or {
            display: flex; align-items: center; gap: 1rem;
            margin: 1.8rem 0; color: #ddd; font-size: 0.8rem;
        }
        .div-or::before, .div-or::after { content:''; flex:1; height:1px; background:#f0f0f0; }

        /* Footer */
        .form-foot { text-align: center; }
        .form-foot p { color: #aaa; font-size: 0.87rem; margin-bottom: 0.5rem; }
        .form-foot a { color: #ff4d6d; font-weight: 700; text-decoration: none; transition: color 0.2s; }
        .form-foot a:hover { color: #c9184a; }
        .back-link {
            display: inline-flex; align-items: center; gap: 0.4rem;
            color: #bbb; font-size: 0.83rem; text-decoration: none;
            transition: color 0.2s; margin-top: 1rem;
        }
        .back-link:hover { color: #ff4d6d; }

        @media (max-width: 900px) {
            .left { display: none; }
            .right { width: 100%; }
            body { overflow: auto; }
        }
        @media (max-width: 480px) {
            .right { padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>

<!-- ── ESQUERDO ── -->
<div class="left">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="particles">
        <span class="p">🌸</span>
        <span class="p">🌺</span>
        <span class="p">🌷</span>
        <span class="p">🌼</span>
        <span class="p">🌸</span>
        <span class="p">🌹</span>
        <span class="p">🌺</span>
        <span class="p">🌷</span>
        <span class="p">🌸</span>
        <span class="p">🌼</span>
    </div>

    <div class="left-content">
        <div class="brand">
            <span class="brand-icon">🌸</span>
            <div class="brand-name">FLORI</div>
            <div class="brand-tagline">Flores que encantam, momentos que ficam</div>
        </div>

        <div class="features">
            <div class="feat">
                <div class="feat-icon">🏪</div>
                <div class="feat-text">
                    <strong>Floriculturas Parceiras</strong>
                    <span>Diversas lojas em um só lugar</span>
                </div>
            </div>
            <div class="feat">
                <div class="feat-icon">🚚</div>
                <div class="feat-text">
                    <strong>Entrega Rápida</strong>
                    <span>Flores frescas até você</span>
                </div>
            </div>
            <div class="feat">
                <div class="feat-icon">🔒</div>
                <div class="feat-text">
                    <strong>Pagamento Seguro</strong>
                    <span>Cartão, PIX e boleto</span>
                </div>
            </div>
            <div class="feat">
                <div class="feat-icon">💝</div>
                <div class="feat-text">
                    <strong>Flores Selecionadas</strong>
                    <span>Qualidade garantida</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── DIREITO ── -->
<div class="right">
    <div class="form-wrap">

        <div class="form-top">
            <div class="greeting">Bem-vindo de volta</div>
            <h2>Entre na sua<br>conta 👋</h2>
            <p>Acesse para continuar comprando flores incríveis</p>
        </div>

        <?php if ($erro): ?>
        <div class="erro-box">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($erro) ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="field">
                <label>Email</label>
                <div class="field-wrap">
                    <i class="fas fa-envelope fi"></i>
                    <input type="email" name="email" required placeholder="seu@email.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
            </div>

            <div class="field">
                <label>Senha</label>
                <div class="field-wrap">
                    <i class="fas fa-lock fi"></i>
                    <input type="password" name="senha" id="senhaInput" required placeholder="••••••••">
                    <button type="button" class="eye-btn" onclick="toggleSenha()">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-arrow-right-to-bracket"></i> Entrar
            </button>
        </form>

        <div class="div-or">ou</div>

        <div class="form-foot">
            <p>Não tem conta? <a href="cadastro.php">Cadastre-se grátis</a></p>
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Voltar para a loja
            </a>
        </div>

    </div>
</div>

<script>
function toggleSenha() {
    const i = document.getElementById('senhaInput');
    const e = document.getElementById('eyeIcon');
    i.type = i.type === 'password' ? 'text' : 'password';
    e.classList.toggle('fa-eye');
    e.classList.toggle('fa-eye-slash');
}
</script>
</body>
</html>
