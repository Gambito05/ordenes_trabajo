<?php

namespace App\controllers;

use Slim\Routing\RouteCollectorProxy;

$app->group('/api',function(RouteCollectorProxy $api){
    $api->group('/ordenes_trabajo'function(RoteCollectorProxy $endpoint){
        $endpoint->get('[/{id}]',OrdenTrabajo::class . ':read');
        $endpoint->post('',OrdenTrabajo::class . ':create');
        $endpoint->put('[/{id}]',OrdenTrabajo::class . ':update');
        $endpoint->delete('[/{id}]',OrdenTrabajo::class . ':delete');
    })
})