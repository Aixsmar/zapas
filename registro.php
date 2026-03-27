<?php
ob_start();
session_start();
include("conexion.php");

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn-registrar'])) {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $usuario = mysqli_real_escape_string($conexion, $_POST['usuario']);
    $password = mysqli_real_escape_string($conexion, $_POST['password']);
    
    // 1. Verificamos si el nombre de usuario ya existe para no tener duplicados
    $check_sql = "SELECT * FROM usuarios WHERE usuario = '$usuario'";
    $check_result = mysqli_query($conexion, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        $mensaje = "<div class='error'>Ese nombre de usuario ya está en uso. Elige otro.</div>";
    } else {
        // 2. Insertamos al nuevo usuario obligatoriamente con el rol de 'cliente'
        $sql_insert = "INSERT INTO usuarios (nombre, usuario, password, rol) VALUES ('$nombre', '$usuario', '$password', 'cliente')";
        
        if (mysqli_query($conexion, $sql_insert)) {
            $mensaje = "<div class='success'>¡Registro exitoso! Ya puedes iniciar sesión.</div>";
        } else {
            $mensaje = "<div class='error'>Error al registrar: " . mysqli_error($conexion) . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Zapatería</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f0f2f5; margin: 0; }
        .card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 320px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; margin-bottom: 10px;}
        .error { color: #721c24; background: #f8d7da; padding: 8px; border-radius: 5px; font-size: 14px; text-align: center; margin-bottom: 10px;}
        .success { color: #155724; background: #d4edda; padding: 8px; border-radius: 5px; font-size: 14px; text-align: center; margin-bottom: 10px;}
        .login-link { text-align: center; display: block; font-size: 14px; color: #64748b; text-decoration: none; font-weight: bold; }
        .login-link:hover { color: #007bff; }
    </style>
</head>
<body>
    <div class="card">
        <h2 style="text-align:center">Crear Cuenta</h2>
        <?php if($mensaje) echo $mensaje; ?>
        <form method="POST" action="registro.php">
            <input type="text" name="nombre" placeholder="Tu nombre real (Ej: Juan)" required>
            <input type="text" name="usuario" placeholder="Usuario (Ej: juan123)" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit" name="btn-registrar">Registrarme</button>
        </form>
        <a href="login.php" class="login-link">Volver al Login</a>
    </div>
</body>
</html>
<?php ob_end_flush(); ?>