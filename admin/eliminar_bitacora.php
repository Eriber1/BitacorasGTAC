<?php
require_once '../auth.php';
require_once '../config.php';

// Requiere autenticación
requerirAutenticacion();

if (!isset($_GET['id'])) {
    header('Location: consultar.php');
    exit;
}

$conn = getConnection();
$id = intval($_GET['id']);

// Obtener información de las fotos para eliminarlas
$stmt = $conn->prepare("SELECT foto_clock_in, foto_clock_out, foto_etiquetas, foto_extra FROM bitacoras WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $bitacora = $result->fetch_assoc();
    
    // Eliminar archivos de fotos
    $fotos = ['foto_clock_in', 'foto_clock_out', 'foto_etiquetas', 'foto_extra'];
    foreach ($fotos as $foto) {
        if (!empty($bitacora[$foto])) {
            $filepath = '../' . UPLOAD_DIR . $bitacora[$foto];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
    }
    
    // Eliminar registro de base de datos
    $stmt_delete = $conn->prepare("DELETE FROM bitacoras WHERE id = ?");
    $stmt_delete->bind_param("i", $id);
    $stmt_delete->execute();
    $stmt_delete->close();
}

$stmt->close();
$conn->close();

header('Location: consultar.php');
exit;
?>
