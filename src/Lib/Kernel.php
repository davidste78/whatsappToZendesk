<?php

namespace Lib;

class Kernel {

    public function run()
    {
        $this->prepareRoutes();
    }

    private function prepareRoutes()
    {
        $controllerFolder   = "\\WaToZendApp\\Controller\\";
        $router             = $this->getRouter();
        $match              = $router->match();

        if ($match) {
            $target     = explode('#', $match['target']);
            $className  = $controllerFolder.$target[0];
            $class      = new $className;

            call_user_func_array(array($class, $target[1]), $match['params']);
        } else {
            header( $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, must-revalidate');
            echo json_encode(array('code'=>'404','message'=>'resource not found for that method'));
        }
    }

    private function getRouter()
    {
        $router = new \AltoRouter();
        $router->map('GET', '/generate-ticket', "ZendeskController#generateTicket");

        return $router;
    }
}
