<?php
// admin_panel.php - Panel principal integrado con PHP y base de datos

// Incluir conexión a la base de datos
require_once 'config/Conexion.php';

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Crear la instancia de conexión
$db = new Database();
$pdo = $db->getConnection();

// Verificar que la conexión PDO existe
if (!$pdo) {
    die("Error: No se pudo establecer conexión con la base de datos.");
}

try {
    // Consultas para el dashboard - CORREGIDAS para tu esquema
    $stmt_ventas_hoy = $pdo->prepare("SELECT COALESCE(SUM(total), 0) as total_ventas, COUNT(*) as num_ventas FROM ventas WHERE DATE(fecha_venta) = CURDATE()");
    $stmt_ventas_hoy->execute();
    $ventas_hoy = $stmt_ventas_hoy->fetch();

    // Productos en stock - CORREGIDO
    $stmt_productos = $pdo->prepare("SELECT COUNT(*) as total_productos, COUNT(CASE WHEN stock <= 5 THEN 1 END) as productos_criticos FROM productos WHERE activo = 1");
    $stmt_productos->execute();
    $productos_info = $stmt_productos->fetch();

    // Usuarios activos - CORREGIDO
    $stmt_usuarios = $pdo->prepare("SELECT COUNT(*) as total_usuarios FROM usuarios WHERE activo = 1");
    $stmt_usuarios->execute();
    $usuarios_info = $stmt_usuarios->fetch();

    // Productos con stock bajo - CORREGIDO
    $stmt_stock_bajo = $pdo->prepare("SELECT nombre_producto as nombre, stock FROM productos WHERE stock <= 5 AND activo = 1 LIMIT 5");
    $stmt_stock_bajo->execute();
    $productos_stock_bajo = $stmt_stock_bajo->fetchAll();

    // Actividad reciente - CORREGIDA
    $stmt_actividad = $pdo->prepare("
        SELECT 'Venta' as tipo, 
               CONCAT('Venta #', v.id_venta) as descripcion, 
               DATE_FORMAT(v.fecha_venta, '%H:%i') as hora, 
               u.nombre as usuario, 
               v.estado as estado
        FROM ventas v 
        JOIN usuarios u ON v.id_usuario = u.id_usuario
        WHERE DATE(v.fecha_venta) = CURDATE()
        ORDER BY v.fecha_venta DESC 
        LIMIT 10
    ");
    $stmt_actividad->execute();
    $actividad_reciente = $stmt_actividad->fetchAll();

    // Lista de productos - CORREGIDA
    $stmt_lista_productos = $pdo->prepare("
        SELECT p.*, c.nombre_categoria as categoria_nombre 
        FROM productos p 
        LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
        WHERE p.activo = 1 
        ORDER BY p.nombre_producto
    ");
    $stmt_lista_productos->execute();
    $lista_productos = $stmt_lista_productos->fetchAll();

    // Categorías - CORREGIDA
    $stmt_categorias = $pdo->prepare("SELECT * FROM categorias ORDER BY nombre_categoria");
    $stmt_categorias->execute();
    $categorias = $stmt_categorias->fetchAll();

    // Ventas recientes para la sección ventas
    $stmt_ventas_recientes = $pdo->prepare("
        SELECT v.*, m.numero_mesa, u.nombre as usuario_nombre
        FROM ventas v
        JOIN mesas m ON v.id_mesa = m.id_mesa
        JOIN usuarios u ON v.id_usuario = u.id_usuario
        ORDER BY v.fecha_venta DESC
        LIMIT 20
    ");
    $stmt_ventas_recientes->execute();
    $ventas_recientes = $stmt_ventas_recientes->fetchAll();

    // Lista de usuarios
    $stmt_lista_usuarios = $pdo->prepare("
        SELECT u.*, r.nombre_rol 
        FROM usuarios u 
        JOIN roles r ON u.id_rol = r.id_rol 
        ORDER BY u.nombre
    ");
    $stmt_lista_usuarios->execute();
    $lista_usuarios = $stmt_lista_usuarios->fetchAll();

    // Lista de roles
    $stmt_roles = $pdo->prepare("SELECT * FROM roles ORDER BY nombre_rol");
    $stmt_roles->execute();
    $roles = $stmt_roles->fetchAll();

    // Mesas disponibles
    $stmt_mesas = $pdo->prepare("SELECT * FROM mesas ORDER BY numero_mesa");
    $stmt_mesas->execute();
    $mesas = $stmt_mesas->fetchAll();

} catch (Exception $e) {
    $error_message = "Error al cargar datos: " . $e->getMessage();
}

// Procesar formulario de nuevo producto - CORREGIDO
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'nuevo_producto') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO productos (nombre_producto, id_categoria, precio, stock, activo) 
            VALUES (?, ?, ?, ?, 1)
        ");
        $stmt->execute([
            $_POST['nombre_producto'],
            $_POST['id_categoria'],
            $_POST['precio'],
            $_POST['stock']
        ]);
        $mensaje_exito = "Producto agregado exitosamente";
        
        // Recargar página para mostrar cambios
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        $mensaje_error = "Error al agregar producto: " . $e->getMessage();
    }
}

// Procesar formulario de nuevo usuario
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'nuevo_usuario') {
    try {
        $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nombre, email, password, telefono, id_rol, activo) 
            VALUES (?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([
            $_POST['nombre'],
            $_POST['email'],
            $password_hash,
            $_POST['telefono'],
            $_POST['id_rol']
        ]);
        $mensaje_exito = "Usuario agregado exitosamente";
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        $mensaje_error = "Error al agregar usuario: " . $e->getMessage();
    }
}

