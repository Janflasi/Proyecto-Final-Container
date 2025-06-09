<?php
require_once __DIR__ . '/../Router.php';
require_once __DIR__ . '/../config/conexion.php';

class MeseroController {
    
    public static function index(Router $router) {
        // Obtener la conexión usando el método interno
        $pdo = self::getConnection();

        // Obtener datos iniciales
        $productos = self::obtenerProductos($pdo);
        $mesas = self::obtenerMesas($pdo);
        $id_usuario = $_SESSION['id_usuario'] ?? 1;

        $router->render('Mesero/mesero', [
            "title" => "Panel Mesero",
            "productos" => $productos,
            "mesas" => $mesas,
            "id_usuario" => $id_usuario
        ]);
    }

    public static function sign(Router $router) {
        $router->render('mesero/sign', [
            "title" => "Registro de Actividad"
        ]);
    }

    public static function processSign(Router $router) {
        // Aquí puedes procesar el registro de actividad del mesero
        // Por ejemplo: registrar entrada/salida, break, etc.
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Procesar los datos del formulario
            // Redirigir o mostrar respuesta
            header('Location: /mesero');
            exit;
        }
    }

    public static function mesas(Router $router) {
        $router->render('mesero/mesas', [
            "title" => "Gestión de Mesas"
        ]);
    }

    public static function pedidos(Router $router) {
        $router->render('mesero/pedidos', [
            "title" => "Pedidos"
        ]);
    }

    public static function crearPedido(Router $router) {
        // Método para crear pedidos desde el formulario de pedidos
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Procesar la creación del pedido
            // Aquí puedes usar las mismas funciones que en processAction
            $pdo = self::getConnection();
            
            try {
                // Lógica para crear el pedido
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Pedido creado correctamente']);
            } catch (Exception $e) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }

    // Método para obtener la conexión a la base de datos
    private static function getConnection() {
        $host = 'localhost';
        $dbname = 'container_bar';
        $username = 'root';
        $password = '';

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch(PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    // Método para procesar las acciones AJAX
    public static function processAction(Router $router) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        // Obtener la conexión
        $pdo = self::getConnection();

        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
        
        try {
            switch ($action) {
                case 'agregar_producto':
                    self::agregarAlCarrito($pdo, $input['id_mesa'], $input['id_usuario'], $input['id_producto']);
                    echo json_encode(['success' => true]);
                    break;
                    
                case 'modificar_cantidad':
                    self::modificarCantidadCarrito($pdo, $input['id_producto'], $input['id_mesa'], $input['id_usuario'], $input['cantidad']);
                    echo json_encode(['success' => true]);
                    break;
                    
                case 'procesar_venta':
                    $id_venta = self::procesarVenta($pdo, $input['id_mesa'], $input['id_usuario']);
                    echo json_encode(['success' => true, 'id_venta' => $id_venta]);
                    break;
                    
                case 'pagar_venta':
                    self::pagarVenta($pdo, $input['id_venta']);
                    echo json_encode(['success' => true]);
                    break;
                    
                case 'limpiar_carrito':
                    $stmt = $pdo->prepare("DELETE FROM carrito WHERE id_mesa = ? AND id_usuario = ?");
                    $stmt->execute([$input['id_mesa'], $input['id_usuario']]);
                    echo json_encode(['success' => true]);
                    break;
                    
                default:
                    echo json_encode(['error' => 'Acción no válida']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // Método para obtener el carrito via AJAX
    public static function getCarrito(Router $router) {
        if (!isset($_GET['id_mesa']) || !isset($_GET['id_usuario'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Parámetros faltantes']);
            return;
        }

        // Obtener la conexión
        $pdo = self::getConnection();
        try {
            $carrito = self::obtenerCarrito($pdo, $_GET['id_mesa'], $_GET['id_usuario']);
            header('Content-Type: application/json');
            echo json_encode($carrito);
        } catch(PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // Método para obtener cuentas pendientes
    public static function getCuentas(Router $router) {
        // Obtener la conexión
        $pdo = self::getConnection();
        try {
            $cuentas = self::obtenerVentasPendientes($pdo);
            header('Content-Type: application/json');
            echo json_encode($cuentas);
        } catch(PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // Funciones auxiliares
    private static function obtenerProductos($pdo) {
        $stmt = $pdo->prepare("SELECT p.*, c.nombre_categoria FROM productos p JOIN categorias c ON p.id_categoria = c.id_categoria WHERE p.activo = 1 ORDER BY c.nombre_categoria, p.nombre_producto");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private static function obtenerMesas($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM mesas ORDER BY numero_mesa");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private static function obtenerCarrito($pdo, $id_mesa, $id_usuario) {
        $stmt = $pdo->prepare("SELECT c.*, p.nombre_producto FROM carrito c JOIN productos p ON c.id_producto = p.id_producto WHERE c.id_mesa = ? AND c.id_usuario = ?");
        $stmt->execute([$id_mesa, $id_usuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private static function agregarAlCarrito($pdo, $id_mesa, $id_usuario, $id_producto) {
        $stmt = $pdo->prepare("SELECT precio FROM productos WHERE id_producto = ?");
        $stmt->execute([$id_producto]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare("SELECT * FROM carrito WHERE id_mesa = ? AND id_usuario = ? AND id_producto = ?");
        $stmt->execute([$id_mesa, $id_usuario, $id_producto]);
        
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("UPDATE carrito SET cantidad = cantidad + 1 WHERE id_mesa = ? AND id_usuario = ? AND id_producto = ?");
            $stmt->execute([$id_mesa, $id_usuario, $id_producto]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO carrito (id_mesa, id_usuario, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, 1, ?)");
            $stmt->execute([$id_mesa, $id_usuario, $id_producto, $producto['precio']]);
        }
    }

    private static function modificarCantidadCarrito($pdo, $id_producto, $id_mesa, $id_usuario, $nueva_cantidad) {
        if ($nueva_cantidad <= 0) {
            $stmt = $pdo->prepare("DELETE FROM carrito WHERE id_producto = ? AND id_mesa = ? AND id_usuario = ?");
            $stmt->execute([$id_producto, $id_mesa, $id_usuario]);
        } else {
            $stmt = $pdo->prepare("UPDATE carrito SET cantidad = ? WHERE id_producto = ? AND id_mesa = ? AND id_usuario = ?");
            $stmt->execute([$nueva_cantidad, $id_producto, $id_mesa, $id_usuario]);
        }
    }

    private static function procesarVenta($pdo, $id_mesa, $id_usuario) {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("SELECT SUM(cantidad * precio_unitario) as total FROM carrito WHERE id_mesa = ? AND id_usuario = ?");
            $stmt->execute([$id_mesa, $id_usuario]);
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $stmt = $pdo->prepare("INSERT INTO ventas (id_mesa, id_usuario, total) VALUES (?, ?, ?)");
            $stmt->execute([$id_mesa, $id_usuario, $total]);
            $id_venta = $pdo->lastInsertId();
            
            $stmt = $pdo->prepare("SELECT * FROM carrito WHERE id_mesa = ? AND id_usuario = ?");
            $stmt->execute([$id_mesa, $id_usuario]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($items as $item) {
                $subtotal = $item['cantidad'] * $item['precio_unitario'];
                $stmt = $pdo->prepare("INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$id_venta, $item['id_producto'], $item['cantidad'], $item['precio_unitario'], $subtotal]);
            }
            
            $stmt = $pdo->prepare("DELETE FROM carrito WHERE id_mesa = ? AND id_usuario = ?");
            $stmt->execute([$id_mesa, $id_usuario]);
            
            $pdo->commit();
            return $id_venta;
        } catch (Exception $e) {
            $pdo->rollback();
            throw $e;
        }
    }

    private static function obtenerVentasPendientes($pdo) {
        $stmt = $pdo->prepare("SELECT v.*, m.numero_mesa FROM ventas v JOIN mesas m ON v.id_mesa = m.id_mesa WHERE v.estado = 'pendiente' ORDER BY v.fecha_venta DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private static function pagarVenta($pdo, $id_venta) {
        $stmt = $pdo->prepare("UPDATE ventas SET estado = 'completada' WHERE id_venta = ?");
        $stmt->execute([$id_venta]);
    }
}
?>