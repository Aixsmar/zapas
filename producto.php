<?php
ob_start();
session_start();
include 'conexion.php';

// Seguridad
if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

$nombre_user = !empty($_SESSION['nombre']) ? $_SESSION['nombre'] : (!empty($_SESSION['usuario']) ? $_SESSION['usuario'] : 'Usuario');
$cantidad_carrito = isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0;

// Validar que recibimos un ID de zapato válido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_prod = intval($_GET['id']);

// 1. Traer información general del zapato
$sql = "SELECT p.*, m.nombre AS marca FROM productos p LEFT JOIN marcas m ON p.id_marca = m.id_marca WHERE p.id_producto = $id_prod";
$res = mysqli_query($conexion, $sql);

if (mysqli_num_rows($res) == 0) {
    header("Location: index.php"); // Si el zapato no existe, lo regresamos al inicio
    exit();
}
$producto = mysqli_fetch_assoc($res);

// 2. Traer las tallas disponibles de este zapato específico
$sql_tallas = "SELECT * FROM producto_tallas WHERE id_producto = $id_prod AND stock > 0 ORDER BY talla ASC";
$res_tallas = mysqli_query($conexion, $sql_tallas);

$hay_tallas = false;
// Verificamos que la consulta no haya fallado antes de contar las filas
if ($res_tallas) {
    $hay_tallas = mysqli_num_rows($res_tallas) > 0;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($producto['nombre']); ?> - Aura Footwear</title>
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
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--bg-color); color: var(--text-dark); }
        
        /* Navbar */
        nav { background: var(--primary); color: white; padding: 1rem 3rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
        .nav-brand { display: flex; align-items: center; }
        .nav-right { display: flex; align-items: center; gap: 25px; }
        .cart-icon { position: relative; color: white; font-size: 1.3rem; text-decoration: none; transition: 0.3s; }
        .cart-icon:hover { color: var(--accent); }
        .cart-badge { position: absolute; top: -8px; right: -10px; background: #ef4444; color: white; font-size: 0.7rem; font-weight: bold; padding: 2px 6px; border-radius: 50%; border: 2px solid var(--primary); }

        .btn-back { display: inline-block; margin: 2rem 0 0 3rem; color: var(--text-muted); text-decoration: none; font-weight: bold; font-size: 0.95rem; transition: 0.2s;}
        .btn-back:hover { color: var(--primary); }

        /* Estructura del Producto */
        .product-container { max-width: 1200px; margin: 2rem auto; display: flex; gap: 4rem; background: white; padding: 3rem; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid var(--border-color); }
        
        /* Lado Izquierdo: Imagen */
        .product-image { flex: 1; background: #f8fafc; border-radius: 15px; display: flex; justify-content: center; align-items: center; padding: 2rem; border: 1px solid var(--border-color); }
        .product-image img { width: 100%; max-width: 500px; object-fit: contain; mix-blend-mode: multiply; transform: rotate(-5deg); filter: drop-shadow(0 20px 20px rgba(0,0,0,0.1)); }
        
        /* Lado Derecho: Detalles */
        .product-details { flex: 1; display: flex; flex-direction: column; justify-content: center; }
        .p-brand { color: var(--accent); font-weight: 800; text-transform: uppercase; letter-spacing: 2px; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .p-title { font-size: 2.5rem; color: var(--primary); font-weight: 900; margin-bottom: 1rem; line-height: 1.1; }
        .p-price { font-size: 2rem; font-weight: 800; margin-bottom: 1.5rem; color: var(--text-dark); }
        .p-desc { color: var(--text-muted); line-height: 1.6; margin-bottom: 2rem; font-size: 1.05rem; }

        /* Selector de Tallas */
        .sizes-header { font-weight: 700; margin-bottom: 1rem; color: var(--primary); display: flex; justify-content: space-between; align-items: center;}
        .size-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(60px, 1fr)); gap: 10px; margin-bottom: 2rem; }
        
        .size-option input[type="radio"] { display: none; }
        .size-option label { display: block; border: 2px solid var(--border-color); border-radius: 8px; text-align: center; padding: 12px 0; font-weight: bold; cursor: pointer; transition: 0.2s; color: var(--text-muted); }
        .size-option label:hover { border-color: var(--primary); color: var(--primary); }
        .size-option input[type="radio"]:checked + label { border-color: var(--accent); background: var(--accent); color: white; }

        .btn-add { background: var(--primary); color: white; border: none; padding: 18px; border-radius: 12px; font-size: 1.1rem; font-weight: bold; cursor: pointer; transition: 0.3s; width: 100%; box-shadow: 0 10px 20px rgba(11, 26, 42, 0.2); display: flex; justify-content: center; align-items: center; gap: 10px;}
        .btn-add:hover { background: var(--primary-light); transform: translateY(-3px); }
        .btn-disabled { background: #cbd5e1; cursor: not-allowed; box-shadow: none; }
        .btn-disabled:hover { transform: none; background: #cbd5e1; }

        @media (max-width: 900px) {
            .product-container { flex-direction: column; padding: 2rem; }
            .product-image img { transform: none; }
        }
    </style>
</head>
<body>

<nav>
    <div class="nav-brand">
        <i class="fa-solid fa-shoe-prints" style="color:var(--accent); font-size:1.6rem; margin-right:10px;"></i> 
        <span style="font-weight: 800; letter-spacing: 1px;">AURA FOOTWEAR</span>
    </div>
    <div class="nav-right">
        <div class="user-greeting">Hola, <span><?php echo htmlspecialchars($nombre_user); ?></span></div>
        <a href="carrito.php" class="cart-icon">
            <i class="fa-solid fa-cart-shopping"></i>
            <span class="cart-badge"><?php echo $cantidad_carrito; ?></span>
        </a>
    </div>
</nav>

<a href="index.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Volver al catálogo</a>

<div class="product-container">
    <div class="product-image">
        <img src="img/<?php echo !empty($producto['imagen']) ? $producto['imagen'] : 'sin_foto.png'; ?>" alt="Zapato">
    </div>
    
    <div class="product-details">
        <div class="p-brand"><?php echo htmlspecialchars($producto['marca'] ?? 'General'); ?></div>
        <h1 class="p-title"><?php echo htmlspecialchars($producto['nombre']); ?></h1>
        <div class="p-price">$<?php echo number_format($producto['precio_base'], 2); ?></div>
        
        <p class="p-desc">
            <?php echo !empty($producto['descripcion']) ? htmlspecialchars($producto['descripcion']) : 'Este calzado premium de Aura Footwear ofrece comodidad, durabilidad y un estilo inigualable para tu día a día.'; ?>
        </p>

        <form action="comprar.php" method="GET">
            <input type="hidden" name="id" value="<?php echo $producto['id_producto']; ?>">
            
            <div class="sizes-header">Selecciona tu talla (US)</div>
            
            <?php if ($hay_tallas): ?>
                <div class="size-grid">
                    <?php while($talla = mysqli_fetch_assoc($res_tallas)): ?>
                        <div class="size-option">
                            <input type="radio" name="talla" id="talla_<?php echo $talla['id_talla']; ?>" value="<?php echo $talla['talla']; ?>" required>
                            <label for="talla_<?php echo $talla['id_talla']; ?>"><?php echo htmlspecialchars($talla['talla']); ?></label>
                        </div>
                    <?php endwhile; ?>
                </div>
                <button type="submit" class="btn-add"><i class="fa-solid fa-bag-shopping"></i> Añadir al Carrito</button>
            <?php else: ?>
                <div style="color: #ef4444; font-weight: bold; margin-bottom: 2rem; background: #fee2e2; padding: 15px; border-radius: 10px;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Tallas agotadas por el momento.
                </div>
                <button type="button" class="btn-add btn-disabled" disabled>Agotado</button>
            <?php endif; ?>
        </form>
    </div>
</div>

</body>
</html>
<?php ob_end_flush(); ?>