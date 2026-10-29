<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use App\Models\OrdenTrabajoModel as OrdenTrabajoModel;

class OrdenTrabajo{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $c){
        $this->container = $c;
    }

    public function read(Request $request, Response $response, array $args): Response{
        $this->container->get('eloquent');

        if(isset($args['id'])){
            $orden = OrdenTrabajoModel::query()
                ->select(['id','placa_vehiculo','propietario','descripcion_falla','costo_estimado','estado','fecha_ingreso'])
                ->find($args['id']);
            return $this->json($response,['data'=>$orden],200);
        }

        $ordenes = OrdenTrabajoModel::query()
            ->select(['id','placa_vehiculo','propietario','descripcion_falla','costo_estimado','estado','fecha_ingreso'])
            ->get()
            ->toArray();
        return $this->json($response,['data'=>$ordenes],200);
    }

    public function create(Request $request, Response $response, array $args): Response{
        $this->container->get('eloquent');

        $data = $request->getParsedBody();
        $data = is_array($data) ? $data : [];

        $errors = [];
        $plate = trim((string)($data['placa_vehiculo'] ?? ''));
        $owner = trim((string)($data['propietario'] ?? ''));
        $fault = trim((string)($data['descripcion_falla'] ?? ''));
        $cost = $data['costo_estimado'] ?? null;
        $status = isset($data['estado']) ? trim((string)$data['estado']) : 'Pendiente';

        if($plate === '' || mb_strlen($plate) > 10){
            $errors['placa_vehiculo'] = 'Es obligatoria y debe tener maximo 10 caracteres.';
        }
        if($owner === '' || mb_strlen($owner) > 100){
            $errors['propietario'] = 'Es obligatorio y debe tener maximo 100 caracteres.';
        }
        if($fault === ''){
            $errors['descripcion_falla'] = 'Es obligatoria.';
        }
        if(!is_numeric($cost) || $cost < 0 || $cost > 99999999.99){
            $errors['costo_estimado'] = 'Es obligatorio y debe ser un numero entre 0 y 99999999.99.';
        }
        if(!in_array($status, self::ESTADOS, true)){
            $errors['estado'] = 'Debe ser: Pendiente, En Proceso, Finalizado o Entregado.';
        }

        if($errors !== []){
            return $this->json($response,['errors'=>$errors],422);
        }

        $orden = OrdenTrabajoModel::create([
            'placa_vehiculo' => $plate,
            'propietario' => $owner,
            'descripcion_falla' => $fault,
            'costo_estimado' => $cost,
            'estado' => $status,
        ]);
        
        return $this->json($response,['data'=>$orden->refresh()->toArray()],201);
    }

    public function update(Request $request, Response $response, array $args): Response{
        $this->container->get('eloquent');

        if(isset($args['id'])){
            $orden = OrdenTrabajoModel::find($args['id']);

            if($orden === null){
                return $this->json($response,['error'=>'Orden de trabajo no encontrada.'],404);
            }

            $data = $request->getParsedBody();
            $data = is_array($data) ? $data : [];

            if(isset($data['placa_vehiculo'])){
                $plate = trim((string)$data['placa_vehiculo']);
                if($plate === '' || mb_strlen($plate) > 10){
                    return $this->json($response,['error'=>'Placa invalida'],422);
                }
                $orden->placa_vehiculo = $plate;
            }

            if(isset($data['propietario'])){
                $owner = trim((string)$data['propietario']);
                if($owner === '' || mb_strlen($owner) > 100){
                    return $this->json($response,['error'=>'Propietario invalido'],422);
                }
                $orden->propietario = $owner;
            }

            if(isset($data['descripcion_falla'])){
                $fault = trim((string)$data['descripcion_falla']);
                if($fault === ''){
                    return $this->json($response,['error'=>'Descripcion de la falla invalida'],422);
                }
                $orden->descripcion_falla = $fault;
            }

            if(isset($data['costo_estimado'])){
                $cost = $data['costo_estimado'];
                if(!is_numeric($cost) || $cost < 0 || $cost > 99999999.99){
                    return $this->json($response,['error'=>'Costo estimado invalido'],422);
                }
                $orden->costo_estimado = $cost;
            }

            if(isset($data['estado'])){
                $status = trim((string)$data['estado']);
                if(!in_array($status, ['Pendiente','En Proceso','Finalizado','Entregado'], true)){
                    return $this->json($response,['error'=>'Estado invalido'],422);
                }
                $orden->estado = $status;
            }
        
            $orden->save();

            return $this->json($response,['data'=>$orden->toArray()],200);
        }
    }

    public function delete(Request $request, Response $response, array $args): Response{
        $this->container->get('eloquent');

        if(isset($args['id'])){
            $orden = OrdenTrabajoModel::find($args['id']);

            if($orden === null){
                return $this->json($response,['error'=>'Orden de trabajo no encontrada.'],404);
            }

            $orden->delete();

            return $response->withStatus(204);
        }
    }

    
    private function json(Response $response, array $payload, int $status): Response{
        $response->getBody()->write(json_encode($payload,
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return $response
            ->withHeader('Content-Type','application/json; charset=utf-8')
            ->withStatus($status);
    }
}