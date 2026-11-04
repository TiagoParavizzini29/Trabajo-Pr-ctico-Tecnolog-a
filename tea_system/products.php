<?php
require_once 'config.php';

// Control de acceso: Si no está logueado, al login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$mensaje = "";

// 1. LÓGICA PARA AGREGAR UN PRODUCTO (CREATE)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    $name = trim($_POST['product_name']);
    $category = intval($_POST['id_category']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);

    if (!empty($name) && $price > 0 && $stock >= 0) {
        $stmt = $conn->prepare("INSERT INTO Products (product_name, id_category, price, stock) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sidi", $name, $category, $price, $stock);
        if ($stmt->execute()) {
            $mensaje = "<div class='alert alert-success'>✅ Producto agregado correctamente.</div>";
        } else {
            $mensaje = "<div class='alert alert-error'>❌ Error al agregar el producto.</div>";
        }
    }
}

// 2. LÓGICA PARA ELIMINAR UN PRODUCTO (DELETE)
if (isset($_GET['delete'])) {
    $id_delete = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM Products WHERE id_product = ?");
    $stmt->bind_param("i", $id_delete);
    if ($stmt->execute()) {
        header("Location: products.php?msg=deleted");
        exit;
    }
}

if (isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
    $mensaje = "<div class='alert alert-success'>✅ Producto eliminado correctamente.</div>";
}

// 3. CONSULTA: Traer todas las categorías para el desplegable
$categories_res = $conn->query("SELECT * FROM Categories");

// 4. CONSULTA: Traer todos los productos para la tabla (READ)
$products_res = $conn->query("SELECT p.*, c.category_name 
                              FROM Products p 
                              LEFT JOIN Categories c ON p.id_category = c.id_category 
                              ORDER BY p.id_product DESC");

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="products-container" style="max-width: 1200px; margin: 30px auto; padding: 0 20px;">
    <h2>Gestionar Inventario de Tés</h2>
    <p>Panel de control para altas, bajas y modificaciones del stock de Germán Paravizzini.</p>
    
    <?php echo $mensaje; ?>

    <div class="crud-card" style="background: white; padding: 25px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #E2E8F0; border-top: 4px solid var(--azul-marino);">
        <h3>➕ Añadir Nueva Variedad de Té</h3>
        <form action="products.php" method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">
            <div>
                <label style="display:block; margin-bottom:5px; font-weight:600;">Nombre del Té:</label>
                <input type="text" name="product_name" required style="width:100%; padding:8px; border:1px solid #CBD5E0; border-radius:4px;">
            </div>
            <div>
                <label style="display:block; margin-bottom:5px; font-weight:600;">Categoría:</label>
                <select name="id_category" required style="width:100%; padding:8px; border:1px solid #CBD5E0; border-radius:4px;">
                    <?php while($cat = $categories_res->fetch_assoc()): ?>
                        <option value="<?php echo $cat['id_category']; ?>"><?php echo $cat['category_name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label style="display:block; margin-bottom:5px; font-weight:600;">Precio ($):</label>
                <input type="number" step="0.01" name="price" required style="width:100%; padding:8px; border:1px solid #CBD5E0; border-radius:4px;">
            </div>
            <div>
                <label style="display:block; margin-bottom:5px; font-weight:600;">Stock Inicial:</label>
                <input type="number" name="stock" required style="width:100%; padding:8px; border:1px solid #CBD5E0; border-radius:4px;">
            </div>
            <div style="display: flex; align-items: flex-end;">
                <button type="submit" name="add_product" style="width:100%; background: var(--celeste); color: white; border:none; padding:10px; font-weight:bold; border-radius:4px; cursor:pointer;">Guardar Té</button>
            </div>
        </form>
    </div>

    <div class="crud-card" style="background: white; padding: 25px; border-radius: 8px; border: 1px solid #E2E8F0;">
        <h3>📦 Lista de Productos en Sistema</h3>
        <div style="overflow-x: auto; margin-top: 15px;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background-color: var(--azul-marino); color: white;">
                        <th style="padding: 12px;">ID</th>
                        <th style="padding: 12px;">Variedad / Nombre</th>
                        <th style="padding: 12px;">Categoría</th>
                        <th style="padding: 12px;">Precio Unitario</th>
                        <th style="padding: 12px;">Stock Disponible</th>
                        <th style="padding: 12px; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products_res && $products_res->num_rows > 0): ?>
                        <?php while($prod = $products_res->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid #E2E8F0;">
                                <td style="padding: 12px;"><?php echo $prod['id_product']; ?></td>
                                <td style="padding: 12px;"><strong><?php echo $prod['product_name']; ?></strong></td>
                                <td style="padding: 12px;"><?php echo $prod['category_name']; ?></td>
                                <td style="padding: 12px;">$<?php echo number_format($prod['price'], 2); ?></td>
                                <td style="padding: 12px;">
                                    <?php if($prod['stock'] < 10): ?>
                                        <span class="badge-danger" style="background:#FF4D4D; color:white; padding:3px 8px; border-radius:4px; font-weight:bold;">Low Stock (<?php echo $prod['stock']; ?>)</span>
                                    <?php else: ?>
                                        <span style="color: #2F855A; font-weight:bold;"><?php echo $prod['stock']; ?> unidades</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <a href="products.php?delete=<?php echo $prod['id_product']; ?>" 
                                       onclick="return confirm('¿Seguro que deseas eliminar este té del sistema?')" 
                                       style="color: #FF4D4D; text-decoration: none; font-weight: bold;">❌ Eliminar</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="padding: 20px; text-align: center; color: #A0AEC0;">No hay tés registrados en el inventario.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>