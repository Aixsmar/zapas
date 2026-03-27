<?php
ob_start();
session_start();
include("conexion.php");

if (isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn-login'])) {
    $usuario = mysqli_real_escape_string($conexion, $_POST['usuario']);
    $password = mysqli_real_escape_string($conexion, $_POST['password']);

    $sql = "SELECT * FROM usuarios WHERE usuario = '$usuario' AND password = '$password'";
    $resultado = mysqli_query($conexion, $sql);

    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $user = mysqli_fetch_assoc($resultado);
        $_SESSION['id_usuario'] = $user['id_usuario'];
        $_SESSION['usuario'] = $user['usuario']; 
        $_SESSION['nombre'] = $user['nombre'];  
        $_SESSION['rol'] = $user['rol']; 
        header("Location: index.php");
        exit();
    } else {
        $error_msg = "Usuario o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Aura Footwear</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #0b1a2a; /* Azul oscuro corporativo de Aura */
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
        }
        
        .login-card { 
            background: white; 
            padding: 40px 40px 50px 40px; 
            border-radius: 20px; 
            width: 100%; 
            max-width: 400px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.4); 
            text-align: center; 
        }

        .logo-img {
            width: 85%; 
            max-width: 250px;
            margin-bottom: 30px;
            transform: scale(1.6);
        }

        .input-group { text-align: left; margin-bottom: 20px; }
        .input-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; color: #475569; }
        
        .input-wrapper { position: relative; }
        .input-wrapper i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 1.1rem; }
        .input-wrapper input { 
            width: 100%; 
            padding: 14px 14px 14px 45px; 
            border: 1.5px solid #e2e8f0; 
            border-radius: 10px; 
            outline: none; 
            font-size: 1rem; 
            transition: border-color 0.3s; 
            background: #f8fafc; 
        }
        .input-wrapper input:focus { border-color: #b48a47; background: white; }
        
        .btn-login { 
            width: 100%; 
            padding: 15px; 
            background: linear-gradient(135deg, #0b1a2a, #1e3a5f); 
            color: white; 
            border: none; 
            border-radius: 30px; 
            font-size: 1rem; 
            font-weight: bold; 
            cursor: pointer; 
            box-shadow: 0 8px 20px rgba(11, 26, 42, 0.3); 
            transition: transform 0.2s; 
            margin-top: 15px;
            letter-spacing: 1px;
        }
        .btn-login:hover { transform: translateY(-3px); }

        .register-link { font-size: 0.9rem; color: #64748b; margin-top: 25px; }
        .register-link a { color: #0b1a2a; font-weight: bold; text-decoration: none; transition: 0.3s;}
        .register-link a:hover { color: #b48a47; }
        
        .error { color: #ef4444; font-size: 0.9rem; margin-bottom: 20px; background: #fee2e2; padding: 12px; border-radius: 8px; border-left: 4px solid #ef4444;}
    </style>
</head>
<body>

    <div class="login-card">
        <img src="img/logo.png" alt="Aura Footwear" class="logo-img">
        
        <?php if($error_msg) echo "<div class='error'>$error_msg</div>"; ?>

        <form method="POST" action="login.php">
            <div class="input-group">
                <label>Usuario</label>
                <div class="input-wrapper">
                    <i class="fa-regular fa-user"></i>
                    <input type="text" name="usuario" placeholder="Ej: juan123" required>
                </div>
            </div>

            <div class="input-group">
                <label>Contraseña</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" placeholder="********" required>
                </div>
            </div>

            <button type="submit" name="btn-login" class="btn-login">INICIAR SESIÓN</button>
        </form>

        <div class="register-link">
            ¿No tienes cuenta? <a href="registro.php">Regístrate gratis</a>
        </div>
    </div>

</body>
</html>
<?php ob_end_flush(); ?>