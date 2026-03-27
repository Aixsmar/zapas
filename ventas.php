<?php include 'conexion.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Historial de Ventas</title>
    <style>
        body { font-family: sans-serif; margin: 40px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #333; color: white; }
    </style>
</head>
<body>
    <h2>💰 Reporte de Ventas Totales</h2>
    <a href="index.php">⬅️ Volver al Inventario</a>
    <br><br>
    <table>
        <tr>
            <th>ID Venta</th>
            <th>Fecha</th>
            <th>Producto</th>
            <th>Total Pagado</th>
        </tr>
        <?php
        $sql = "SELECT v.id_venta, v.fecha_venta, p.nombre, dv.precio_unitario 
                FROM ventas v 
                JOIN detalle_ventas dv ON v.id_venta = dv.id_venta 
                JOIN productos p ON dv.id_producto = p.id_producto 
                ORDER BY v.fecha_venta DESC";
        $res = mysqli_query($conexion, $sql);
        while($f = mysqli_fetch_assoc($res)) {
            echo "<tr>
                    <td>{$f['id_venta']}</td>
                    <td>{$f['fecha_venta']}</td>
                    <td>{$f['nombre']}</td>
                    <td>\${$f['precio_unitario']}</td>
                  </tr>";
        }
        ?>
    </table>
</body>
</html>