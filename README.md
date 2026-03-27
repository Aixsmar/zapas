# 👟 Aura Footwear - Sistema de Gestión de Zapatería

Este es un sistema web funcional desarrollado en **PHP y MySQL** para la gestión de una zapatería. Permite la visualización de catálogo, filtrado de productos, sistema de carrito de compras y gestión de inventario automatizado por tallas.

## 🚀 Características principales
- **Catálogo Dinámico:** Los productos se muestran directamente desde la base de datos.
- **Filtros Avanzados:** Búsqueda por nombre, marca y rangos de precio.
- **Inventario por Tallas:** Al realizar una compra, el sistema descuenta automáticamente el stock de la talla específica en la base de datos.
- **Panel de Administrador:** Visualización de ingresos totales y gestión de productos.
- **Diseño Premium:** Interfaz limpia y moderna utilizando CSS personalizado y FontAwesome.

## 🛠️ Instalación y Configuración

### 1. Requisitos
- Servidor local (XAMPP, WAMP o Laragon).
- PHP 7.4 o superior.
- MySQL / MariaDB.

### 2. Base de Datos
1. Entra a `phpMyAdmin`.
2. Crea una nueva base de datos llamada `zapateria_bd`.
3. Importa el archivo `database.sql` incluido en este repositorio.

### 3. Configuración del Código
1. Mueve la carpeta del proyecto a `C:/xampp/htdocs/`.
2. Revisa el archivo `conexion.php` y asegúrate de que los credenciales de tu base de datos sean correctos:
   ```php
   $conexion = mysqli_connect("localhost", "root", "", "zapateria_bd");