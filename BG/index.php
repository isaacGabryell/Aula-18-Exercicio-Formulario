<?php
// Redireciona para a view principal usando caminho absoluto da aplicação
$basePath = dirname($_SERVER['SCRIPT_NAME']);
$basePath = $basePath === '/' ? '' : $basePath;
header('Location: ' . $basePath . '/view/index.php');
exit();
?>