<?php

require_once __DIR__ . '/../Router.php';

class AuthController {
    
    public static function login(Router $router) {
        $router->render('auth/login', [
            "title" => "Iniciar Sesión"
        ]);
    }
    
}

?>
