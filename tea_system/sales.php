<?php
// Incluimos la configuración inicial y la conexión a la base de datos
require_once 'config.php';

// Si el usuario no ha iniciado sesión, lo redirigimos al login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$mensaje = "";
$tipo_mensaje = ""; // Puede ser 'success' (verde) o 'error' (rojo)

// PROCESAR EL FORMULARIO CUANDO SE ENVÍA UNA VENTA
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registrar_venta'])) {
    $id_product = intval($_POST['id_product']);
    $quantity = intval($_POST['quantity']);
    $id_user = $_SESSION['user_id'];
    $sale_date = date('Y-m-d H:i:s');

    // 1. Obtener los datos del producto (precio y stock actual)
    $query_prod = "SELECT price, stock, product_name FROM Products WHERE id_product = $id_product";
    $result_prod = $conn->query($query_prod);

    if ($result_prod && $result_prod->num_rows > 0) {
        $producto = $result_prod->fetch_assoc();
        $precio_unitario = $producto['price'];
        $stock_actual = $producto['stock'];
        $nombre_producto = $producto['product_name'];

        // 2. Validar si hay suficiente stock para la venta
        if ($quantity > $stock_actual) {
            $mensaje = "Error: No hay suficiente stock de $nombre_producto. Stock disponible: $stock_actual unidades.";
            $tipo_mensaje = "error";
        } else {
            // Calcular los totales
            $subtotal = $precio_unitario * $quantity;
            $total_venta = $subtotal; // En este flujo simple, el total de la venta coincide con el subtotal

            // Iniciar una transacción en la base de datos para asegurar que se guarde todo o nada
            $conn->begin_transaction();

            try {
                // 3. Insertar la cabecera de la venta en la tabla 'Sales'
                $stmt_sales = $conn->prepare("INSERT INTO Sales (sale_date, id_user, total) VALUES (?, ?, ?)");
                $stmt_sales->bind_param("sid", $sale_date, $id_user, $total_venta);
                $stmt_sales->execute();
                $id_sale = $conn->insert_id; // Obtenemos el ID de la venta que se acaba de crear

                // 4. Insertar el detalle en la tabla 'SaleDetail'
                $stmt_detail = $conn->prepare("INSERT INTO SaleDetail (id_sale, id_product, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
                $stmt_detail->bind_param("iiidd", $id_sale, $id_product, $quantity, $precio_unitario, $subtotal);
                $stmt_detail->execute();

                // 5. Actualizar el stock del producto restando lo vendido
                $nuevo_stock = $stock_actual - $quantity;
                $stmt_stock = $conn->prepare("UPDATE Products SET stock = ? WHERE id_product = ?");
                $stmt_stock->bind_param("ii", $nuevo_stock, $id_product);
                $stmt_stock->execute();

                // Si todo salió bien, guardamos los cambios de forma definitiva
                $conn->commit();

                $mensaje = "Venta registrada con éxito. Total: $" . number_format($total_venta, 2);
                $tipo_mensaje = "success";

                // Alerta de Stock Mínimo (Si queda por debajo de 10 unidades)
                if ($nuevo_stock < 10) {
                    $mensaje .= " ¡Atención! El stock de '$nombre_producto' ha quedado bajo ($nuevo_stock unidades).";
                    $tipo_mensaje = "warning"; // Usaremos un color naranja de advertencia
                }

            } catch (Exception $e) {
                // Si algo falla, deshacemos los cambios para proteger la integridad de los datos
                $conn->rollback();
                $mensaje = "Error crítico al registrar la venta: " . $e->getMessage();
                $tipo_mensaje = "error";
            }
        }
    } else {
        $mensaje = "El producto seleccionado no existe.";
        $tipo_mensaje = "error";
    }
}

// Obtener la lista de productos para rellenar el menú desplegable del formulario
$productos_disponibles = $conn->query("SELECT id_product, product_name, price, stock FROM Products ORDER BY product_name ASC");

// Obtener el historial de las últimas 10 ventas para mostrar en pantalla
$query_historial = "SELECT s.id_sale, s.sale_date, u.username, p.product_name, sd.quantity, sd.subtotal 
                    FROM SaleDetail sd
                    JOIN Sales s ON sd.id_sale = s.id_sale
                    JOIN Products p ON sd.id_product = p.id_product
                    JOIN Users u ON s.id_user = u.id_user
                    ORDER BY s.sale_date DESC LIMIT 10";
$historial_ventas = $conn->query($query_historial);

// Incluimos los componentes visuales del inicio (HTML, Navbar)
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="sales-page-container">
<h2 style="margin-bottom:25px;color:#08294d;font-size:32px;">
🛒 Gestión de Ventas
</h2>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?>">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>

    <div class="sales-grid">
        <div class="sales-card form-section">
            <h3>Registrar Nueva Venta</h3>
            <form action="sales.php" method="POST">
                <div class="form-group">
                    <label for="id_product">Seleccionar Producto (Té):</label>
                    <select name="id_product" id="id_product" required>
                        <option value="">-- Seleccione un té --</option>
                        <?php if ($productos_disponibles && $productos_disponibles->num_rows > 0): ?>
                            <?php while($prod = $productos_disponibles->fetch_assoc()): ?>
                                <option value="<?php echo $prod['id_product']; ?>">
                                    <?php echo $prod['product_name']; ?> (Disponibles: <?php echo $prod['stock']; ?> - $<?php echo $prod['price']; ?>)
                                </option>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <option value="" disabled>No hay productos registrados en la base de datos</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="quantity">Cantidad a vender:</label>
                    <input type="number" name="quantity" id="quantity" min="1" value="1" required>
                </div>

                <button type="submit" name="registrar_venta" class="btn-submit">Procesar Venta</button>
            </form>
        </div>

        <div class="sales-card history-section">
            <h3>Últimas Ventas Registradas</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Fecha/Hora</th>
                            <th>Producto</th>
                            <th>Cant.</th>
                            <th>Total</th>
                            <th>Vendedor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($historial_ventas && $historial_ventas->num_rows > 0): ?>
                            <?php while($venta = $historial_ventas->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($venta['sale_date'])); ?></td>
                                    <td><?php echo $venta['product_name']; ?></td>
                                    <td><?php echo $venta['quantity']; ?></td>
                                    <td>$<?php echo number_format($venta['subtotal'], 2); ?></td>
                                    <td><?php echo $venta['username']; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">Aún no se han registrado ventas en el sistema.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php 
// Incluimos el pie de página para cerrar correctamente las etiquetas HTML
include 'includes/footer.php'; 
?>