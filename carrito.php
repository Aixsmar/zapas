<?php
ob_start();
session_start();
include 'conexion.php';

// 1. SEGURIDAD: Si no hay sesión, al login
if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

$nombre_user = !empty($_SESSION['nombre']) ? $_SESSION['nombre'] : (!empty($_SESSION['usuario']) ? $_SESSION['usuario'] : 'Usuario');

// --- LÓGICA PARA QUITAR UN ARTÍCULO ESPECÍFICO ---
if (isset($_GET['quitar'])) {
    $item_id_quitar = $_GET['quitar']; // Ejemplo: "6-40"
    $indice = array_search($item_id_quitar, $_SESSION['carrito']);
    if ($indice !== false) {
        unset($_SESSION['carrito'][$indice]);
        $_SESSION['carrito'] = array_values($_SESSION['carrito']); // Reindexar
    }
    header("Location: carrito.php");
    exit();
}

// --- LÓGICA PARA VACIAR TODO EL CARRITO ---
if (isset($_GET['vaciar'])) {
    $_SESSION['carrito'] = [];
    header("Location: carrito.php");
    exit();
}

$cantidad_carrito = isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0;
$total_pagar = 0;
$items_procesados = []; 

// --- 2. PROCESAMIENTO DE LOS ITEMS (ID-TALLA) ---
if ($cantidad_carrito > 0) {
    foreach ($_SESSION['carrito'] as $codigo_item) {
        // Separamos el ID de la Talla. Ej: "6-40" ->
        $partes = explode('-', $codigo_item);
        
        // CORRECCIÓN TÉCNICA: Acceder a los índices del array generado por explode
         $id_actual = isset($partes[0]) ? intval($partes[0]) : 0;
         $talla_actual = isset($partes[1]) ? $partes[1] : 'N/A';

        // Buscamos los datos del producto en la base de datos
        $sql = "SELECT p.*, m.nombre AS marca 
                FROM productos p 
                LEFT JOIN marcas m ON p.id_marca = m.id_marca 
                WHERE p.id_producto = $id_actual";
        
        $res = mysqli_query($conexion, $sql);
        
        if ($res && mysqli_num_rows($res) > 0) {
            $fila = mysqli_fetch_assoc($res);
            $fila['talla_seleccionada'] = $talla_actual;
            $fila['codigo_carrito'] = $codigo_item;
            $items_procesados[] = $fila; 
            $total_pagar += $fila['precio_base'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Carrito - Aura Footwear</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --primary: #0b1a2a; 
            --accent: #b48a47; 
            --bg-color: #f4f7f6; 
            --text-dark: #1e293b; 
            --text-muted: #64748b; 
            --border-color: #e2e8f0; 
        }
        
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg-color); color: var(--text-dark); }
        
        /* Navbar */
        nav { background: var(--primary); color: white; padding: 1rem 3rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
        .nav-brand { font-weight: 800; letter-spacing: 1px; display: flex; align-items: center; gap: 10px; }
        .nav-right { display: flex; align-items: center; gap: 25px; }
        .cart-icon { position: relative; color: var(--accent); font-size: 1.3rem; text-decoration: none; }
        .cart-badge { position: absolute; top: -8px; right: -10px; background: #ef4444; color: white; font-size: 0.7rem; font-weight: bold; padding: 2px 6px; border-radius: 50%; border: 2px solid var(--primary); }

        /* Layout */
        .layout { max-width: 1200px; margin: 3rem auto; padding: 0 2rem; display: flex; gap: 2.5rem; align-items: flex-start; }
        .cart-items { flex: 1; }
        .cart-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 1.5rem; }
        
        /* Tarjetas de Producto */
        .item-card { background: white; border-radius: 15px; padding: 1.5rem; display: flex; align-items: center; gap: 1.5rem; margin-bottom: 1rem; box-shadow: 0 5px 15px rgba(0,0,0,0.03); border: 1px solid var(--border-color); }
        .item-img { width: 100px; height: 100px; background: #f8fafc; border-radius: 10px; display: flex; justify-content: center; align-items: center; padding: 10px;}
        .item-img img { max-width: 100%; max-height: 100%; object-fit: contain; mix-blend-mode: multiply; }
        
        .item-details { flex: 1; }
        .item-brand { color: var(--accent); font-size: 0.7rem; font-weight: 800; text-transform: uppercase; }
        .item-title { font-size: 1.1rem; color: var(--primary); font-weight: bold; margin: 0.2rem 0; }
        .item-size-badge { display: inline-block; background: #0b1a2a; color: white; padding: 2px 10px; border-radius: 5px; font-size: 0.8rem; font-weight: bold; margin-top: 5px; }
        
        .item-price { font-size: 1.3rem; font-weight: 800; }
        .btn-remove { color: var(--text-muted); text-decoration: none; font-size: 0.85rem; margin-top: 10px; display: inline-block; transition: 0.2s; }
        .btn-remove:hover { color: #ef4444; }

        /* Resumen */
        .order-summary { width: 350px; background: white; border-radius: 20px; padding: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.05); position: sticky; top: 100px; border: 1px solid var(--border-color); }
        .summary-total { display: flex; justify-content: space-between; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px dashed var(--border-color); font-size: 1.4rem; font-weight: 800; color: var(--primary); }
        .btn-checkout { display: block; width: 100%; background: var(--accent); color: white; text-align: center; padding: 16px; border-radius: 12px; text-decoration: none; font-weight: bold; margin-top: 2rem; box-shadow: 0 5px 15px rgba(180, 138, 71, 0.3); transition: 0.3s; }
        .btn-checkout:hover { transform: translateY(-3px); background: #9c763a; }
        
        .empty-cart { text-align: center; padding: 4rem 2rem; background: white; border-radius: 20px; width: 100%; border: 1px solid var(--border-color); }

        @media (max-width: 900px) {
            .layout { flex-direction: column; }
            .order-summary { width: 100%; position: static; }
        }
    </style>
</head>
<body>

<nav>
    <div class="nav-brand">
        <i class="fa-solid fa-shoe-prints" style="color:var(--accent); font-size:1.6rem;"></i> AURA FOOTWEAR
    </div>
    <div class="nav-right">
        <div class="user-greeting">Hola, <span><?php echo htmlspecialchars($nombre_user); ?></span></div>
        <a href="carrito.php" class="cart-icon">
            <i class="fa-solid fa-cart-shopping"></i>
            <span class="cart-badge"><?php echo $cantidad_carrito; ?></span>
        </a>
    </div>
</nav>

<div class="layout">
    <div class="cart-items">
        <div class="cart-header">
            <h2>Mi Bolsa (<?php echo $cantidad_carrito; ?>)</h2>
            <?php if ($cantidad_carrito > 0): ?>
                <a href="carrito.php?vaciar=true" style="color:#ef4444; text-decoration:none; font-size:0.9rem; font-weight:600;">
                    <i class="fa-solid fa-trash-can"></i> Vaciar carrito
                </a>
            <?php endif; ?>
        </div>

        <?php if ($cantidad_carrito > 0 && !empty($items_procesados)): ?>
            <?php foreach ($items_procesados as $prod): ?>
                <div class="item-card">
                    <div class="item-img">
                        <img src="img/<?php echo !empty($prod['imagen']) ? $prod['imagen'] : 'sin_foto.png'; ?>" alt="Zapato">
                    </div>
                    <div class="item-details">
                        <div class="item-brand"><?php echo htmlspecialchars($prod['marca']); ?></div>
                        <div class="item-title"><?php echo htmlspecialchars($prod['nombre']); ?></div>
                        <div class="item-size-badge">Talla: <?php echo $prod['talla_seleccionada']; ?></div>
                        <br>
                        <a href="carrito.php?quitar=<?php echo $prod['codigo_carrito']; ?>" class="btn-remove">
                            <i class="fa-solid fa-xmark"></i> Eliminar
                        </a>
                    </div>
                    <div class="item-price">
                        $<?php echo number_format($prod['precio_base'], 2); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-cart">
                <i class="fa-solid fa-bag-shopping" style="font-size:3rem; color:#cbd5e1; margin-bottom:1rem;"></i>
                <h3>Tu bolsa está vacía</h3>
                <p style="color:var(--text-muted); margin-bottom: 1.5rem;">Añade algunos zapatos para verlos aquí.</p>
                <a href="index.php" class="btn-checkout" style="display:inline-block; width:auto; padding: 12px 30px;">
                    Ir a la tienda
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($cantidad_carrito > 0): ?>
    <div class="order-summary">
        <h3>Resumen</h3>
        <div style="display:flex; justify-content:space-between; margin-top:1rem; color:var(--text-muted);">
            <span>Subtotal</span>
            <span>$<?php echo number_format($total_pagar, 2); ?></span>
        </div>
        <div style="display:flex; justify-content:space-between; margin-top:0.5rem; color:var(--text-muted);">
            <span>Envío</span>
            <span style="color:#16a34a; font-weight:bold;">Gratis</span>
        </div>
        
        <div class="summary-total">
            <span>Total</span>
            <span>$<?php echo number_format($total_pagar, 2); ?></span>
        </div>
        
        <a href="confirmar_pago.php" class="btn-checkout">Finalizar Compra</a>
        <a href="index.php" style="display:block; text-align:center; color:var(--primary); text-decoration:none; margin-top:1rem; font-weight:600;">
            Seguir comprando
        </a>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
<?php ob_end_flush(); ?>