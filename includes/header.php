<?php
if (!isset($pageTitle)) {
    $pageTitle = 'Veículos Cadastrados';
}

$currentPage = basename($_SERVER['PHP_SELF']);

function navActive($file, $currentPage) {
    return $currentPage === $file ? ' active' : '';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style.css?v=app-20260512-1" />
</head>
<body>
    <div class="app-shell">
        <main class="main-content">
            <header class="app-header">
                <button class="menu-button" type="button" aria-label="Abrir menu">
                    <i class="fas fa-bars"></i>
                </button>
                <a class="brand-mark" href="index.php" aria-label="Inicio">
                    <i class="fas fa-shield-halved"></i>
                    <i class="fas fa-car-side"></i>
                </a>
                <div class="brand-copy">
                    <h1><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
                    <p>Painel de controle de sinistros veiculares</p>
                </div>
                <a class="profile-pill" href="index.php" aria-label="Perfil">
                    <i class="fas fa-user"></i>
                    <span></span>
                </a>
            </header>