// Procesar actualización de stock
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'actualizar_stock') {
    try {
        $stmt = $pdo->prepare("UPDATE productos SET stock = ? WHERE id_producto = ?");
        $stmt->execute([$_POST['nuevo_stock'], $_POST['id_producto']]);
        $mensaje_exito = "Stock actualizado exitosamente";
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        $mensaje_error = "Error al actualizar stock: " . $e->getMessage();
    }
}

// Procesar nueva mesa
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'nueva_mesa') {
    try {
        $stmt = $pdo->prepare("INSERT INTO mesas (numero_mesa, capacidad, ubicacion, estado) VALUES (?, ?, ?, 'disponible')");
        $stmt->execute([
            $_POST['numero_mesa'],
            $_POST['capacidad'],
            $_POST['ubicacion']
        ]);
        $mensaje_exito = "Mesa agregada exitosamente";
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        $mensaje_error = "Error al agregar mesa: " . $e->getMessage();
    }
}

// Procesar eliminación de producto
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'eliminar_producto') {
    try {
        $stmt = $pdo->prepare("UPDATE productos SET activo = 0 WHERE id_producto = ?");
        $stmt->execute([$_POST['id_producto']]);
        $mensaje_exito = "Producto eliminado exitosamente";
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        $mensaje_error = "Error al eliminar producto: " . $e->getMessage();
    }
}

// Procesar actualización de estado de mesa
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'cambiar_estado_mesa') {
    try {
        $stmt = $pdo->prepare("UPDATE mesas SET estado = ? WHERE id_mesa = ?");
        $stmt->execute([$_POST['nuevo_estado'], $_POST['id_mesa']]);
        $mensaje_exito = "Estado de mesa actualizado exitosamente";
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        $mensaje_error = "Error al actualizar estado de mesa: " . $e->getMessage();
    }
}

// Procesar nueva categoría
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'nueva_categoria') {
    try {
        $stmt = $pdo->prepare("INSERT INTO categorias (nombre_categoria, descripcion) VALUES (?, ?)");
        $stmt->execute([
            $_POST['nombre_categoria'],
            $_POST['descripcion']
        ]);
        $mensaje_exito = "Categoría agregada exitosamente";
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        $mensaje_error = "Error al agregar categoría: " . $e->getMessage();
    }
}

// Procesar edición de producto
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'editar_producto') {
    try {
        $stmt = $pdo->prepare("UPDATE productos SET nombre_producto = ?, id_categoria = ?, precio = ? WHERE id_producto = ?");
        $stmt->execute([
            $_POST['nombre_producto'],
            $_POST['id_categoria'],
            $_POST['precio'],
            $_POST['id_producto']
        ]);
        $mensaje_exito = "Producto actualizado exitosamente";
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        $mensaje_error = "Error al actualizar producto: " . $e->getMessage();
    }
}

