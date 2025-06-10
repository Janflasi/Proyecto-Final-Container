<?php

require_once __DIR__ . '/../Router.php';
require_once __DIR__ . '/../config/Conexion.php';


class DashboardController {

    public static function index(Router $router) {
        $router->render('dashboard/admin', [
            "title" => "Dashboard - Admin"
        ]);
    }

    // CONTROL DE STOCK - MÉTODO PRINCIPAL
    public static function controlStock(Router $router) {
        $database = new Database();
        $pdo = $database->getConnection();
        
        // Manejar peticiones AJAX/POST para el control de stock
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            header('Content-Type: application/json');
            
            try {
                $response = ['success' => false, 'message' => 'Acción no válida'];
                
                switch ($_POST['action']) {
                    case 'obtener_productos':
                        $busqueda = $_POST['busqueda'] ?? '';
                        echo json_encode(self::obtenerProductosStock($pdo, $busqueda));
                        exit;
                        
                    case 'obtener_estadisticas':
                        echo json_encode(self::obtenerEstadisticasStock($pdo));
                        exit;
                        
                    case 'ajustar_stock':
                        $resultado = self::ajustarStock($pdo, 
                            $_POST['id_producto'] ?? 0,
                            $_POST['nuevo_stock'] ?? 0,
                            $_POST['motivo'] ?? 'Ajuste manual'
                        );
                        echo json_encode(['success' => $resultado]);
                        exit;
                        
                    case 'obtener_alertas':
                        echo json_encode(self::obtenerAlertasStock($pdo));
                        exit;
                        
                    case 'obtener_movimientos':
                        echo json_encode(self::obtenerMovimientosStock($pdo));
                        exit;
                }
                
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }
        }
        
        // Renderizar la vista con datos iniciales
        $productos = self::obtenerProductosStock($pdo);
        $estadisticas = self::obtenerEstadisticasStock($pdo);
        $alertas = self::obtenerAlertasStock($pdo);
        $movimientos = self::obtenerMovimientosStock($pdo);
        
