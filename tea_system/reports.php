<?php
// Incluimos la configuración y conexión a la base de datos
require_once 'config.php';

// Si el usuario no está logueado, se va al login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// 1. CONSULTA SQL: Top 5 de productos más vendidos (Criterio de Éxito)
$query_top5 = "SELECT p.product_name, SUM(sd.quantity) AS TotalSold, SUM(sd.subtotal) AS TotalRevenue
               FROM SaleDetail sd
               JOIN Products p ON sd.id_product = p.id_product
               GROUP BY p.id_product, p.product_name
               ORDER BY TotalSold DESC
               LIMIT 5";
$result_top5 = $conn->query($query_top5);

// 2. CONSULTA SQL: Reporte de Ganancias Mensuales del año actual
$query_mensual = "SELECT DATE_FORMAT(s.sale_date, '%M %Y') AS Mes, 
                         COUNT(s.id_sale) AS TotalVentas, 
                         SUM(s.total) AS GananciaMensual
                  FROM Sales s
                  WHERE YEAR(s.sale_date) = YEAR(CURRENT_DATE())
                  GROUP BY MONTH(s.sale_date), DATE_FORMAT(s.sale_date, '%M %Y')
                  ORDER BY MONTH(s.sale_date) DESC";
$result_mensual = $conn->query($query_mensual);

// Incluimos los componentes visuales
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="reports-page-container">
    <div 
    <div class="reports-header">
    <div>
        <h2>📊 Panel de Reportes</h2>
        <p>Resumen comercial del sistema de gestión Té Devoto.</p>
    </div>

    <button onclick="window.print();" class="btn-export">
        🖨️ Exportar PDF
    </button>
</div>

    <div class="reports-grid">
        <div class="report-card">
            <h3>🏆 Top 5 - Té Más Vendidos</h3>
            <p class="report-desc">Los 5 productos con mayor volumen de unidades salidas en el sistema.</p>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Puesto</th>
                            <th>Variedad de Té</th>
                            <th>Unidades Vendidas</th>
                            <th>Total Recaudado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($result_top5 && $result_top5->num_rows > 0): 
                            $puesto = 1;
                            while($top = $result_top5->fetch_assoc()):
                        ?>
                            <tr>
                                <td><strong>#<?php echo $puesto++; ?></strong></td>
                                <td><?php echo $top['product_name']; ?></td>
                                <td><?php echo $top['TotalSold']; ?> unidades</td>
                                <td>$<?php echo number_format($top['TotalRevenue'], 2); ?></td>
                            </tr>
                        <?php 
                            endwhile;
                        else: 
                        ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">No hay datos de ventas suficientes para armar el ranking.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="report-card">
            <h3>📊 Resumen de Ingresos por Mes</h3>
            <p class="report-desc">Historial de facturación acumulada durante el año en curso.</p>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Mes / Período</th>
                            <th>Cant. Transacciones</th>
                            <th>Total Facturado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($result_mensual && $result_mensual->num_rows > 0): 
                            while($mes = $result_mensual->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?php echo $mes['Mes']; ?></td>
                                <td><?php echo $mes['TotalVentas']; ?> ventas</td>
                                <td class="text-success"><strong>$<?php echo number_format($mes['GananciaMensual'], 2); ?></strong></td>
                            </tr>
                        <?php 
                            endwhile;
                        else: 
                        ?>
                            <tr>
                                <td colspan="3" style="text-align: center;">No se registran movimientos económicos en este año.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php 
// Incluimos el pie de página
include 'includes/footer.php'; 
?>