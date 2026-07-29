<?php
session_start();

require_once "tarea.php";

if (!isset($_SESSION['cedula'])) {
    header("Location: ingreso.php");
    exit;
}

$usuario = $_SESSION['cedula'];
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'agregar') {
        $texto = trim($_POST['texto'] ?? '');

        if ($texto === '') {
            $error = "Debe escribir una tarea.";
        } elseif (guardarTarea($usuario, $texto)) {
            header("Location: tareas.php");
            exit;
        } else {
            $error = "No se pudo guardar la tarea.";
        }
    }

    if ($accion === 'completar') {
        $id = $_POST['id'] ?? '';

        if ($id !== '' && completarTarea($usuario, $id)) {
            header("Location: tareas.php");
            exit;
        }

        $error = "No se pudo completar la tarea.";
    }

    if ($accion === 'eliminar') {
        $id = $_POST['id'] ?? '';

        if ($id !== '' && eliminarTarea($usuario, $id)) {
            header("Location: tareas.php");
            exit;
        }

        $error = "No se pudo eliminar la tarea.";
    }
}

$tareas = listarTareas($usuario);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestor de tareas</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>
<main class="contenedor">

    <div class="encabezado-tareas">
        <div>
            <h1>Gestor de Tareas</h1>

            <p>
                Usuario:
                <strong>
                    <?= htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8') ?>
                </strong>
            </p>
        </div>

        <a class="cerrar-sesion" href="logout.php">
            Cerrar sesión
        </a>
    </div>

    <?php if ($error !== ''): ?>
        <p class="error">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <h2>Agregar tarea</h2>

    <form method="POST" action="tareas.php" class="formulario-tarea">
        <input type="hidden" name="accion" value="agregar">

        <input
            type="text"
            name="texto"
            maxlength="200"
            placeholder="Escriba una nueva tarea"
            required
        >

        <button type="submit">Agregar</button>
    </form>

    <h2>Pendientes</h2>

    <?php if (empty($tareas['pendientes'])): ?>
        <p class="sin-tareas">No hay tareas pendientes.</p>
    <?php else: ?>
        <ul class="lista-tareas">

            <?php foreach ($tareas['pendientes'] as $tarea): ?>
                <li class="tarea">

                    <span>
                        <?= htmlspecialchars(
                            $tarea['texto'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </span>

                    <div class="acciones">

                        <form method="POST" action="tareas.php">
                            <input
                                type="hidden"
                                name="accion"
                                value="completar"
                            >

                            <input
                                type="hidden"
                                name="id"
                                value="<?= htmlspecialchars(
                                    $tarea['id'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                            <button
                                type="submit"
                                class="boton-completar"
                            >
                                Completar
                            </button>
                        </form>

                        <form method="POST" action="tareas.php">
                            <input
                                type="hidden"
                                name="accion"
                                value="eliminar"
                            >

                            <input
                                type="hidden"
                                name="id"
                                value="<?= htmlspecialchars(
                                    $tarea['id'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                            <button
                                type="submit"
                                class="boton-eliminar"
                            >
                                Eliminar
                            </button>
                        </form>

                    </div>
                </li>
            <?php endforeach; ?>

        </ul>
    <?php endif; ?>

    <h2>Completadas</h2>

    <?php if (empty($tareas['completadas'])): ?>
        <p class="sin-tareas">No hay tareas completadas.</p>
    <?php else: ?>
        <ul class="lista-tareas">

            <?php foreach ($tareas['completadas'] as $tarea): ?>
                <li class="tarea tarea-completada">

                    <span>
                        <?= htmlspecialchars(
                            $tarea['texto'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </span>

                    <div class="acciones">
                        <form method="POST" action="tareas.php">
                            <input
                                type="hidden"
                                name="accion"
                                value="eliminar"
                            >

                            <input
                                type="hidden"
                                name="id"
                                value="<?= htmlspecialchars(
                                    $tarea['id'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >

                            <button
                                type="submit"
                                class="boton-eliminar"
                            >
                                Eliminar
                            </button>
                        </form>
                    </div>

                </li>
            <?php endforeach; ?>

        </ul>
    <?php endif; ?>

</main>
</body>
</html>