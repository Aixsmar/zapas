<?php
session_start();
// Si no es admin, mándalo de vuelta al index con un aviso
if ($_SESSION['rol'] !== 'admin') {
    echo "<script>alert('Acceso denegado. Solo administradores.'); window.location='index.php';</script>";
    exit();
}
// ... resto del código ...
?>
<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
// ... aquí sigue el resto de tu código de index o agregar ...
?>
<?php 
include 'conexion.php'; 

// 1. Cargar los datos actuales del zapato
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $res = mysqli_query($conexion, "SELECT * FROM productos WHERE id_producto = $id");
    $p = mysqli_fetch_assoc($res);
}

// 2. Guardar los cambios si se presiona el botón
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $desc = mysqli_real_escape_string($conexion, $_POST['descripcion']);

    $sql = "UPDATE productos SET nombre='$nombre', precio_base='$precio', stock='$stock', descripcion='$desc' WHERE id_producto=$id";

    if (mysqli_query($conexion, $sql)) {
        echo "<script>alert('¡Cambios guardados!'); window.location='index.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Editar Zapato</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 50px; }
        .box { background: white; padding: 30px; border-radius: 8px; max-width: 450px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input, textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; box-sizing: border-box; }
        .btn-save { width: 100%; padding: 12px; background: #007bff; color: white; border: none; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
<div class="box">
    <h3>✏️ Editar Producto #<?php echo $p['id_producto']; ?></h3>
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $p['id_producto']; ?>">
        
        <label>Modelo:</label>
        <input type="text" name="nombre" value="<?php echo $p['nombre']; ?>" required>
        
        <label>Precio:</label>
        <input type="number" step="0.01" name="precio" value="<?php echo $p['precio_base']; ?>" required>
        
        <label>Stock:</label>
        <input type="number" name="stock" value="<?php echo $p['stock']; ?>" required>
        
        <label>Descripción:</label>
        <textarea name="descripcion" rows="4"><?php echo $p['descripcion']; ?></textarea>
        
        <button type="submit" class="btn-save">Actualizar Datos</button>
    </form>
    <br>
    <a href="index.php" style="color: #666; text-decoration: none;">⬅️ Volver sin cambios</a>
</div>
</body>
</html>