<?php

/**
 * Devuelve la ruta del archivo de tareas de un usuario.
 */
function archivoTareas($usuario)
{
    // Evita que el nombre del archivo contenga caracteres peligrosos.
    $usuarioSeguro = preg_replace('/[^a-zA-Z0-9_-]/', '_', $usuario);

    return __DIR__ . "/tareas_" . $usuarioSeguro . ".csv";
}

/**
 * Guarda una tarea nueva.
 */
function guardarTarea($usuario, $texto)
{
    $texto = trim($texto);

    if ($texto === '') {
        return false;
    }

    $archivo = archivoTareas($usuario);
    $manejador = fopen($archivo, 'a');

    if ($manejador === false) {
        return false;
    }

    if (!flock($manejador, LOCK_EX)) {
        fclose($manejador);
        return false;
    }

    $id = bin2hex(random_bytes(8));

    $resultado = fputcsv(
        $manejador,
        [$id, $texto, 'pendiente']
    );

    fflush($manejador);
    flock($manejador, LOCK_UN);
    fclose($manejador);

    return $resultado !== false;
}

/**
 * Retorna las tareas separadas en pendientes y completadas.
 */
function listarTareas($usuario)
{
    $resultado = [
        'pendientes' => [],
        'completadas' => []
    ];

    $archivo = archivoTareas($usuario);

    if (!file_exists($archivo)) {
        return $resultado;
    }

    $manejador = fopen($archivo, 'r');

    if ($manejador === false) {
        return $resultado;
    }

    flock($manejador, LOCK_SH);

    while (($campos = fgetcsv($manejador)) !== false) {
        if (count($campos) < 3) {
            continue;
        }

        $tarea = [
            'id'     => $campos[0],
            'texto'  => $campos[1],
            'estado' => $campos[2]
        ];

        if ($tarea['estado'] === 'completada') {
            $resultado['completadas'][] = $tarea;
        } else {
            $resultado['pendientes'][] = $tarea;
        }
    }

    flock($manejador, LOCK_UN);
    fclose($manejador);

    return $resultado;
}

/**
 * Abre, modifica y vuelve a escribir el archivo de tareas.
 */
function modificarArchivoTareas($usuario, $modificador)
{
    $archivo = archivoTareas($usuario);
    $manejador = fopen($archivo, 'c+');

    if ($manejador === false) {
        return false;
    }

    if (!flock($manejador, LOCK_EX)) {
        fclose($manejador);
        return false;
    }

    $tareas = [];

    rewind($manejador);

    while (($campos = fgetcsv($manejador)) !== false) {
        if (count($campos) < 3) {
            continue;
        }

        $tareas[] = [
            'id'     => $campos[0],
            'texto'  => $campos[1],
            'estado' => $campos[2]
        ];
    }

    $tareas = $modificador($tareas);

    rewind($manejador);
    ftruncate($manejador, 0);

    $resultado = true;

    foreach ($tareas as $tarea) {
        if (
            fputcsv(
                $manejador,
                [$tarea['id'], $tarea['texto'], $tarea['estado']]
            ) === false
        ) {
            $resultado = false;
        }
    }

    fflush($manejador);
    flock($manejador, LOCK_UN);
    fclose($manejador);

    return $resultado;
}

/**
 * Cambia una tarea pendiente a completada.
 */
function completarTarea($usuario, $id)
{
    $encontrada = false;

    $resultado = modificarArchivoTareas(
        $usuario,
        function ($tareas) use ($id, &$encontrada) {
            foreach ($tareas as &$tarea) {
                if ($tarea['id'] === $id) {
                    $tarea['estado'] = 'completada';
                    $encontrada = true;
                    break;
                }
            }

            unset($tarea);

            return $tareas;
        }
    );

    return $resultado && $encontrada;
}

/**
 * Elimina una tarea.
 */
function eliminarTarea($usuario, $id)
{
    $encontrada = false;

    $resultado = modificarArchivoTareas(
        $usuario,
        function ($tareas) use ($id, &$encontrada) {
            $nuevasTareas = [];

            foreach ($tareas as $tarea) {
                if ($tarea['id'] === $id) {
                    $encontrada = true;
                    continue;
                }

                $nuevasTareas[] = $tarea;
            }

            return $nuevasTareas;
        }
    );

    return $resultado && $encontrada;
}