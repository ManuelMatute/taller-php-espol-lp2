<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <h1>Formulario de Registro</h1>
    <form method="POST" action="bienvenido.php">
        <label>Cédula:</label>
        <input type="text" name="cedula" maxlength="10" required><br>
 
        <label>Nombre:</label>
        <input type="text" name="nombre" maxlength="30" required><br>
 
        <label>Estado Civil:</label>
        <select name="estado_civil">
            <option value="soltero">Soltero</option>
            <option value="casado">Casado</option>
            <option value="union_libre">Unión libre</option>
            <option value="viudo">Viudo</option>
        </select><br>
 
        <label>Correo:</label>
        <input type="email" name="correo" required><br>
 
        <label>Clave:</label>
        <input
            type="password"
            name="clave"
            minlength="6"
            required
        >
 
        <input type="submit" value="Registrar">
        <input type="reset" value="Resetear">
    </form>
</body>
</html>