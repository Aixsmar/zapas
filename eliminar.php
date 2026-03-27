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
include 'conexion.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM productos WHERE id_producto = $id";

    if (mysqli_query($conexion, $sql)) {
        header("Location: index.php"); // Regresa a la lista automáticamente
    } else {
        echo "Error al eliminar: " . mysqli_error($conexion);
    }
}
?>