<?php
ob_start();
session_start();
include 'conexion.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn-guardar'])) {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $id_marca = intval($_POST['id_marca']);
    $precio = floatval($_POST['precio']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    
    // Gestión de Imagen
    $imagen = "sin_foto.png";
    if (!empty($_FILES['imagen']['name'])) {
        $imagen = time() . "_" . $_FILES['imagen']['name'];
        move_uploaded_file($_FILES['imagen']['tmp_name'], "img/" . $imagen);
    }

    // 1. Insertar el producto (Ya no necesitamos la columna 'stock' en la tabla productos)
    $sql_prod = "INSERT INTO productos (nombre, id_marca, precio_base, descripcion, imagen) 
                 VALUES ('$nombre', '$id_marca', '$precio', '$descripcion', '$imagen')";
    
    if (mysqli_query($conexion, $sql_prod)) {
        $id_nuevo_producto = mysqli_insert_id($conexion);
        
        // 2. Insertar las tallas y sus stocks
        $tallas_input = $_POST['tallas']; // Arreglo de tallas
        foreach ($tallas_input as $talla => $cantidad) {
            $cantidad = intval($cantidad);
            if ($cantidad > 0) {
                $sql_talla = "INSERT INTO producto_tallas (id_producto, talla, stock) 
                              VALUES ('$id_nuevo_producto', '$talla', '$cantidad')";
                mysqli_query($conexion, $sql_talla);
            }
        }
        header("Location: index.php?msg=guardado");
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Zapato - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 40px; }
        .form-card { max-width: 600px; background: white; padding: 30px; border-radius: 15px; margin: auto; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        h2 { color: #0b1a2a; margin-bottom: 20px; text-align: center; }
        input, select, textarea { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; }
        .tallas-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; background: #f8fafc; padding: 15px; border-radius: 10px; margin: 15px 0; }
        .talla-item label { font-size: 0.8rem; font-weight: bold; display: block; text-align: center; }
        .talla-item input { padding: 5px; text-align: center; margin: 5px 0; }
        .btn-save { background: #b48a47; color: white; border: none; padding: 15px; width: 100%; border-radius: 10px; font-weight: bold; cursor: pointer; font-size: 1rem; }
    </style>
</head>
<body>
    <div class="form-card">
        <h2><i class="fa-solid fa-plus-circle"></i> Nuevo Producto</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="nombre" placeholder="Nombre del Zapato" required>
            
            <select name="id_marca" required>
                <option value="">Selecciona Marca</option>
                <?php 
                $res_m = mysqli_query($conexion, "SELECT * FROM marcas");
                while($m = mysqli_fetch_assoc($res_m)) echo "<option value='{$m['id_marca']}'>{$m['nombre']}</option>";
                ?>
            </select>

            <input type="number" step="0.01" name="precio" placeholder="Precio ($)" required>
            <textarea name="descripcion" placeholder="Descripción breve..." rows="3"></textarea>
            <label>Imagen del producto:</label>
            <input type="file" name="imagen" accept="image/*">

            <h4 style="margin-top:20px;">Inventario por Talla (Cantidad disponible)</h4>
            <div class="tallas-grid">
                <?php 
                $tallas_comunes = ['36', '37', '38', '39', '40', '41', '42', '43'];
                foreach($tallas_comunes as $t): ?>
                    <div class="talla-item">
                        <label>Talla <?php echo $t; ?></label>
                        <input type="number" name="tallas[<?php echo $t; ?>]" value="0" min="0">
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" name="btn-guardar" class="btn-save">Guardar Producto</button>
        </form>
    </div>
</body>
</html>