<?php
// Incluimos la configuración inicial y la base de datos
require_once 'config.php';

// Si el usuario no inició sesión, lo mandamos al login de inmediato
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// 1. CONSULTA SQL: Contar cuántos productos totales hay registrados
$query_prod_count = "SELECT COUNT(*) AS total_productos FROM Products";
$res_prod_count = $conn->query($query_prod_count);
$total_productos = ($res_prod_count) ? $res_prod_count->fetch_assoc()['total_productos'] : 0;

// 2. CONSULTA SQL: Calcular los ingresos totales por ventas del día de hoy
$hoy = date('Y-m-d');
$query_ventas_hoy = "SELECT SUM(total) AS total_hoy FROM Sales WHERE DATE(sale_date) = '$hoy'";
$res_ventas_hoy = $conn->query($query_ventas_hoy);
$row_ventas = $res_ventas_hoy->fetch_assoc();
$total_ventas_hoy = ($row_ventas['total_hoy']) ? $row_ventas['total_hoy'] : 0;

// 3. CONSULTA SQL: Detectar productos con bajo stock (Menos de 10 unidades - Criterio de Éxito)
$query_bajo_stock = "SELECT product_name, stock FROM Products WHERE stock < 10";
$result_bajo_stock = $conn->query($query_bajo_stock);
$cant_alertas = ($result_bajo_stock) ? $result_bajo_stock->num_rows : 0;

// Incluimos los componentes visuales del menú
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="dashboard-container">
    <div class="welcome-box">
        <h2>¡Bienvenido al Sistema, <?php echo $_SESSION['username']; ?>!</h2>
        <p>Perfil activo: <strong><?php echo ($_SESSION['role'] == 'Admin') ? 'Administrador del Sistema' : 'Usuario Estándar'; ?></strong></p>
        <p>Fecha actual de operación: <?php echo date('d/m/Y'); ?></p>
    </div>

    <?php if ($cant_alertas > 0): ?>
        <div class="dashboard-alert-card">
            <h3>⚠️ ALERTA DE INVENTARIO CRÍTICO</h3>
            <p>Se han detectado <?php echo $cant_alertas; ?> producto(s) con stock por debajo del límite mínimo (10 unidades):</p>
            <ul>
                <?php while($prod_alerta = $result_bajo_stock->fetch_assoc()): ?>
                    <li><strong><?php echo $prod_alerta['product_name']; ?></strong>: Solo quedan <span class="badge-danger"><?php echo $prod_alerta['stock']; ?> unidades</span>.</li>
                <?php endwhile; ?>
            </ul>
            <p><a href="products.php" class="btn-alert-link">Ir a reponer stock →</a></p>
        </div>
    <?php endif; ?>

    <div class="dashboard-grid">
        <div class="db-card card-blue">
            <h3>📦 Catálogo</h3>
            <p class="db-number"><?php echo $total_productos; ?></p>
            <p class="db-label">Variedades de té registradas</p>
        </div>

        <div class="db-card card-green">
            <h3>💰 Ventas de Hoy</h3>
            <p class="db-number">$<?php echo number_format($total_ventas_hoy, 2); ?></p>
            <p class="db-label">Ingresos acumulados hoy</p>
        </div>

        <div class="db-card card-purple">
            <h3>⚡ Accesos Rápidos</h3>
            <div class="db-actions">
                <a href="sales.php" class="btn-db-action">🛒 Registrar una Venta</a>
                <a href="reports.php" class="btn-db-action">📊 Ver Reportes del Mes</a>
            </div>
        </div>
    </div>
</div>

<?php 
// Incluimos el pie de página
include 'includes/footer.php'; 
?>