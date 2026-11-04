<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar">
    <div class="nav-container">
        <a href="dashboard.php" class="nav-logo">🍵 Té Devoto</a>
        <ul class="nav-menu">
            <?php if(isset($_SESSION['user_id'])): ?>
                <li><a href="dashboard.php">Inicio</a></li>
                <li><a href="products.php">Productos</a></li>
                <li><a href="sales.php">Ventas</a></li>
                <li><a href="reports.php">Reportes</a></li>
                <li class="nav-user">👤 <?php echo $_SESSION['username']; ?> (<?php echo $_SESSION['role']; ?>)</li>
                <li><a href="logout.php" class="btn-logout">Cerrar Sesión</a></li>
            <?php else: ?>
                <li><a href="index.php">Iniciar Sesión</a></li>
                <li><a href="register.php">Registrarse</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
<main class="content-container">