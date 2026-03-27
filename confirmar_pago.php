<?php
ob_start();
session_start();
include 'conexion.php';

if (empty($_SESSION['carrito'])) {
    header("Location: index.php");
    exit();
}

// Intentamos sacar el ID de usuario, por defecto 1
$id_user_final = isset($_SESSION['id_usuario']) ? intval($_SESSION['id_usuario']) : 1;
$total_final = 0;
$exito = false;

mysqli_begin_transaction($conexion);

try {
    // 1. Calcular Total
    foreach ($_SESSION['carrito'] as $item) {
        $partes = explode('-', (string)$item);
        $id_prod = isset($partes[0]) ? intval($partes[0]) : 0;
        $talla_val = isset($partes[1]) ? (string)$partes[1] : '';

        if ($id_prod > 0) {
            $res = mysqli_query($conexion, "SELECT precio_base FROM productos WHERE id_producto = $id_prod");
            if ($fila_p = mysqli_fetch_assoc($res)) {
                $total_final += $fila_p['precio_base'];
            }
        }
    }

    // 2. Insertar Venta (fecha_venta, total, id_usuario)
    $sql_venta = "INSERT INTO ventas (fecha_venta, total, id_usuario) VALUES (NOW(), $total_final, $id_user_final)";
    mysqli_query($conexion, $sql_venta);
    $id_venta = mysqli_insert_id($conexion);

    // 3. Insertar Detalles y Descontar Stock
    foreach ($_SESSION['carrito'] as $item) {
        $partes = explode('-', (string)$item);
        $id_prod = isset($partes[0]) ? intval($partes[0]) : 0;
        $talla_val = isset($partes[1]) ? (string)$partes[1] : '';
        $talla_limpia = mysqli_real_escape_string($conexion, $talla_val);

        if ($id_prod > 0 && !empty($talla_limpia)) {
            // Guardar detalle
            mysqli_query($conexion, "INSERT INTO detalle_ventas (id_venta, id_producto, talla) 
                                     VALUES ($id_venta, $id_prod, '$talla_limpia')");

            // Descontar stock
            mysqli_query($conexion, "UPDATE producto_tallas 
                                     SET stock = stock - 1 
                                     WHERE id_producto = $id_prod AND talla = '$talla_limpia'");
        }
    }

    mysqli_commit($conexion);
    $_SESSION['carrito'] = []; // Vaciar carrito
    $exito = true;

} catch (Exception $e) {
    mysqli_rollback($conexion);
    $exito = false;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estado de Compra - Aura Footwear</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 3rem; border-radius: 20px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1); max-width: 450px; border: 1px solid #e2e8f0; }
        .icon { font-size: 4rem; margin-bottom: 1.5rem; }
        .btn { display: inline-block; background: #b48a47; color: white; padding: 12px 30px; border-radius: 10px; text-decoration: none; font-weight: bold; margin-top: 1rem; }
    </style>
</head>
<body>
    <div class="card">
        <?php if($exito): ?>
            <div class="icon" style="color: #16a34a;"><i class="fa-solid fa-circle-check"></i></div>
            <h1 style="color:#0b1a2a;">¡Compra Exitosa!</h1>
            <p style="color:#64748b;">Tu pedido se ha guardado correctamente.</p>
            <a href="index.php" class="btn">Volver al Inicio</a>
        <?php else: ?>
            <div class="icon" style="color: #ef4444;"><i class="fa-solid fa-circle-xmark"></i></div>
            <h1 style="color:#0b1a2a;">Error en la Compra</h1>
            <p style="color:#64748b;">Hubo un problema al procesar tu pedido. Intenta de nuevo.</p>
            <a href="carrito.php" class="btn" style="background:#0b1a2a;">Regresar al Carrito</a>
        <?php endif; ?>
    </div>
</body>
</html>
<?php ob_end_flush(); ?>