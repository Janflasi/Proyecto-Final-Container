<?php

// Cargar el autoload si usas composer o carga manual de clases
require_once './Router.php';
require_once './controllers/HomeController.php';
require_once './controllers/AuthController.php';
require_once './controllers/DashboardController.php';
require_once './controllers/MeseroController.php';

$router = new Router;

// ==================== RUTAS PÚBLICAS ====================

// Página de inicio
$router->get('/', [HomeController::class, 'index']);
$router->get('/inicio', [HomeController::class, 'index']);

// ==================== RUTAS DE AUTENTICACIÓN ====================

// Login - mostrar formulario
$router->get('/login', [AuthController::class, 'login']);
// Login - procesar formulario
$router->post('/login', [AuthController::class, 'authenticate']);
// Logout
$router->get('/logout', [AuthController::class, 'logout']);

// ==================== RUTAS DEL DASHBOARD (ADMIN) ====================

// Dashboard principal
$router->get('/admin', [DashboardController::class, 'index']);
$router->get('/dashboard', [DashboardController::class, 'index']);

// Control de Stock
$router->get('/admin/stock', [DashboardController::class, 'controlStock']);
$router->post('/admin/stock', [DashboardController::class, 'updateStock']);

// GESTIÓN DE EMPLEADOS - RUTAS COMPLETAS
// Ruta principal (GET) - Muestra la página de gestión con todos los empleados
$router->get('/admin/empleados', [DashboardController::class, 'empleados']);

// Ruta principal (POST) - Maneja peticiones AJAX para acciones dinámicas
$router->post('/admin/empleados', [DashboardController::class, 'empleados']);

// Rutas específicas para compatibilidad con formularios tradicionales
$router->post('/admin/empleados/create', [DashboardController::class, 'createEmpleado']);
$router->post('/admin/empleados/update', [DashboardController::class, 'updateEmpleado']);
$router->post('/admin/empleados/delete', [DashboardController::class, 'deleteEmpleado']);

// Rutas adicionales opcionales para funcionalidades específicas
$router->get('/admin/empleados/search', [DashboardController::class, 'empleados']); // Para búsquedas con GET
$router->get('/admin/empleados/edit/{id}', [DashboardController::class, 'empleados']); // Para editar específico


// Pagos de Empleados
$router->get('/admin/pagos', [DashboardController::class, 'pagosEmpleados']);
$router->post('/admin/pagos', [DashboardController::class, 'procesarPago']);

// Gestión de Productos
$router->get('/admin/productos', [DashboardController::class, 'productos']);
$router->post('/admin/productos', [DashboardController::class, 'createProducto']);
$router->post('/admin/productos/update', [DashboardController::class, 'updateProducto']);
$router->post('/admin/productos/delete', [DashboardController::class, 'deleteProducto']);


// Gestión de Categorías - Agregar estas rutas
$router->get('/admin/categorias', [DashboardController::class, 'categorias']);
$router->post('/admin/categorias', [DashboardController::class, 'categorias']); // Para manejar formularios inline
$router->post('/admin/categorias/create', [DashboardController::class, 'createCategoria']);
$router->post('/admin/categorias/update', [DashboardController::class, 'updateCategoria']);
$router->post('/admin/categorias/delete', [DashboardController::class, 'deleteCategoria']);
// Reportes y Control
$router->get('/admin/reportes', [DashboardController::class, 'reportes']);
$router->post('/admin/reportes/generar', [DashboardController::class, 'generarReporte']);

// Control de Ventas - Router corregido
// Solo necesitas una ruta que maneje tanto GET como POST
$router->get('/admin/ventas', [DashboardController::class, 'ventasControl']);
$router->post('/admin/ventas', [DashboardController::class, 'ventasControl']);

// O alternativamente, si prefieres separar la lógica:
// $router->get('/admin/ventas', [DashboardController::class, 'ventasControl']);
// $router->post('/admin/ventas/procesar', [DashboardController::class, 'ventasControl']);

// Configuración del Sistema
$router->get('/admin/configuracion', [DashboardController::class, 'configuracion']);
$router->post('/admin/configuracion', [DashboardController::class, 'updateConfiguracion']);

// Seguridad del Sistema
$router->get('/admin/seguridad', [DashboardController::class, 'seguridad']);
$router->post('/admin/seguridad', [DashboardController::class, 'updateSeguridad']);



$router->get('/admin/mesas', [DashboardController::class, 'mesas']);
$router->post('/admin/seguridad', [DashboardController::class, 'updateSeguridad']);

// ==================== RUTAS DEL MESERO ====================

// Rutas principales del mesero
$router->get('/mesero', [MeseroController::class, 'index']);
$router->get('/mesero/sign', [MeseroController::class, 'sign']);
$router->post('/mesero/sign', [MeseroController::class, 'processSign']);
$router->get('/mesero/mesas', [MeseroController::class, 'mesas']);
$router->get('/mesero/pedidos', [MeseroController::class, 'pedidos']);
$router->post('/mesero/crear-pedido', [MeseroController::class, 'crearPedido']);

// Rutas AJAX
$router->post('/mesero/action', [MeseroController::class, 'processAction']);
$router->get('/mesero/carrito', [MeseroController::class, 'getCarrito']);
$router->get('/mesero/cuentas', [MeseroController::class, 'getCuentas']);
$router->get('/mesero/mesas-data', [MeseroController::class, 'getMesas']);

// API para obtener datos en tiempo real
$router->get('/api/productos', [DashboardController::class, 'getProductosAPI']);
$router->get('/api/stock', [DashboardController::class, 'getStockAPI']);
$router->get('/api/ventas', [DashboardController::class, 'getVentasAPI']);

// ==================== VERIFICAR RUTAS ====================

$router->verifyRoutes();
