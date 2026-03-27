<?php
session_start();

// 1. Inicializar el carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// 2. Verificar que recibimos el ID y la Talla
if (isset($_GET['id']) && isset($_GET['talla'])) {
    
    $id_producto = intval($_GET['id']);
    $talla_elegida = $_GET['talla']; // Ejemplo: "40" o "38"

    // 3. Creamos una "Llave Única" combinada (ID-TALLA)
    // Esto permite que el carrito distinga entre el mismo modelo pero diferente número
    $item_id = $id_producto . "-" . $talla_elegida;

    // 4. Agregamos al carrito si no está ya adentro
    // Usamos el ID combinado como valor en el arreglo
    if (!in_array($item_id, $_SESSION['carrito'])) {
        $_SESSION['carrito'][] = $item_id;
    }

    // 5. Redirigir al carrito para que el cliente vea su elección
    header("Location: carrito.php");
    exit();

} else {
    // Si alguien intenta entrar a comprar.php sin elegir talla, lo mandamos al inicio
    header("Location: index.php");
    exit();
}
?>