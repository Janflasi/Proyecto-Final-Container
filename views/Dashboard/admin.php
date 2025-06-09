<?php
// admin_panel.php - Panel principal integrado optimizado
require_once 'config/Conexion.php';
if (session_status() == PHP_SESSION_NONE) session_start();

$db = new Database();
if (!$pdo = $db->getConnection()) die("Error: No se pudo establecer conexión con la base de datos.");

// Función para ejecutar consultas preparadas
function queryDB($pdo, $sql, $params = []) {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

// Función para obtener datos con fetch
function fetchData($pdo, $sql, $params = []) {
    return queryDB($pdo, $sql, $params)->fetch();
}

// Función para obtener múltiples datos
function fetchAllData($pdo, $sql, $params = []) {
    return queryDB($pdo, $sql, $params)->fetchAll();
}

try {
    // Consultas optimizadas para dashboard
    $ventas_hoy = fetchData($pdo, "SELECT COALESCE(SUM(total), 0) as total_ventas, COUNT(*) as num_ventas FROM ventas WHERE DATE(fecha_venta) = CURDATE()");
    $productos_info = fetchData($pdo, "SELECT COUNT(*) as total_productos, COUNT(CASE WHEN stock <= 5 THEN 1 END) as productos_criticos FROM productos WHERE activo = 1");
    $usuarios_info = fetchData($pdo, "SELECT COUNT(*) as total_usuarios FROM usuarios WHERE activo = 1");
    $productos_stock_bajo = fetchAllData($pdo, "SELECT nombre_producto as nombre, stock FROM productos WHERE stock <= 5 AND activo = 1 LIMIT 5");
    
    $actividad_reciente = fetchAllData($pdo, "
        SELECT 'Venta' as tipo, CONCAT('Venta #', v.id_venta) as descripcion, 
               DATE_FORMAT(v.fecha_venta, '%H:%i') as hora, u.nombre as usuario, v.estado as estado
        FROM ventas v JOIN usuarios u ON v.id_usuario = u.id_usuario
        WHERE DATE(v.fecha_venta) = CURDATE() ORDER BY v.fecha_venta DESC LIMIT 10");
    
    $lista_productos = fetchAllData($pdo, "
        SELECT p.*, c.nombre_categoria as categoria_nombre 
        FROM productos p LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
        WHERE p.activo = 1 ORDER BY p.nombre_producto");
    
    $categorias = fetchAllData($pdo, "SELECT * FROM categorias ORDER BY nombre_categoria");
    $ventas_recientes = fetchAllData($pdo, "
        SELECT v.*, m.numero_mesa, u.nombre as usuario_nombre
        FROM ventas v JOIN mesas m ON v.id_mesa = m.id_mesa JOIN usuarios u ON v.id_usuario = u.id_usuario
        ORDER BY v.fecha_venta DESC LIMIT 20");
    
    $lista_usuarios = fetchAllData($pdo, "
        SELECT u.*, r.nombre_rol FROM usuarios u JOIN roles r ON u.id_rol = r.id_rol ORDER BY u.nombre");
    
    $roles = fetchAllData($pdo, "SELECT * FROM roles ORDER BY nombre_rol");
    $mesas = fetchAllData($pdo, "SELECT * FROM mesas ORDER BY numero_mesa");
    
    $reportes_ventas = fetchAllData($pdo, "
        SELECT DATE(fecha_venta) as fecha, COUNT(*) as num_ventas, SUM(total) as total_ventas
        FROM ventas WHERE fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY DATE(fecha_venta) ORDER BY fecha DESC");
    
    $productos_populares = fetchAllData($pdo, "
        SELECT p.nombre_producto, SUM(dv.cantidad) as total_vendido, SUM(dv.subtotal) as ingresos_totales
        FROM detalle_ventas dv JOIN productos p ON dv.id_producto = p.id_producto JOIN ventas v ON dv.id_venta = v.id_venta
        WHERE v.fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY p.id_producto, p.nombre_producto ORDER BY total_vendido DESC LIMIT 10");

} catch (Exception $e) {
    $error_message = "Error al cargar datos: " . $e->getMessage();
}

// Procesamiento de formularios optimizado
if ($_POST && isset($_POST['action'])) {
    try {
        $action = $_POST['action'];
        $mensaje_exito = "";
        
        switch ($action) {
            case 'nuevo_producto':
                queryDB($pdo, "INSERT INTO productos (nombre_producto, id_categoria, precio, stock, activo) VALUES (?, ?, ?, ?, 1)", 
                    [$_POST['nombre_producto'], $_POST['id_categoria'], $_POST['precio'], $_POST['stock']]);
                $mensaje_exito = "Producto agregado exitosamente";
                break;
                
            case 'nuevo_usuario':
                queryDB($pdo, "INSERT INTO usuarios (nombre, email, password, telefono, id_rol, activo) VALUES (?, ?, ?, ?, ?, 1)", 
                    [$_POST['nombre'], $_POST['email'], password_hash($_POST['password'], PASSWORD_DEFAULT), $_POST['telefono'], $_POST['id_rol']]);
                $mensaje_exito = "Usuario agregado exitosamente";
                break;
                
            case 'actualizar_stock':
                queryDB($pdo, "UPDATE productos SET stock = ? WHERE id_producto = ?", [$_POST['nuevo_stock'], $_POST['id_producto']]);
                $mensaje_exito = "Stock actualizado exitosamente";
                break;
                
            case 'nueva_mesa':
                queryDB($pdo, "INSERT INTO mesas (numero_mesa, capacidad, ubicacion, estado) VALUES (?, ?, ?, 'disponible')", 
                    [$_POST['numero_mesa'], $_POST['capacidad'], $_POST['ubicacion']]);
                $mensaje_exito = "Mesa agregada exitosamente";
                break;
                
            case 'eliminar_producto':
                queryDB($pdo, "UPDATE productos SET activo = 0 WHERE id_producto = ?", [$_POST['id_producto']]);
                $mensaje_exito = "Producto eliminado exitosamente";
                break;
                
            case 'cambiar_estado_mesa':
                queryDB($pdo, "UPDATE mesas SET estado = ? WHERE id_mesa = ?", [$_POST['nuevo_estado'], $_POST['id_mesa']]);
                $mensaje_exito = "Estado de mesa actualizado exitosamente";
                break;
                
            case 'nueva_categoria':
                queryDB($pdo, "INSERT INTO categorias (nombre_categoria, descripcion) VALUES (?, ?)", 
                    [$_POST['nombre_categoria'], $_POST['descripcion']]);
                $mensaje_exito = "Categoría agregada exitosamente";
                break;
                
            case 'editar_producto':
                queryDB($pdo, "UPDATE productos SET nombre_producto = ?, id_categoria = ?, precio = ? WHERE id_producto = ?", 
                    [$_POST['nombre_producto'], $_POST['id_categoria'], $_POST['precio'], $_POST['id_producto']]);
                $mensaje_exito = "Producto actualizado exitosamente";
                break;
        }
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
        
    } catch (Exception $e) {
        $mensaje_error = "Error al procesar: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Bar Container</title>
    <link rel="stylesheet" href="/style/style3.css">
</head>
<body>
<!-- Sidebar -->
<nav class="sidebar">
    <div class="logo">
        <a href="/admin" class="logo-link">
            <img src="/assets/unnamed.png" alt="Bar Container" class="logo-img">
            <h1>Bar Container</h1>
        </a>
    </div>
        
    <div class="nav-menu">
        <?php 
        $nav_sections = [
            'Principal' => [
                ['Dashboard', '/admin', 'dashboard', 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z M8 5a2 2 0 012-2h4a2 2 0 012 2v4H8V5z', true]
            ],
            'Inventario' => [
                ['Productos', '/admin/productos', 'productos', 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10'],
                ['Control Stock', '/admin/stock', 'stock', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                ['Categorías', 'admin/categorias', 'categorias', 'M19 11H5m14-7H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z']
            ],
            'Ventas' => [
                ['Ventas', '/admin/ventas', 'ventas', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1'],
                ['Reportes', '#', 'reportes', 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z']
            ],
            'Personal' => [
                ['Usuarios', '/admin/empleados', 'usuarios', 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z'],
                ['Pagos', '/admin/pagos', 'pagos', 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z']
            ],
            'Sistema' => [
                ['Mesas', '/admin/mesas', 'mesas', 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                ['mesas', '#', 'mesas', 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z']
            ]
        ];
        
        foreach ($nav_sections as $section_name => $items): ?>
            <div class="nav-section">
                <div class="nav-section-title"><?php echo $section_name; ?></div>
                <ul>
                    <?php foreach ($items as $item): ?>
                        <li class="nav-item">
                            <a href="<?php echo $item[1]; ?>" class="nav-link <?php echo isset($item[4]) ? 'active' : ''; ?>" onclick="showSection('<?php echo $item[2]; ?>')">
                                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="<?php echo $item[3]; ?>"/>
                                </svg>
                                <?php echo $item[0]; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </div>
</nav>

<!-- Main Content -->
<main class="main-content">
    <!-- Mostrar mensajes -->
    <?php 
    $messages = [
        'mensaje_exito' => ['✅', 'alert-success'],
        'mensaje_error' => ['❌', 'alert-danger'],
        'error_message' => ['❌', 'alert-danger']
    ];
    
    foreach ($messages as $var => $config) {
        if (isset($$var)) {
            echo "<div class='alert {$config[1]}'>{$config[0]} " . $$var . "</div>";
        }
    }
    ?>

    <!-- Dashboard Section -->
    <section id="dashboard" class="section active">
        <div class="header">
            <h1 class="page-title">Dashboard</h1>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <?php 
            $stats = [
                ['Ventas Hoy', '💰', '$' . number_format($ventas_hoy['total_ventas'] ?? 0), '↗️ ' . ($ventas_hoy['num_ventas'] ?? 0) . ' órdenes', 'positive'],
                ['Órdenes Hoy', '📋', $ventas_hoy['num_ventas'] ?? 0, '↗️ Ventas del día', 'positive'],
                ['Productos en Stock', '📦', $productos_info['total_productos'] ?? 0, 
                 ($productos_info['productos_criticos'] ?? 0) > 0 ? '↘️ ' . $productos_info['productos_criticos'] . ' productos críticos' : '✅ Stock normal',
                 ($productos_info['productos_criticos'] ?? 0) > 0 ? 'negative' : 'positive'],
                ['Usuarios Activos', '👥', $usuarios_info['total_usuarios'] ?? 0, '✅ Personal activo', 'positive']
            ];
            
            foreach ($stats as $stat): ?>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title"><?php echo $stat[0]; ?></div>
                        <div class="stat-icon"><?php echo $stat[1]; ?></div>
                    </div>
                    <div class="stat-value"><?php echo $stat[2]; ?></div>
                    <div class="stat-change <?php echo $stat[4]; ?>">
                        <?php echo $stat[3]; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Alerts para productos con stock bajo -->
        <?php foreach ($productos_stock_bajo as $producto): ?>
            <div class="alert alert-warning">
                ⚠️ <strong>Stock Bajo:</strong> <?php echo htmlspecialchars($producto['nombre']); ?> (<?php echo $producto['stock']; ?> unidades restantes)
            </div>
        <?php endforeach; ?>

        <!-- Recent Activity -->
        <div class="content-section">
            <div class="section-header">
                <h2 class="section-title">Actividad Reciente</h2>
            </div>
            
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <?php foreach (['Hora', 'Tipo', 'Descripción', 'Usuario', 'Estado'] as $header): ?>
                                <th><?php echo $header; ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($actividad_reciente)): ?>
                            <?php foreach ($actividad_reciente as $actividad): ?>
                                <tr>
                                    <td><?php echo $actividad['hora']; ?></td>
                                    <td><?php echo $actividad['tipo']; ?></td>
                                    <td><?php echo htmlspecialchars($actividad['descripcion']); ?></td>
                                    <td><?php echo htmlspecialchars($actividad['usuario']); ?></td>
                                    <td><span class="btn btn-sm btn-success"><?php echo $actividad['estado']; ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center;">No hay actividad reciente</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Productos Section -->
    <section id="productos" class="section">
        <div class="header">
            <h1 class="page-title">Gestión de Productos</h1>
            <div class="header-actions">
                <button class="btn btn-secondary">📥 Importar</button>
                <button class="btn btn-primary">➕ Nuevo Producto</button>
            </div>
        </div>

        <div class="content-section">
            <div class="section-header">
                <h2 class="section-title">Agregar Producto</h2>
            </div>
            
            <form class="form-grid" method="POST">
                <input type="hidden" name="action" value="nuevo_producto">
                
                <?php 
                $form_fields = [
                    ['Nombre del Producto', 'text', 'nombre_producto', 'Ej: Cerveza Corona'],
                    ['Precio de Venta (COP)', 'number', 'precio', '5000', 'step="0.01"'],
                    ['Stock Inicial', 'number', 'stock', '50']
                ];
                
                foreach ($form_fields as $field): ?>
                    <div class="form-group">
                        <label class="form-label"><?php echo $field[0]; ?></label>
                        <input type="<?php echo $field[1]; ?>" name="<?php echo $field[2]; ?>" class="form-input" 
                               placeholder="<?php echo $field[3]; ?>" <?php echo isset($field[4]) ? $field[4] : ''; ?> required>
                    </div>
                <?php endforeach; ?>
                
                <div class="form-group">
                    <label class="form-label">Categoría</label>
                    <select name="id_categoria" class="form-select" required>
                        <option value="">Seleccionar categoría</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?php echo $categoria['id_categoria']; ?>">
                                <?php echo htmlspecialchars($categoria['nombre_categoria']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">💾 Guardar Producto</button>
                </div>
            </form>
        </div>

        <div class="content-section">
            <div class="section-header">
                <h2 class="section-title">Lista de Productos</h2>
            </div>
            
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <?php foreach (['Producto', 'Categoría', 'Precio', 'Stock', 'Acciones'] as $header): ?>
                                <th><?php echo $header; ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>    
                    <tbody>
                        <?php if (!empty($lista_productos)): ?>
                            <?php foreach ($lista_productos as $producto): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($producto['nombre_producto']); ?></td>
                                    <td><?php echo htmlspecialchars($producto['categoria_nombre']); ?></td>
                                    <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                                    <td><?php echo $producto['stock']; ?></td>
                                    <td>
                                        <button class="btn btn-secondary" onclick="editProducto(<?php echo $producto['id_producto']; ?>)">✏️ Editar</button>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="eliminar_producto">
                                            <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                                            <button type="submit" class="btn btn-danger">🗑️ Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5">No hay productos registrados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>
</body>
</html>