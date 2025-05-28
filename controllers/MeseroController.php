<?php

require_once __DIR__ . '/../Router.php';

class MeseroController {

    public static function index(Router $router) {
        $router->render('mesero/index', [
            "title" => "Panel Mesero"
        ]);
    }

    public static function sign(Router $router) {
        $router->render('mesero/sign', [
            "title" => "Registro de Actividad"
        ]);
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
}

?>
