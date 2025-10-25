<?php
require_once '../auth.php';
require_once '../config.php';

// Requiere autenticación
requerirAutenticacion();

if (!isset($_GET['id'])) {
    die('ID de bitácora no especificado');
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
    <title>Bitácora <?php echo $bitacora['os']; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 10px;
            background-color: white;
        }
        @page {
            size: A4;
            margin: 10mm;
        }
        .container {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 0;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }
        .no-print {
            text-align: center;
            margin: 20px 0;
        }
        .btn-pdf {
            background-color: #0077C8;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }
        .btn-pdf:hover {
            background: #005a96;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
        }
        .logo-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 15px;
            border-bottom: 2px solid #000;
            background-color: white;
        }
        .logo {
            height: 40px;
            object-fit: contain;
        }
        .logo-gtac {
            max-width: 250px;
        }
        .logo-huawei {
            max-width: 130px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-collapse: collapse;
        }
        .info-cell {
            border: 1px solid #000;
            padding: 4px 8px;
            display: flex;
            align-items: center;
            font-size: 12px;
        }
        .info-cell.full-width {
            grid-column: 1 / -1;
        }
        .label {
            font-weight: bold;
            background-color: white;
            padding-right: 10px;
            white-space: nowrap;
        }
        .value {
            flex: 1;
            min-height: 20px;
        }
        .content {
            display: flex;
            flex-direction: column;
            flex: 1;
            overflow: hidden;
        }
        .bitacora-section {
            border: 1px solid #000;
            padding: 10px;
            min-height: 250px;
            overflow-y: auto;
        }
        .bitacora-text {
            width: 100%;
            min-height: 250px;
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            white-space: pre-wrap;
        }
        .images-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            height: 593px;
            flex: 1;
        }
        .image-box {
            border: 1px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f5f5f5;
            position: relative;
            padding: 5px;
            overflow: hidden;
        }
        .image-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .no-image {
            color: #999;
            font-size: 14px;
        }
        @media print {
            body {
                padding: 0;
            }
            .container {
                border: 2px solid #000;
                page-break-after: avoid;
            }
            .content {
                page-break-inside: avoid;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-pdf" onclick="window.print()">📄 Imprimir / Guardar PDF</button>
        <a href="consultar.php" class="btn-back">⬅️ Volver</a>
    </div>

    <div class="container">
        <div class="logo-header">
            <img src="https://asignaciones.gtac.com.mx/static/img/LogoGTAC.png" alt="LogoGTAC" class="logo logo-gtac">
            <img src="https://1000marcas.net/wp-content/uploads/2019/12/Huawei-Logo-768x432.png" alt="Huawei Logo" class="logo logo-huawei">
        </div>

        <div class="info-grid">
            <div class="info-cell">
                <span class="label">OS</span>
                <span class="value"><?php echo htmlspecialchars($bitacora['os']); ?></span>
            </div>
            <div class="info-cell">
                <span class="label">Pagina No.</span>
                <span class="value">1 de 1</span>
            </div>
            
            <div class="info-cell">
                <span class="label">Cliente</span>
                <span class="value"><?php echo htmlspecialchars($bitacora['cliente']); ?></span>
            </div>
            <div class="info-cell">
                <span class="label">Brigada</span>
                <span class="value"><?php echo htmlspecialchars($bitacora['brigada']); ?></span>
            </div>
            
            <div class="info-cell">
                <span class="label">Sitio</span>
                <span class="value"><?php echo htmlspecialchars($bitacora['sitio']); ?></span>
            </div>
            <div class="info-cell">
                <span class="label">FM/NOC</span>
                <span class="value"><?php echo htmlspecialchars($bitacora['fm_acceso'] ?? '') . ' / ' . htmlspecialchars($bitacora['noc_acceso'] ?? ''); ?></span>
            </div>
            
            <div class="info-cell full-width">
                <span class="label">Fecha</span>
                <span class="value"><?php echo date('d/m/Y', strtotime($bitacora['fecha'])); ?></span>
            </div>
        </div>
        
        <div class="content">
            <div class="bitacora-section">
                <div class="bitacora-text"><?php echo htmlspecialchars($bitacora['bitacora']); ?></div>
            </div>
            
            <div class="images-grid">
                <div class="image-box">
                    <?php if (!empty($bitacora['foto_clock_in'])): ?>
                        <img src="../<?php echo UPLOAD_DIR . $bitacora['foto_clock_in']; ?>" alt="Clock In">
                    <?php else: ?>
                        <span class="no-image">Sin imagen</span>
                    <?php endif; ?>
                </div>
                <div class="image-box">
                    <?php if (!empty($bitacora['foto_clock_out'])): ?>
                        <img src="../<?php echo UPLOAD_DIR . $bitacora['foto_clock_out']; ?>" alt="Clock Out">
                    <?php else: ?>
                        <span class="no-image">Sin imagen</span>
                    <?php endif; ?>
                </div>
                <div class="image-box">
                    <?php if (!empty($bitacora['foto_etiquetas'])): ?>
                        <img src="../<?php echo UPLOAD_DIR . $bitacora['foto_etiquetas']; ?>" alt="Etiquetas">
                    <?php else: ?>
                        <span class="no-image">Sin imagen</span>
                    <?php endif; ?>
                </div>
                <div class="image-box">
                    <?php if (!empty($bitacora['foto_extra'])): ?>
                        <img src="../<?php echo UPLOAD_DIR . $bitacora['foto_extra']; ?>" alt="Extra">
                    <?php else: ?>
                        <span class="no-image">Sin imagen</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>