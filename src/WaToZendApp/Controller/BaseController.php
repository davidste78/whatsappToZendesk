<?php

namespace WaToZendApp\Controller;

class BaseController {

    protected function render($data)
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        echo json_encode($data);
    }
}
