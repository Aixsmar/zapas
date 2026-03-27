<?php 
ob_start(); 
session_start();
include 'conexion.php'; 

// Validación de seguridad
if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

$rol = $_SESSION['rol'];

// ARREGLO DEL NOMBRE: Si el 'nombre' real está vacío, usamos el 'usuario' (nickname)
$nombre_user = !empty($_SESSION['nombre']) ? $_SESSION['nombre'] : (!empty($_SESSION['usuario']) ? $_SESSION['usuario'] : 'Usuario');

// Contamos cuántos productos hay en el carrito
$cantidad_carrito = isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0;

// Recibir variables del filtro
$buscar = isset($_GET['buscar']) ? mysqli_real_escape_string($conexion, trim($_GET['buscar'])) : "";
$marcas_seleccionadas = isset($_GET['marcas']) ? $_GET['marcas'] : [];
$rango_precio = isset($_GET['precio']) ? $_GET['precio'] : "";

// --- CONSTRUCCIÓN DE LA CONSULTA SQL CON FILTROS ---
// CORRECCIÓN: Ahora sumamos el stock de la tabla producto_tallas en tiempo real
$sql = "SELECT p.*, m.nombre AS marca, 
        (SELECT SUM(stock) FROM producto_tallas WHERE id_producto = p.id_producto) as stock_dinamico 
        FROM productos p 
        LEFT JOIN marcas m ON p.id_marca = m.id_marca 
        WHERE 1=1";

if ($buscar != "") {
    $sql .= " AND (p.nombre LIKE '%$buscar%' OR m.nombre LIKE '%$buscar%' OR p.descripcion LIKE '%$buscar%')";
}
if (!empty($marcas_seleccionadas)) {
    $marcas_ids = array_map('intval', $marcas_seleccionadas);
    $marcas_in = implode(',', $marcas_ids);
    $sql .= " AND p.id_marca IN ($marcas_in)";
}
if ($rango_precio != "") {
    if ($rango_precio == "bajo") { $sql .= " AND p.precio_base < 50"; } 
    elseif ($rango_precio == "medio") { $sql .= " AND p.precio_base BETWEEN 50 AND 100"; } 
    elseif ($rango_precio == "alto") { $sql .= " AND p.precio_base > 100"; }
}

$resultado = mysqli_query($conexion, $sql);

