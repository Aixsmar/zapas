<?php 
include 'conexion.php'; 

// Consulta para traer las ventas con el nombre del producto y la marca
$sql = "SELECT v.id_venta, v.fecha_venta, p.nombre as producto, m.nombre as marca, dv.precio_unitario 
        FROM ventas v 
        JOIN detalle_ventas dv ON v.id_venta = dv.id_venta 
        JOIN productos p ON dv.id_producto = p.id_producto 
        LEFT JOIN marcas m ON p.id_marca = m.id_marca 
        ORDER BY v.fecha_venta DESC";

$resultado = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 40px; }
        .reporte-box { max-width: 900px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        h2 { color: #2c3e50; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; color: #555; }
        .volver { display: inline-block; margin-bottom: 20px; color: #007bff; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<div class="reporte-box">
    <a href="index.php" class="volver">⬅️ Volver al Inventario</a>
    <h2>📊 Historial de Ventas Realizadas</h2>
    
    <table>
        <thead>
            <tr>
                <th>Ticket #</th>
                <th>Fecha y Hora</th>
                <th>Marca / Modelo</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php while($f = mysqli_fetch_assoc($resultado)): ?>
            <tr>
                <td><strong><?php echo $f['id_venta']; ?></strong></td>
                <td><?php echo date("d/m/Y H:i", strtotime($f['fecha_venta'])); ?></td>
                <td><?php echo $f['marca'] . " - " . $f['producto']; ?></td>
                <td style="color: #28a745; font-weight: bold;">$<?php echo number_format($f['precio_unitario'], 2); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>