<?php
require_once __DIR__ . '/../Router.php';
require_once __DIR__ . '/../config/conexion.php';

class AuthController {
    
    public static function login(Router $router) {
        // Redirigir si ya está logueado
        if (self::isLoggedIn()) {
            if ($_SESSION['usuario_rol'] == 1) {
                header('Location: /dashboard');
            } else {
                header('Location: /pedidos');
            }
            exit();
        }
        
        $router->render('auth/login', [
            "title" => "Iniciar Sesión"
        ]);
    }
    
    public static function authenticate(Router $router) {
        session_start();
        
        $error = '';
        $email = trim($_POST['correo'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $error = 'Por favor, complete todos los campos.';
        } else {
            try {
                // Crear conexión a BD
                $database = new Database();
                $pdo = $database->getConnection();
                
                // Consulta para buscar el usuario por email
                $stmt = $pdo->prepare("SELECT u.id_usuario, u.nombre, u.email, u.password, u.id_rol, r.nombre_rol 
                                      FROM usuarios u 
                                      INNER JOIN roles r ON u.id_rol = r.id_rol 
                                      WHERE u.email = ? AND u.activo = 1");
                $stmt->execute([$email]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($usuario) {
                    // Verificar contraseña (usando SHA2 como en tu BD)
                    $password_hash = hash('sha256', $password);
                    
                    if ($password_hash === $usuario['password']) {
                        // Login exitoso - iniciar sesión
                        session_start();
                        $_SESSION['usuario_id'] = $usuario['id_usuario'];
                        $_SESSION['usuario_nombre'] = $usuario['nombre'];
                        $_SESSION['usuario_email'] = $usuario['email'];
                        $_SESSION['usuario_rol'] = $usuario['id_rol'];
                        $_SESSION['nombre_rol'] = $usuario['nombre_rol'];
                        
                        // Redirigir según el rol
                        if ($usuario['id_rol'] == 1) { // Administrador
                            header('Location: /dashboard');
                        } else { // Mesero
                            header('Location: /pedidos');
                        }
                        exit();
                    } else {
                        $error = 'Correo electrónico o contraseña incorrectos.';
                    }
                } else {
                    $error = 'Correo electrónico o contraseña incorrectos.';
                }
            } catch (PDOException $e) {
                $error = 'Error en el sistema. Intente nuevamente.';
                // Para debug: error_log($e->getMessage());
            }
        }
        
        // Si hay error, mostrar login con el error
        $router->render('auth/login', [
            "title" => "Iniciar Sesión",
            "error" => $error,
            "email" => $email
        ]);
    }
    
    public static function logout() {
        session_start();
        session_destroy();
        header('Location: /login');
        exit();
    }
    
    // Función para verificar si el usuario está logueado
    public static function isLoggedIn() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['usuario_id']);
    }
    
    // Función para verificar rol de administrador
    public static function isAdmin() {
        if (!self::isLoggedIn()) {
            return false;
        }
        return $_SESSION['usuario_rol'] == 1;
    }
    
    // Función para verificar login (redirigir si no está logueado)
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: /login');
            exit();
        }
    }
    
    // Función para verificar rol de administrador (redirigir si no es admin)
    public static function requireAdmin() {
        self::requireLogin();
        if (!self::isAdmin()) {
            header('Location: /acceso-denegado');
            exit();
        }
    }
    
    // Obtener datos del usuario actual
    public static function getCurrentUser() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['usuario_id'],
            'nombre' => $_SESSION['usuario_nombre'],
            'email' => $_SESSION['usuario_email'],
            'rol' => $_SESSION['usuario_rol'],
            'nombre_rol' => $_SESSION['nombre_rol']
        ];
    }
}
?>