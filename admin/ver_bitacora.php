<?php
require_once '../auth.php';
require_once '../config.php';

// Requiere autenticación
requerirAutenticacion();

$usuario = obtenerUsuarioActual();

if (!isset($_GET['id'])) {
    header('Location: consultar.php');
    exit;
}

$conn = getConnection();
$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM bitacoras WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Bitácora no encontrada');
}

$bitacora = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Bitácora - OS <?php echo htmlspecialchars($bitacora['os']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 30px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #0077C8;
        }
        .header-left h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 5px;
        }
        .user-info {
            text-align: right;
            font-size: 14px;
            color: #666;
        }
        .menu {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .menu a {
            padding: 10px 20px;
            background: #0077C8;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background 0.3s;
        }
        .menu a:hover {
            background: #005a96;
        }
        .menu a.btn-pdf {
            background: #dc3545;
        }
        .menu a.btn-pdf:hover {
            background: #c82333;
        }
        .info-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            font-weight: bold;
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .info-value {
            font-size: 16px;
            color: #333;
        }
        .bitacora-section {
            background: white;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .bitacora-section h2 {
            color: #0077C8;
            margin-bottom: 15px;
            font-size: 20px;
        }
        .bitacora-text {
            white-space: pre-wrap;
            line-height: 1.6;
            color: #333;
        }
        .images-section {
            margin-top: 20px;
        }
        .images-section h2 {
            color: #0077C8;
            margin-bottom: 15px;
            font-size: 20px;
        }
        .images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .image-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            background: white;
        }
        .image-card-header {
            background: #f8f9fa;
            padding: 10px;
            font-weight: bold;
            color: #333;
            border-bottom: 1px solid #ddd;
        }
        .image-card-body {
            padding: 10px;
            min-height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f5f5;
        }
        .image-card-body img {
            max-width: 100%;
            max-height: 300px;
            object-fit: contain;
        }
        .no-image {
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-left">
                <h1>📋 Bitácora OS: <?php echo htmlspecialchars($bitacora['os']); ?></h1>
            </div>
            <div class="user-info">
                👤 <strong><?php echo htmlspecialchars($usuario['nombre']); ?></strong>
            </div>
        </div>

        <div class="menu">
            <a href="consultar.php">⬅️ Volver al Listado</a>
            <a href="generar_pdf.php?id=<?php echo $bitacora['id']; ?>" class="btn-pdf" target="_blank">📄 Generar PDF</a>
            <a href="eliminar_bitacora.php?id=<?php echo $bitacora['id']; ?>" onclick="return confirm('¿Está seguro de eliminar esta bitácora?')" style="background: #6c757d;">🗑️ Eliminar</a>
        </div>

        <div class="info-section">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">OS</span>
                    <span class="info-value"><?php echo htmlspecialchars($bitacora['os']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Cliente</span>
                    <span class="info-value"><?php echo htmlspecialchars($bitacora['cliente']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Sitio</span>
                    <span class="info-value"><?php echo htmlspecialchars($bitacora['sitio']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Fecha</span>
                    <span class="info-value"><?php echo date('d/m/Y', strtotime($bitacora['fecha'])); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Brigada</span>
                    <span class="info-value"><?php echo htmlspecialchars($bitacora['brigada']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">FM Acceso</span>
                    <span class="info-value"><?php echo htmlspecialchars($bitacora['fm_acceso'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">NOC Acceso</span>
                    <span class="info-value"><?php echo htmlspecialchars($bitacora['noc_acceso'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Fecha de Registro</span>
                    <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($bitacora['fecha_registro'])); ?></span>
                </div>
            </div>
        </div>

        <div class="bitacora-section">
            <h2>📝 Descripción de Actividades</h2>
            <div class="bitacora-text"><?php echo htmlspecialchars($bitacora['bitacora']); ?></div>
        </div>

        <div class="images-section">
            <h2>📸 Fotografías</h2>
            <div class="images-grid">
                <?php
                // Función para generar URL de Google Drive
                function getGoogleDriveImageUrl($fileId) {
                    if (empty($fileId)) return '';
                    return 'https://lh3.googleusercontent.com/d/' . $fileId;
                }
                ?>
                
                <div class="image-card">
                    <div class="image-card-header">🕐 Clock In</div>
                    <div class="image-card-body">
                        <?php if (!empty($bitacora['foto_clock_in'])): ?>
                            <img src="<?php echo getGoogleDriveImageUrl($bitacora['foto_clock_in']); ?>" alt="Clock In">
                        <?php else: ?>
                            <span class="no-image">Sin imagen</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="image-card">
                    <div class="image-card-header">🕐 Clock Out</div>
                    <div class="image-card-body">
                        <?php if (!empty($bitacora['foto_clock_out'])): ?>
                            <img src="<?php echo getGoogleDriveImageUrl($bitacora['foto_clock_out']); ?>" alt="Clock Out">
                        <?php else: ?>
                            <span class="no-image">Sin imagen</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="image-card">
                    <div class="image-card-header">🏷️ Etiquetas Punta A</div>
                    <div class="image-card-body">
                        <?php if (!empty($bitacora['foto_etiquetas'])): ?>
                            <img src="<?php echo getGoogleDriveImageUrl($bitacora['foto_etiquetas']); ?>" alt="Etiquetas">
                        <?php else: ?>
                            <span class="no-image">Sin imagen</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="image-card">
                    <div class="image-card-header">📷 tiquetas Punta B</div>
                    <div class="image-card-body">
                        <?php if (!empty($bitacora['foto_extra'])): ?>
                            <img src="<?php echo getGoogleDriveImageUrl($bitacora['foto_extra']); ?>" alt="Extra">
                        <?php else: ?>
                            <span class="no-image">Sin imagen</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>