$total_vnt = 0;
if ($rol == 'admin') {
    $res_vnt = mysqli_query($conexion, "SELECT SUM(total) as total FROM ventas");
    if($res_vnt){
        $fila_vnt = mysqli_fetch_assoc($res_vnt);
        $total_vnt = $fila_vnt['total'] ?? 0;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo - Aura Footwear</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --primary: #0b1a2a; /* Azul oscuro Aura */
            --primary-light: #1e3a5f;
            --accent: #b48a47; /* Dorado Aura */
            --bg-color: #f4f7f6; /* Gris súper claro y elegante */
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }
        
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--bg-color); color: var(--text-dark); }
        
        /* --- Navbar --- */
        nav { background: var(--primary); color: white; padding: 1rem 3rem; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
        .nav-brand { font-size: 1.3rem; font-weight: 800; letter-spacing: 1.5px; display: flex; align-items: center; gap: 12px; }
        .nav-brand i { color: var(--accent); font-size: 1.6rem; transform: rotate(-15deg); }
        
        .nav-right { display: flex; align-items: center; gap: 25px; }
        .user-greeting { font-size: 0.95rem; color: #cbd5e1; }
        .user-greeting span { color: var(--accent); font-weight: bold; }
        
        /* Ícono del Carrito Preparado */
        .cart-icon { position: relative; color: white; font-size: 1.3rem; text-decoration: none; transition: 0.3s; }
        .cart-icon:hover { color: var(--accent); transform: scale(1.1); }
        .cart-badge { position: absolute; top: -8px; right: -10px; background: #ef4444; color: white; font-size: 0.7rem; font-weight: bold; padding: 2px 6px; border-radius: 50%; border: 2px solid var(--primary); }

        .logout { background: transparent; border: 1.5px solid rgba(255,255,255,0.3); color: white; padding: 8px 20px; border-radius: 25px; text-decoration: none; font-size: 0.85rem; font-weight: 600; transition: all 0.3s; }
        .logout:hover { background: white; color: var(--primary); }

        /* --- Estructura Principal --- */
        .layout { display: flex; max-width: 1400px; margin: 2.5rem auto; padding: 0 2rem; gap: 2.5rem; align-items: flex-start; }
        
        /* --- Sidebar (Filtros) --- */
        .sidebar { width: 260px; background: transparent; position: sticky; top: 100px; }
        .sidebar h3 { font-size: 1rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 1.2rem; color: var(--primary); border-bottom: 2px solid var(--border-color); padding-bottom: 0.5rem; margin-top: 2rem;}
        .sidebar h3:first-child { margin-top: 0; }
        
        .search-box { display: flex; flex-direction: column; gap: 10px; }
        .search-box input { padding: 14px; border: 1px solid var(--border-color); border-radius: 10px; outline: none; width: 100%; font-size: 0.95rem; background: white; transition: 0.3s; box-shadow: 0 2px 5px rgba(0,0,0,0.02);}
        .search-box input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(180, 138, 71, 0.1); }
        
        .filter-group { display: flex; flex-direction: column; gap: 12px; }
        .filter-group label { display: flex; align-items: center; gap: 10px; color: var(--text-dark); font-size: 0.95rem; cursor: pointer; transition: 0.2s; }
        .filter-group label:hover { color: var(--accent); }
        .filter-group input[type="checkbox"], .filter-group input[type="radio"] { accent-color: var(--accent); width: 16px; height: 16px; cursor: pointer; }

        .btn-filter { width: 100%; background: var(--primary); color: white; border: none; padding: 14px; border-radius: 10px; cursor: pointer; font-weight: bold; transition: 0.3s; margin-top: 2rem; letter-spacing: 0.5px; box-shadow: 0 4px 10px rgba(11, 26, 42, 0.2);}
        .btn-filter:hover { background: var(--primary-light); transform: translateY(-2px); }
        .btn-clear { display: block; text-align: center; color: var(--text-muted); font-size: 0.85rem; margin-top: 15px; text-decoration: none; transition: 0.2s;}
        .btn-clear:hover { color: var(--primary); text-decoration: underline; }

        .main-content { flex: 1; }

        /* --- Panel Admin --- */
        .admin-panel { display: flex; justify-content: space-between; align-items: center; background: white; padding: 1.5rem 2.5rem; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); margin-bottom: 2rem; border-left: 6px solid var(--accent); }
        .stat-box h4 { color: var(--text-muted); font-weight: 600; margin-bottom: 5px; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px;}
        .stat-box h2 { font-size: 2.2rem; color: var(--primary); }
        .btn-add { background: var(--accent); color: white; padding: 12px 25px; border-radius: 25px; text-decoration: none; font-weight: bold; transition: 0.3s; box-shadow: 0 4px 15px rgba(180, 138, 71, 0.3); }
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(180, 138, 71, 0.4); }

        /* --- Grid de Productos --- */
        .grid-productos { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2.5rem; }
        
        /* --- Tarjetas Premium --- */
        .card { background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.04); transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); display: flex; flex-direction: column; height: 100%; border: 1px solid rgba(226, 232, 240, 0.6); position: relative;}
        .card:hover { transform: translateY(-12px); box-shadow: 0 20px 40px rgba(0,0,0,0.08); border-color: var(--accent); }
        
        .img-container { background: white; height: 250px; display: flex; justify-content: center; align-items: center; padding: 30px; border-bottom: 1px solid #f1f5f9; }
        .card-img { width: 100%; height: 100%; object-fit: contain; mix-blend-mode: multiply; transition: transform 0.5s; }
        .card:hover .card-img { transform: scale(1.08) rotate(-5deg); }
        
        .card-body { padding: 1.8rem; display: flex; flex-direction: column; flex-grow: 1; background: white; }
        .card-brand { color: var(--accent); font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 5px; }
        .card-title { font-size: 1.3rem; margin: 0 0 1rem 0; color: var(--primary); font-weight: 800; line-height: 1.2; }
        .card-price { font-size: 1.6rem; font-weight: 800; color: var(--text-dark); margin: auto 0 1.2rem 0; }
        
        /* Badge de Stock */
        .stock-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; margin-bottom: 1rem; width: fit-content;}
        .stock-ok { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .stock-out { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        .btn-buy { background: var(--primary); color: white; text-align: center; padding: 14px; border-radius: 12px; text-decoration: none; font-weight: bold; transition: 0.3s; display: flex; justify-content: center; align-items: center; gap: 8px; font-size: 0.95rem;}
        .btn-buy:hover { background: var(--accent); box-shadow: 0 5px 15px rgba(180, 138, 71, 0.3); }

        .admin-actions { margin-top: 15px; padding-top: 15px; border-top: 1px dashed var(--border-color); display: flex; justify-content: space-between; font-size: 0.85rem; }
        .admin-actions a { text-decoration: none; font-weight: 600; transition: 0.2s; display: flex; align-items: center; gap: 5px;}
        .edit-link { color: var(--text-muted); } .edit-link:hover { color: var(--primary); }
        .delete-link { color: #ef4444; } .delete-link:hover { color: #b91c1c; }

        @media (max-width: 900px) {
            .layout { flex-direction: column; }
            .sidebar { width: 100%; position: static; }
        }
    </style>
</head>
<body>

<nav>
    <div class="nav-brand">
        <i class="fa-solid fa-shoe-prints"></i>
        AURA FOOTWEAR
    </div>
    
    <div class="nav-right">
        <div class="user-greeting">Hola, <span><?php echo htmlspecialchars($nombre_user); ?></span></div>
        
        <a href="carrito.php" class="cart-icon">
            <i class="fa-solid fa-cart-shopping"></i>
            <span class="cart-badge"><?php echo $cantidad_carrito; ?></span>
        </a>

        <a href="logout.php" class="logout">Cerrar Sesión</a>
    </div>
</nav>

<div class="layout">
    <aside class="sidebar">
        <form method="GET" action="index.php">
            
            <h3>Buscar</h3>
            <div class="search-box">
                <input type="text" name="buscar" placeholder="¿Qué buscas hoy?" value="<?php echo htmlspecialchars($buscar); ?>">
            </div>

            <h3>Marcas</h3>
            <div class="filter-group">
                <?php 
                $sql_marcas_filtros = "SELECT * FROM marcas ORDER BY nombre ASC";
                $res_marcas = mysqli_query($conexion, $sql_marcas_filtros);
                
                if($res_marcas && mysqli_num_rows($res_marcas) > 0){
                    while($m = mysqli_fetch_assoc($res_marcas)){
                        $checked = in_array($m['id_marca'], $marcas_seleccionadas) ? "checked" : "";
                        echo "<label><input type='checkbox' name='marcas[]' value='{$m['id_marca']}' $checked> " . htmlspecialchars($m['nombre']) . "</label>";
                    }
                } else {
                    echo "<small style='color:var(--text-muted)'>No hay marcas.</small>";
                }
                ?>
            </div>

            <h3>Rango de Precio</h3>
            <div class="filter-group">
                <label><input type="radio" name="precio" value="bajo" <?php if($rango_precio == 'bajo') echo 'checked'; ?>> Menos de $50</label>
                <label><input type="radio" name="precio" value="medio" <?php if($rango_precio == 'medio') echo 'checked'; ?>> $50 - $100</label>
                <label><input type="radio" name="precio" value="alto" <?php if($rango_precio == 'alto') echo 'checked'; ?>> Más de $100</label>
            </div>

            <button type="submit" class="btn-filter">APLICAR FILTROS</button>
            
            <?php if($buscar != "" || !empty($marcas_seleccionadas) || $rango_precio != ""): ?>
                <a href="index.php" class="btn-clear">Limpiar filtros</a>
            <?php endif; ?>

        </form>
    </aside>

    <main class="main-content">
        
        <?php if ($rol == 'admin'): ?>
        <div class="admin-panel">
            <div class="stat-box">
                <h4>Ingresos Totales</h4>
                <h2>$<?php echo number_format($total_vnt, 2); ?></h2>
                <a href="reporte_ventas.php" style="font-size: 0.85rem; color: var(--accent); text-decoration: none; font-weight: bold;">Ver reporte &rarr;</a>
            </div>
            <a href="agregar.php" class="btn-add"><i class="fa-solid fa-plus"></i> Añadir Zapato</a>
        </div>
        <?php endif; ?>

        <div class="grid-productos">
            <?php if ($resultado && mysqli_num_rows($resultado) > 0): ?>
                <?php while($f = mysqli_fetch_assoc($resultado)): 
                    // CAMBIO CLAVE: Usamos 'stock_dinamico' que viene de la subconsulta SUM()
                    $stk = isset($f['stock_dinamico']) ? intval($f['stock_dinamico']) : 0;
                ?>
                    <div class="card">
                        <div class="img-container">
                            <img src="img/<?php echo !empty($f['imagen']) ? $f['imagen'] : 'sin_foto.png'; ?>" class="card-img" alt="Zapato">
                        </div>
                        <div class="card-body">
                            <span class="card-brand"><?php echo htmlspecialchars($f['marca'] ?? 'General'); ?></span>
                            <h3 class="card-title"><?php echo htmlspecialchars($f['nombre']); ?></h3>
                            
                            <div class="stock-badge <?php echo ($stk > 0) ? 'stock-ok' : 'stock-out'; ?>">
                                <?php echo ($stk > 0) ? "En stock: $stk" : "Agotado"; ?>
                            </div>

                            <div class="card-price">$<?php echo number_format($f['precio_base'], 2); ?></div>

                            <?php if ($stk > 0): ?>
                               <a href="producto.php?id=<?php echo $f['id_producto']; ?>" class="btn-buy" style="background: white; color: var(--primary); border: 2px solid var(--primary);"><i class="fa-regular fa-eye"></i> Ver Detalles</a>
                            <?php else: ?>
                                <div class="btn-buy" style="background: #e2e8f0; color: #94a3b8; cursor:not-allowed;"><i class="fa-solid fa-ban"></i> Agotado</div>
                            <?php endif; ?>

                            <?php if ($rol == 'admin'): ?>
                                <div class="admin-actions">
                                    <a href="editar.php?id=<?php echo $f['id_producto']; ?>" class="edit-link"><i class="fa-regular fa-pen-to-square"></i> Editar</a>
                                    <a href="eliminar.php?id=<?php echo $f['id_producto']; ?>" class="delete-link" onclick="return confirm('¿Borrar producto?')"><i class="fa-regular fa-trash-can"></i> Borrar</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 60px; background: white; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                    <i class="fa-solid fa-box-open" style="font-size: 3.5rem; color: #cbd5e1; margin-bottom: 20px;"></i>
                    <h3 style="color: var(--primary); margin-bottom: 10px; font-size: 1.5rem;">Sin resultados</h3>
                    <p style="color: var(--text-muted); font-size: 1.1rem;">No encontramos zapatos que coincidan con esos filtros.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>
<?php ob_end_flush(); ?>