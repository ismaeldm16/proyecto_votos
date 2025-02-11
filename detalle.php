<?php
include 'conexion.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Producto no especificado.");
}

$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    die("Producto no encontrado.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Producto</title>
</head>
<body>
    <h1><?php echo htmlspecialchars($producto['nombre']); ?></h1>
    <p>Descripción: <?php echo htmlspecialchars($producto['descripcion']); ?></p>
</body>
</html>