// Obtener reportes de ventas por fecha
$stmt_reportes = $pdo->prepare("
    SELECT DATE(fecha_venta) as fecha, 
           COUNT(*) as num_ventas, 
           SUM(total) as total_ventas
    FROM ventas 
    WHERE fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(fecha_venta)
    ORDER BY fecha DESC
");
$stmt_reportes->execute();
$reportes_ventas = $stmt_reportes->fetchAll();

// Obtener productos más vendidos
$stmt_productos_populares = $pdo->prepare("
    SELECT p.nombre_producto, 
           SUM(dv.cantidad) as total_vendido,
           SUM(dv.subtotal) as ingresos_totales
    FROM detalle_ventas dv
    JOIN productos p ON dv.id_producto = p.id_producto
    JOIN ventas v ON dv.id_venta = v.id_venta
    WHERE v.fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY p.id_producto, p.nombre_producto
    ORDER BY total_vendido DESC
    LIMIT 10
");
$stmt_productos_populares->execute();
$productos_populares = $stmt_productos_populares->fetchAll();
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
            <div class="nav-section">
                <div class="nav-section-title">Principal</div>
                <ul>
                    <li class="nav-item">
                        <a href="/admin" class="nav-link active" onclick="showSection('dashboard')">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v4H8V5z"/>
                            </svg>
                            Dashboard
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Inventario</div>
                <ul>
                    <li class="nav-item">
                        <a href="/admin/productos" class="nav-link" onclick="showSection('productos')">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                            </svg>
                            Productos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/stock" class="nav-link" onclick="showSection('stock')">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Control Stock
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" onclick="showSection('categorias')">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14-7H5a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2z"/>
                            </svg>
                            Categorías
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Ventas</div>
                <ul>
                    <li class="nav-item">
                        <a href="/admin/ventas" class="nav-link" onclick="showSection('ventas')">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                            Ventas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" onclick="showSection('reportes')">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Reportes
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Personal</div>
                <ul>
                    <li class="nav-item">
                        <a href="/admin/empleados" class="nav-link" onclick="showSection('usuarios')">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                            </svg>
                            Usuarios
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/pagos" class="nav-link" onclick="showSection('pagos')">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Pagos
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Sistema</div>
                <ul>
                    <li class="nav-item">
                        <a href="#" class="nav-link" onclick="showSection('mesas')">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            Mesas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" onclick="showSection('configuracion')">
                            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Configuración
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Mostrar mensajes -->
        <?php if (isset($mensaje_exito)): ?>
            <div class="alert alert-success">✅ <?php echo $mensaje_exito; ?></div>
        <?php endif; ?>
        
        <?php if (isset($mensaje_error)): ?>
            <div class="alert alert-danger">❌ <?php echo $mensaje_error; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">❌ <?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Dashboard Section -->
        <section id="dashboard" class="section active">
            <div class="header">
                <h1 class="page-title">Dashboard</h1>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Ventas Hoy</div>
                        <div class="stat-icon">💰</div>
                    </div>
                    <div class="stat-value">$<?php echo number_format($ventas_hoy['total_ventas'] ?? 0); ?></div>
                    <div class="stat-change positive">
                        ↗️ <?php echo $ventas_hoy['num_ventas'] ?? 0; ?> órdenes
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Órdenes Hoy</div>
                        <div class="stat-icon">📋</div>
                    </div>
                    <div class="stat-value"><?php echo $ventas_hoy['num_ventas'] ?? 0; ?></div>
                    <div class="stat-change positive">
                        ↗️ Ventas del día
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Productos en Stock</div>
                        <div class="stat-icon">📦</div>
                    </div>
                    <div class="stat-value"><?php echo $productos_info['total_productos'] ?? 0; ?></div>
                    <div class="stat-change <?php echo ($productos_info['productos_criticos'] ?? 0) > 0 ? 'negative' : 'positive'; ?>">
                        <?php if (($productos_info['productos_criticos'] ?? 0) > 0): ?>
                            ↘️ <?php echo $productos_info['productos_criticos']; ?> productos críticos
                        <?php else: ?>
                            ✅ Stock normal
                        <?php endif; ?>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Usuarios Activos</div>
                        <div class="stat-icon">👥</div>
                    </div>
                    <div class="stat-value"><?php echo $usuarios_info['total_usuarios'] ?? 0; ?></div>
                    <div class="stat-change positive">
                        ✅ Personal activo
                    </div>
                </div>
            </div>

            <!-- Alerts para productos con stock bajo -->
            <?php if (!empty($productos_stock_bajo)): ?>
                <?php foreach ($productos_stock_bajo as $producto): ?>
                    <div class="alert alert-warning">
                        ⚠️ <strong>Stock Bajo:</strong> <?php echo htmlspecialchars($producto['nombre']); ?> (<?php echo $producto['stock']; ?> unidades restantes)
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Recent Activity -->
            <div class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Actividad Reciente</h2>
                </div>
                
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Tipo</th>
                                <th>Descripción</th>
                                <th>Usuario</th>
                                <th>Estado</th>
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
                                <tr>
                                    <td colspan="5" style="text-align: center;">No hay actividad reciente</td>
                                </tr>
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
                    
                    <div class="form-group">
                        <label class="form-label">Nombre del Producto</label>
                        <input type="text" name="nombre_producto" class="form-input" placeholder="Ej: Cerveza Corona" required>
                    </div>
                    
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
                        <label class="form-label">Precio de Venta (COP)</label>
                        <input type="number" name="precio" class="form-input" placeholder="5000" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Stock Inicial</label>
                        <input type="number" name="stock" class="form-input" placeholder="50" required>
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
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Acciones</th>
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
                                <tr>
                                    <td colspan="5">No hay productos registrados.</td>
                                </tr>
                            <?php endif; ?>                 