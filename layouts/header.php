<?php
if (!class_exists('Usuario', false)) {
    require_once __DIR__ . '/../models/Usuario.php';
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$usuarioActual = (isset($_SESSION['usuario']) && $_SESSION['usuario'] instanceof Usuario) ? $_SESSION['usuario'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina ?? 'SGBV - Sistema de Gestión de Bibliotecas Virtuales') ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom Premium CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css?v=<?= file_exists(__DIR__ . '/../public/css/style.css') ? filemtime(__DIR__ . '/../public/css/style.css') : time() ?>">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>home">
            <i class="bi bi-book-half"></i> SGBV <span class="fs-6 fw-normal text-secondary d-none d-sm-inline">| Biblioteca Digital</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>home"><i class="bi bi-grid-fill me-1"></i> Catálogo</a>
                </li>
                <?php if ($usuarioActual): ?>
                    <?php if ($usuarioActual->rol_id === 1): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>admin/dashboard"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>libreria/mis_libros"><i class="bi bi-collection-play-fill me-1"></i> Mi Librería</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>admin/billetera"><i class="bi bi-wallet2 me-1"></i> Mi Billetera</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>estandar/panel"><i class="bi bi-bookmark-star-fill me-1"></i> Mi Panel & Préstamos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>libreria/mis_libros"><i class="bi bi-collection-play-fill me-1"></i> Mi Librería</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <?php if ($usuarioActual): ?>
                    <button class="btn btn-outline-custom position-relative d-flex align-items-center gap-2 py-1 px-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#cartOffcanvas" aria-controls="cartOffcanvas" title="Ver carrito de préstamos">
                        <i class="bi bi-cart3 text-info fs-5"></i>
                        <span class="d-none d-md-inline fw-semibold">Carrito</span>
                        <span class="badge bg-danger rounded-pill px-2 py-1" id="cartBadgeCount"><?= count($_SESSION['carrito'] ?? []) ?></span>
                    </button>

                    <div class="dropdown">
                        <button class="btn btn-outline-custom dropdown-toggle d-flex align-items-center gap-2" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-5 text-primary"></i>
                            <span><?= htmlspecialchars($usuarioActual->nombre) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow-lg border border-secondary rounded-3 mt-2" aria-labelledby="userMenu">
                            <?php if ($usuarioActual->rol_id === 1): ?>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/dashboard"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>libreria/mis_libros"><i class="bi bi-collection-play-fill me-2"></i> Mi Librería</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/recursos"><i class="bi bi-journal-plus me-2"></i> Gestión de Recursos</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/historial"><i class="bi bi-clock-history me-2"></i> Historial de Préstamos</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/reportes"><i class="bi bi-file-earmark-bar-graph me-2"></i> Reportes PDF/Excel</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/billetera"><i class="bi bi-wallet2 me-2"></i> Mi Billetera</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>home"><i class="bi bi-grid me-2"></i> Catálogo Literario</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>estandar/panel"><i class="bi bi-bookmark-star me-2"></i> Mi Panel & Préstamos</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>libreria/mis_libros"><i class="bi bi-collection-play-fill me-2"></i> Mi Librería</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider border-secondary"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>usuario/configuracion"><i class="bi bi-gear me-2"></i> Configuración de Cuenta</a></li>
                            <li><hr class="dropdown-divider border-secondary"></li>
                            <li class="px-2 pb-1"><a class="dropdown-item rounded-2 bg-danger text-white fw-bold d-flex align-items-center py-2" href="<?= BASE_URL ?>logout" style="background-color: #dc3545 !important; color: #ffffff !important;"><i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>login" class="btn btn-outline-custom"><i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión</a>
                    <a href="<?= BASE_URL ?>registro" class="btn btn-gradient-primary"><i class="bi bi-person-plus-fill me-1"></i> Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<?php
// Sistema de notificaciones flotantes (Tarea 11)
if ($usuarioActual && !class_exists('Prestamo', false)) {
    require_once __DIR__ . '/../models/Prestamo.php';
}
if ($usuarioActual && $usuarioActual->rol_id === 2):
    $prestamosActivosNotif = Prestamo::obtenerPorUsuario($usuarioActual->id);
    $alertaVencimiento = [];
    foreach ($prestamosActivosNotif as $pNotif) {
        if ($pNotif->estado === 'activo') {
            $diasRest = $pNotif->calcularDiasRestantes();
            if ($diasRest <= 3 && $diasRest >= 0) {
                $alertaVencimiento[] = ['id' => $pNotif->id, 'titulo' => $pNotif->recurso_titulo, 'dias' => $diasRest];
            }
        }
    }
?>
    <?php if (!empty($alertaVencimiento)): ?>
        <div class="container mt-3">
            <?php foreach ($alertaVencimiento as $av): ?>
                <div class="alert alert-warning alert-dismissible fade show rounded-4 border-1 border-warning shadow-lg d-flex align-items-center justify-content-between gap-3 animate-fade-in py-2 px-4" role="alert">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-warning text-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-bell-fill fs-5"></i>
                        </div>
                        <div>
                            <strong class="text-dark">¡Atención! Tu préstamo está por vencer:</strong>
                            <span class="text-dark ms-1">"<?= htmlspecialchars($av['titulo']) ?>" vence en <b><?= $av['dias'] ?> día(s)</b>.</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="<?= BASE_URL ?>estandar/renovar?prestamo_id=<?= $av['id'] ?>" class="btn btn-sm btn-dark fw-bold rounded-pill px-3">
                            <i class="bi bi-arrow-repeat me-1"></i> Renovar 14 Días
                        </a>
                        <button type="button" class="btn-close position-static" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<main class="flex-grow-1">
    <!-- Contenedor de notificaciones globales -->
    <div class="container mt-3">
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-lg d-flex align-items-center gap-2 animate-fade-in" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-4 text-danger"></i>
                <div><?= htmlspecialchars($_SESSION['error']) ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['exito'])): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-lg d-flex align-items-center gap-2 animate-fade-in" role="alert">
                <i class="bi bi-check-circle-fill fs-4 text-success"></i>
                <div><?= htmlspecialchars($_SESSION['exito']) ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['exito']); ?>
        <?php endif; ?>
    </div>
