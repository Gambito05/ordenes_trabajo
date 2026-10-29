<?php
declare(strict_types=1);

namespace App\Validators;

use Respect\Validation\ValidatorBuilder as v;

final class OrdenTrabajoValidator {

    private function reglas(): array {
        return [
            'placa_vehiculo' => [
                'regla' => v::stringType()
                ->notBlank()
                ->length(v::between(1,10))
                ->regex('/^(?:[A-Z]{3}-?[0-9]{3}|[0-9]{6}|[A-Z]{2}-[0-9]{6})$/'),
                'mensaje' => 'La placa es obligatoria y debe tener un formato válido: ABC123, ABC-123, 123456 o CL-123456.',
            ],
            'propietario' => [
                'regla' => v::stringType()
                ->notBlank()
                ->length(v::between(6,100))
                ->regex('/^[A-Za-zÑñÁÉÍÓÚáéíóú]{2,}(?: [A-Za-zÑñÁÉÍÓÚáéíóú]{2,}){1,3}$/u'),
                'mensaje' => 'El propietario es obligatorio y debe tener entre 6 y 100 caracteres, solo letras y espacios (nombre y apellido).',
            ],
            'descripcion_falla' => [
                'regla' => v::stringType()
                ->notBlank()
                ->length(v::between(10,2000)),
                'mensaje' => 'La descripción de la falla es obligatoria y debe tener entre 10 y 2000 caracteres.',
            ],
            'costo_estimado' => [
                'regla' => v::anyOf(v::stringType(), v::numericVal())
                ->notBlank()
                ->regex('/^(?:0|[1-9][0-9]{0,7})(?:\.[0-9]{1,2})?$/'),
                'mensaje' => 'El costo estimado es obligatorio, debe ser un número positivo de hasta 8 enteros y 2 decimales.',
            ],
            'estado' => [
                'regla' => v::stringType()->regex('/^(?:Pendiente|En Proceso|Finalizado|Entregado)$/'),
                'mensaje' => 'El estado debe ser: Pendiente, En Proceso, Finalizado o Entregado.',
            ],
        ];
    }

    public function validate(array $data): array {
        $errores = [];

        foreach ($this->reglas() as $campo => $config){
            $valor = $data[$campo] ?? null;
            $regla = $config['regla'];
            $mensaje = $config['mensaje'];

            if ($campo === 'estado' && ($valor === null || $valor === '')) {
                continue;
            }

            if (!$regla->isValid($valor)) {
                $errores[$campo] = $mensaje;
            }
        }
        return $errores;
    }
}