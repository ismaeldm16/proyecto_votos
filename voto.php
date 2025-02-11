<?php
include 'conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productoId = $_POST['productoId'];
    $voto = $_POST['voto'];
    $usuarioId = $_SESSION['usuario'];

    // Verificar si el usuario ya votó este producto
    $stmt = $pdo->prepare("SELECT * FROM votos WHERE idPr = ? AND idUs = ?");
    $stmt->execute([$productoId, $usuarioId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => '⚠️ Ya has votado este producto.']);
        exit;
    }

    // Insertar el nuevo voto
    $stmt = $pdo->prepare("INSERT INTO votos (idPr, idUs, cantidad) VALUES (?, ?, ?)");
    $stmt->execute([$productoId, $usuarioId, $voto]);

    // Calcular la nueva media y el total de votos
    $stmt = $pdo->prepare("SELECT AVG(cantidad) AS media, COUNT(*) AS total FROM votos WHERE idPr = ?");
    $stmt->execute([$productoId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Generar estrellas con medias estrellas
    function mostrarEstrellas($media) {
        $media = round($media * 2) / 2; // Redondear a 0.5 más cercano
        $estrellas = str_repeat("★", floor($media)); // Estrellas llenas
        if ($media - floor($media) == 0.5) {
            $estrellas .= "⯪"; // Media estrella
        }
        $estrellas .= str_repeat("☆", 5 - ceil($media)); // Estrellas vacías
        return $estrellas;
    }

    echo json_encode([
        'success' => true,
        'media' => round($result['media'], 1),
        'total' => $result['total'],
        'estrellas' => mostrarEstrellas($result['media'])
    ]);
}
?>
