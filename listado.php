<?php
include 'conexion.php';
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$es_admin = isset($_SESSION['es_admin']) && $_SESSION['es_admin'];

// Procesar la adición de productos (solo para admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevo_producto']) && $es_admin) {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];

    $stmt = $pdo->prepare("INSERT INTO productos (nombre, descripcion) VALUES (?, ?)");
    $stmt->execute([$nombre, $descripcion]);
}

// Procesar la eliminación de productos (solo para admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrar_producto']) && $es_admin) {
    $id = $_POST['id'];

    $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->execute([$id]);
}

// Procesar la actualización de productos (solo para admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_producto']) && $es_admin) {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];

    $stmt = $pdo->prepare("UPDATE productos SET nombre = ?, descripcion = ? WHERE id = ?");
    $stmt->execute([$nombre, $descripcion, $id]);
}

// Obtener todos los productos
$stmt = $pdo->query("SELECT * FROM productos");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Función para mostrar estrellas con medias estrellas
function mostrarEstrellas($media) {
    $media = round($media * 2) / 2;
    $estrellas = str_repeat("★", floor($media));
    if ($media - floor($media) == 0.5) {
        $estrellas .= "⯪"; // Media estrella
    }
    $estrellas .= str_repeat("☆", 5 - ceil($media));
    return $estrellas;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Productos</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?></h1>

        <?php if ($es_admin): ?>
            <h2>Gestión de Productos</h2>
            
            <!-- Formulario para añadir un producto -->
            <h3>Añadir Producto</h3>
            <form method="POST">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required>
                <br>
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" required></textarea>
                <br>
                <button type="submit" name="nuevo_producto">Añadir Producto</button>
            </form>
        <?php endif; ?>

        <h2>Lista de Productos</h2>
        <table border="1">
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Valoración</th>
                <th>Acciones</th>
            </tr>
            <?php foreach ($productos as $producto): ?>
                <tr>
                    <td><?php echo htmlspecialchars($producto['id']); ?></td>
                    <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                    <td id="media-<?php echo $producto['id']; ?>">
                        <?php
                        $stmt = $pdo->prepare("SELECT AVG(cantidad) AS media, COUNT(*) AS total FROM votos WHERE idPr = ?");
                        $stmt->execute([$producto['id']]);
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($result['total'] > 0) {
                            echo mostrarEstrellas($result['media']) . " (" . $result['total'] . " votos)";
                        } else {
                            echo "Sin valorar";
                        }
                        ?>
                    </td>
                    <td>
                        <?php if ($es_admin): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
                                <button type="submit" name="borrar_producto" onclick="return confirm('¿Estás seguro de que deseas eliminar este producto?');">Eliminar</button>
                            </form>
                        <?php endif; ?>
                        <select id="valoracion-<?php echo $producto['id']; ?>">
                            <option value="" selected disabled>Valorar</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?> ★</option>
                            <?php endfor; ?>
                        </select>
                        <button onclick="guardarVoto(<?php echo $producto['id']; ?>)">Guardar Valoración</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <br>
        <a href="logout.php">Cerrar Sesión</a>
    </div>

    <script>
        async function guardarVoto(productoId) {
            const select = document.querySelector(`#valoracion-${productoId}`);
            const voto = select.value;

            if (!voto) {
                alert("Por favor, selecciona una valoración antes de guardar.");
                return;
            }

            const response = await fetch('voto.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `productoId=${productoId}&voto=${voto}`
            });

            const data = await response.json();

            if (data.success) {
                const mediaElement = document.querySelector(`#media-${productoId}`);
                mediaElement.innerHTML = `${data.estrellas} (${data.total} votos)`;
                alert("✅ Valoración guardada con éxito.");
            } else {
                alert(data.message);
            }
        }
    </script>
</body>
</html>