        $router->render('dashboard/control_stock', [
            "title" => "Control de Stock",
            "productos" => $productos,
            "estadisticas" => $estadisticas,
            "alertas" => $alertas,
            "movimientos" => $movimientos
        ]);
    }

    // Método para compatibilidad con la ruta POST separada
    public static function updateStock(Router $router) {
        // Redirigir al método principal
        self::controlStock($router);
    }

    // FUNCIONES AUXILIARES PARA CONTROL DE STOCK
    private static function obtenerProductosStock($pdo, $busqueda = '') {
        try {
            $sql = "SELECT p.*, c.nombre_categoria FROM productos p 
                    INNER JOIN categorias c ON p.id_categoria = c.id_categoria 
                    WHERE p.activo = 1";
            
            if (!empty($busqueda)) {
                $sql .= " AND (p.nombre_producto LIKE :busqueda OR p.id_producto LIKE :busqueda)";
            }
            
            $sql .= " ORDER BY p.nombre_producto";
            
            $stmt = $pdo->prepare($sql);
            if (!empty($busqueda)) {
                $stmt->bindValue(':busqueda', '%' . $busqueda . '%');
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    private static function obtenerEstadisticasStock($pdo) {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        COUNT(CASE WHEN stock > 20 THEN 1 END) as disponible,
                        COUNT(CASE WHEN stock BETWEEN 10 AND 20 THEN 1 END) as bajo,
                        COUNT(CASE WHEN stock < 10 THEN 1 END) as critico
                    FROM productos WHERE activo = 1";
            
            $stmt = $pdo->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return ['total' => 0, 'disponible' => 0, 'bajo' => 0, 'critico' => 0];
        }
    }
    
    private static function ajustarStock($pdo, $idProducto, $nuevoStock, $motivo = 'Ajuste manual') {
        try {
            $pdo->beginTransaction();
            
            // Obtener información actual del producto
            $sql = "SELECT stock, nombre_producto FROM productos WHERE id_producto = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $idProducto, PDO::PARAM_INT);
            $stmt->execute();
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$producto) {
                $pdo->rollback();
                return false;
            }
            
            // Actualizar el stock
            $sql = "UPDATE productos SET stock = :stock WHERE id_producto = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':stock', $nuevoStock, PDO::PARAM_INT);
            $stmt->bindParam(':id', $idProducto, PDO::PARAM_INT);
            $stmt->execute();
            
            // Registrar el movimiento en gastos
            $diferencia = $nuevoStock - $producto['stock'];
            $concepto = $motivo . " - " . $producto['nombre_producto'] . " (" . ($diferencia > 0 ? "+" : "") . $diferencia . ")";
            
            $sql = "INSERT INTO gastos (fecha_gasto, concepto, monto, categoria_gasto, descripcion) 
                    VALUES (NOW(), :concepto, 0, 'Ajuste Stock', :descripcion)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':concepto', $concepto);
            $stmt->bindParam(':descripcion', $motivo);
            $stmt->execute();
            
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollback();
            return false;
        }
    }
    
    private static function obtenerAlertasStock($pdo) {
        try {
            $sql = "SELECT nombre_producto, stock, 
                    CASE 
                        WHEN stock < 5 THEN 'critico'
                        WHEN stock < 15 THEN 'bajo'
                        ELSE 'normal'
                    END as nivel
                    FROM productos 
                    WHERE activo = 1 AND stock < 15 
                    ORDER BY stock ASC";
            
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    private static function obtenerMovimientosStock($pdo) {
        try {
            $sql = "SELECT concepto, fecha_gasto, descripcion 
                    FROM gastos 
                    WHERE categoria_gasto = 'Ajuste Stock' 
                    ORDER BY fecha_gasto DESC, id_gasto DESC 
                    LIMIT 10";
            
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public static function empleados(Router $router) {
        $router->render('dashboard/Empleados', [
            "title" => "Gestión de Empleados"
        ]);
    }

    public static function pagosEmpleados(Router $router) {
        $router->render('dashboard/PagosEmpleados', [
            "title" => "Pagos de Empleados"
        ]);
    }

    // GESTIÓN DE PRODUCTOS - FUNCIONES COMPLETAS CON IMAGEN
    public static function productos(Router $router) {
        $database = new Database();
        $pdo = $database->getConnection();
        
        // Manejar peticiones AJAX
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            header('Content-Type: application/json');
            
            try {
                $response = ['success' => false, 'message' => 'Acción no válida'];
                
                switch ($_POST['action']) {
                    case 'add':
                        $response['success'] = self::addProducto($pdo, $_POST, $_FILES);
                        $response['message'] = $response['success'] ? 'Producto agregado' : 'Error al agregar';
                        break;
                        
                    case 'update':
                        $response['success'] = self::updateProductoData($pdo, $_POST['id'], $_POST, $_FILES);
                        $response['message'] = $response['success'] ? 'Producto actualizado' : 'Error al actualizar';
                        break;
                        
                    case 'delete':
                        $response['success'] = self::deleteProductoData($pdo, $_POST['id']);
                        $response['message'] = $response['success'] ? 'Producto eliminado' : 'Error al eliminar';
                        break;
                        
                    case 'get':
                        $response = self::getProductoById($pdo, $_POST['id']);
                        break;
                }
                
                echo json_encode($response);
                exit;
                
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }
        }
        
        // Obtener datos para la vista
        $categoria_filtro = $_GET['categoria'] ?? 'all';
        $busqueda = $_GET['search'] ?? '';
        $productos = self::getProductos($pdo, $categoria_filtro, $busqueda);
        $categorias = self::getCategorias($pdo);
        
        $router->render('dashboard/Products_admin', [
            "title" => "Gestión de Productos",
            "productos" => $productos,
            "categorias" => $categorias,
            "categoria_filtro" => $categoria_filtro,
            "busqueda" => $busqueda
        ]);
    }

    // FUNCIÓN PARA MANEJAR CARGA DE IMAGEN
    private static function manejarImagenProducto($imagen, $nombre_producto = '') {
        if (!isset($imagen) || $imagen['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        // Validar tipo de archivo
        $tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($imagen['type'], $tipos_permitidos)) {
            throw new Exception('Tipo de archivo no permitido. Use JPG, PNG o GIF.');
        }
        
        // Validar tamaño (máximo 5MB)
        if ($imagen['size'] > 5 * 1024 * 1024) {
            throw new Exception('El archivo es demasiado grande. Máximo 5MB.');
        }
        
        // Crear directorio si no existe
        $directorio = __DIR__ . '/../public/images/productos/';
        if (!file_exists($directorio)) {
            mkdir($directorio, 0777, true);
        }
        
        // Generar nombre único para el archivo
        $extension = pathinfo($imagen['name'], PATHINFO_EXTENSION);
        $nombre_limpio = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $nombre_producto));
        $nombre_archivo = $nombre_limpio . '_' . uniqid() . '.' . $extension;
        $ruta_completa = $directorio . $nombre_archivo;
        
        // Mover archivo
        if (move_uploaded_file($imagen['tmp_name'], $ruta_completa)) {
            return 'images/productos/' . $nombre_archivo;
        } else {
            throw new Exception('Error al subir la imagen.');
        }
    }

    // Funciones auxiliares para productos MODIFICADAS PARA IMAGEN
    private static function getProductos($pdo, $categoria = null, $search = null) {
        $sql = "SELECT p.*, c.nombre_categoria FROM productos p JOIN categorias c ON p.id_categoria = c.id_categoria WHERE p.activo = 1";
        $params = [];
        
        if ($categoria && $categoria != 'all') {
            $sql .= " AND c.nombre_categoria = ?";
            $params[] = $categoria;
        }
        if ($search) {
            $sql .= " AND p.nombre_producto LIKE ?";
            $params[] = "%$search%";
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private static function getCategorias($pdo) {
        return $pdo->query("SELECT * FROM categorias ORDER BY nombre_categoria")->fetchAll(PDO::FETCH_ASSOC);
    }

    private static function addProducto($pdo, $data, $files = null) {
        try {
            $imagen_ruta = null;
            
            // Manejar imagen si se subió
            if ($files && isset($files['imagen']) && $files['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
                $imagen_ruta = self::manejarImagenProducto($files['imagen'], $data['nombre']);
            }
            
            $stmt = $pdo->prepare("INSERT INTO productos (nombre_producto, precio, stock, id_categoria, imagen) VALUES (?, ?, ?, ?, ?)");
            return $stmt->execute([$data['nombre'], $data['precio'], $data['stock'], $data['categoria'], $imagen_ruta]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private static function updateProductoData($pdo, $id, $data, $files = null) {
        try {
            // Obtener imagen actual
            $stmt = $pdo->prepare("SELECT imagen FROM productos WHERE id_producto = ?");
            $stmt->execute([$id]);
            $producto_actual = $stmt->fetch(PDO::FETCH_ASSOC);
            $imagen_ruta = $producto_actual['imagen'];
            
            // Manejar nueva imagen si se subió
            if ($files && isset($files['imagen']) && $files['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
                // Eliminar imagen anterior si existe
                if ($imagen_ruta && file_exists(__DIR__ . '/../public/' . $imagen_ruta)) {
                    unlink(__DIR__ . '/../public/' . $imagen_ruta);
                }
                $imagen_ruta = self::manejarImagenProducto($files['imagen'], $data['nombre']);
            }
            
            $stmt = $pdo->prepare("UPDATE productos SET nombre_producto = ?, precio = ?, stock = ?, id_categoria = ?, imagen = ? WHERE id_producto = ?");
            return $stmt->execute([$data['nombre'], $data['precio'], $data['stock'], $data['categoria'], $imagen_ruta, $id]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private static function deleteProductoData($pdo, $id) {
        try {
            // Obtener imagen antes de eliminar
            $stmt = $pdo->prepare("SELECT imagen FROM productos WHERE id_producto = ?");
            $stmt->execute([$id]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Eliminar imagen si existe
            if ($producto && $producto['imagen'] && file_exists(__DIR__ . '/../public/' . $producto['imagen'])) {
                unlink(__DIR__ . '/../public/' . $producto['imagen']);
            }
            
            return $pdo->prepare("UPDATE productos SET activo = 0 WHERE id_producto = ?")->execute([$id]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private static function getProductoById($pdo, $id) {
        $stmt = $pdo->prepare("SELECT * FROM productos WHERE id_producto = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Métodos para rutas específicas (compatibilidad)
    public static function createProducto(Router $router) {
        // Redirigir al método principal
        self::productos($router);
    }

    public static function updateProducto(Router $router) {
        // Redirigir al método principal
        self::productos($router);
    }

    public static function deleteProducto(Router $router) {
        // Redirigir al método principal
        self::productos($router);
    }

    // GESTIÓN DE CATEGORÍAS - MÉTODOS CORREGIDOS
    public static function categorias(Router $router) {
        $database = new Database();
        $pdo = $database->getConnection();
        
        $mensaje = '';
        $tipo_mensaje = '';
        
        // Procesar formularios POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['accion'])) {
                switch ($_POST['accion']) {
                    case 'agregar':
                        $resultado = self::procesarAgregarCategoria($pdo, $_POST);
                        $mensaje = $resultado['mensaje'];
                        $tipo_mensaje = $resultado['tipo'];
                        break;
                        
                    case 'editar':
                        $resultado = self::procesarEditarCategoria($pdo, $_POST);
                        $mensaje = $resultado['mensaje'];
                        $tipo_mensaje = $resultado['tipo'];
                        break;
                        
                    case 'eliminar':
                        $resultado = self::procesarEliminarCategoria($pdo, $_POST);
                        $mensaje = $resultado['mensaje'];
                        $tipo_mensaje = $resultado['tipo'];
                        break;
                }
            }
        }
        
        // Manejar mensajes de GET (redirecciones)
        if (isset($_GET['success'])) {
            $mensaje = $_GET['success'];
            $tipo_mensaje = 'success';
        } elseif (isset($_GET['error'])) {
            $mensaje = $_GET['error'];
            $tipo_mensaje = 'error';
        }
        
        // Obtener categoría para editar
        $categoria_editar = null;
        if (isset($_GET['editar'])) {
            $id_editar = $_GET['editar'];
            try {
                $stmt = $pdo->prepare("SELECT * FROM categorias WHERE id_categoria = ?");
                $stmt->execute([$id_editar]);
                $categoria_editar = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                $mensaje = "Error al cargar categoría: " . $e->getMessage();
                $tipo_mensaje = "error";
            }
        }
        
        // Obtener todas las categorías
        try {
            $stmt = $pdo->query("SELECT * FROM categorias ORDER BY nombre_categoria");
            $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            $categorias = [];
            $mensaje = "Error al cargar categorías: " . $e->getMessage();
            $tipo_mensaje = "error";
        }

        $router->render('dashboard/categorias_Dashboard', [
            "title" => "Gestión de Categorías",
            "categorias" => $categorias,
            "mensaje" => $mensaje,
            "tipo_mensaje" => $tipo_mensaje,
            "categoria_editar" => $categoria_editar
        ]);
    }

    // Métodos auxiliares para categorías - CORREGIDOS PARA EVITAR DUPLICADOS
    private static function procesarAgregarCategoria($pdo, $data) {
        $nombre = trim($data['nombre'] ?? '');
        $descripcion = trim($data['descripcion'] ?? '');
        
        if (empty($nombre)) {
            return ['mensaje' => 'El nombre de la categoría es obligatorio', 'tipo' => 'error'];
        }
        
        try {
            // Verificar si ya existe una categoría con ese nombre (case insensitive)
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM categorias WHERE UPPER(nombre_categoria) = UPPER(?)");
            $stmt_check->execute([$nombre]);
            $existe = $stmt_check->fetchColumn();
            
            if ($existe > 0) {
                return ['mensaje' => 'Ya existe una categoría con el nombre "' . $nombre . '"', 'tipo' => 'error'];
            }
            
            // Si no existe, proceder a insertar
            $stmt = $pdo->prepare("INSERT INTO categorias (nombre_categoria, descripcion) VALUES (?, ?)");
            $resultado = $stmt->execute([$nombre, $descripcion]);
            
            if ($resultado) {
                return ['mensaje' => 'Categoría agregada exitosamente', 'tipo' => 'success'];
            } else {
                return ['mensaje' => 'Error al insertar la categoría', 'tipo' => 'error'];
            }
            
        } catch(PDOException $e) {
            // Verificar si es error de duplicado específicamente
            if ($e->getCode() == 23000 && strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return ['mensaje' => 'Ya existe una categoría con ese nombre. Por favor, elige otro nombre.', 'tipo' => 'error'];
            }
            return ['mensaje' => 'Error en la base de datos: ' . $e->getMessage(), 'tipo' => 'error'];
        } catch(Exception $e) {
            return ['mensaje' => 'Error inesperado: ' . $e->getMessage(), 'tipo' => 'error'];
        }
    }

    private static function procesarEditarCategoria($pdo, $data) {
        $id = $data['id'] ?? '';
        $nombre = trim($data['nombre'] ?? '');
        $descripcion = trim($data['descripcion'] ?? '');
        
        if (empty($nombre) || empty($id)) {
            return ['mensaje' => 'El nombre de la categoría y el ID son obligatorios', 'tipo' => 'error'];
        }
        
        try {
            // Verificar si ya existe otra categoría con ese nombre (excluyendo la actual, case insensitive)
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM categorias WHERE UPPER(nombre_categoria) = UPPER(?) AND id_categoria != ?");
            $stmt_check->execute([$nombre, $id]);
            $existe = $stmt_check->fetchColumn();
            
            if ($existe > 0) {
                return ['mensaje' => 'Ya existe otra categoría con el nombre "' . $nombre . '"', 'tipo' => 'error'];
            }
            
            // Si no existe conflicto, proceder a actualizar
            $stmt = $pdo->prepare("UPDATE categorias SET nombre_categoria = ?, descripcion = ? WHERE id_categoria = ?");
            $resultado = $stmt->execute([$nombre, $descripcion, $id]);
            
            if ($resultado) {
                return ['mensaje' => 'Categoría actualizada exitosamente', 'tipo' => 'success'];
            } else {
                return ['mensaje' => 'Error al actualizar la categoría', 'tipo' => 'error'];
            }
            
        } catch(PDOException $e) {
            // Verificar si es error de duplicado específicamente
            if ($e->getCode() == 23000 && strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return ['mensaje' => 'Ya existe otra categoría con ese nombre. Por favor, elige otro nombre.', 'tipo' => 'error'];
            }
            return ['mensaje' => 'Error en la base de datos: ' . $e->getMessage(), 'tipo' => 'error'];
        } catch(Exception $e) {
            return ['mensaje' => 'Error inesperado: ' . $e->getMessage(), 'tipo' => 'error'];
        }
    }

    private static function procesarEliminarCategoria($pdo, $data) {
        $id = $data['id'] ?? '';
        
        if (!empty($id)) {
            try {
                // Verificar si tiene productos asociados
                $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE id_categoria = ? AND activo = 1");
                $stmt_check->execute([$id]);
                $productos_asociados = $stmt_check->fetchColumn();
                
                if ($productos_asociados > 0) {
                    return ['mensaje' => 'No se puede eliminar la categoría porque tiene productos asociados', 'tipo' => 'error'];
                }
                
                // Eliminar la categoría
                $stmt = $pdo->prepare("DELETE FROM categorias WHERE id_categoria = ?");
                $stmt->execute([$id]);
                return ['mensaje' => 'Categoría eliminada exitosamente', 'tipo' => 'success'];
            } catch(PDOException $e) {
                return ['mensaje' => 'Error al eliminar categoría: ' . $e->getMessage(), 'tipo' => 'error'];
            }
        } else {
            return ['mensaje' => 'ID de categoría no válido', 'tipo' => 'error'];
        }
    }

    // Métodos de compatibilidad con rutas separadas
    public static function createCategoria(Router $router) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $database = new Database();
            $pdo = $database->getConnection();
            
            $resultado = self::procesarAgregarCategoria($pdo, $_POST);
            
            if ($resultado['tipo'] == 'success') {
                header('Location: /admin/categorias?success=' . urlencode($resultado['mensaje']));
            } else {
                header('Location: /admin/categorias?error=' . urlencode($resultado['mensaje']));
            }
            exit;
        }
        
        header('Location: /admin/categorias');
        exit;
    }

    public static function updateCategoria(Router $router) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $database = new Database();
            $pdo = $database->getConnection();
            
            $resultado = self::procesarEditarCategoria($pdo, $_POST);
            
            if ($resultado['tipo'] == 'success') {
                header('Location: /admin/categorias?success=' . urlencode($resultado['mensaje']));
            } else {
                header('Location: /admin/categorias?error=' . urlencode($resultado['mensaje']));
            }
            exit;
        }
        
        header('Location: /admin/categorias');
        exit;
    }

    public static function deleteCategoria(Router $router) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $database = new Database();
            $pdo = $database->getConnection();
            
            $resultado = self::procesarEliminarCategoria($pdo, $_POST);
            
            if ($resultado['tipo'] == 'success') {
                header('Location: /admin/categorias?success=' . urlencode($resultado['mensaje']));
            } else {
                header('Location: /admin/categorias?error=' . urlencode($resultado['mensaje']));
            }
            exit;
        }
        
        header('Location: /admin/categorias');
        exit;
    }

    public static function reportes(Router $router) {
        $router->render('dashboard/reportes_control', [
            "title" => "Reportes y Control"
        ]);
    }
    public static function mesas(Router $router) {
        $router->render('dashboard/admin_mesas', [
            "title" => "Mesas"
        ]);
    }

    public static function ventasControl(Router $router) {
        $router->render('dashboard/ventas_control', [
            "title" => "Control de Ventas"
        ]);
    }

    public static function configuracion(Router $router) {
        $router->render('dashboard/configuracion', [
            "title" => "Configuración del Sistema"
        ]);
    }

    public static function seguridad(Router $router) {
        $router->render('dashboard/seguridad', [
            "title" => "Seguridad del Sistema"
        ]);
    }
    
}

?>