<?php

require_once __DIR__ . '/../Router.php';

class DashboardController {

    public static function index(Router $router) {
        $router->render('dashboard/admin', [
            "title" => "Dashboard - Admin"
        ]);
    }

    public static function controlStock(Router $router) {
        $router->render('dashboard/control_stock', [
            "title" => "Control de Stock"
        ]);
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

    public static function productos(Router $router) {
        $router->render('dashboard/Products_admin', [
            "title" => "Gestión de Productos"
        ]);
    }

    public static function reportes(Router $router) {
        $router->render('dashboard/reportes_control', [
            "title" => "Reportes y Control"
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
