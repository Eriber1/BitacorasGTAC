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
    <!-- titulo del documeto pdf  2025XXXX_BITACORA_SITIO_OS_XXXX -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bitacora_<?php echo $bitacora['sitio']; ?>_OS_<?php echo $bitacora['os']; ?></title>
    
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
           border: none;
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
        border: 2px solid #000;
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
            height: 100px;
            object-fit: contain;
        }
        .logo-gtac {
            max-width: 250px;
        }
        .logo-huawei {
            max-width: 130px;
        }
        
/* INICIO: Reemplazo de estilos de info-grid */
.info-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    border-top: 1px solid #000; /* Borde superior para la tabla */
    border-bottom: 1px solid #000; /* Borde inferior para la tabla */
}
.info-table td {
    border-left: 1px solid #000;
    border-right: 1px solid #000;
    border-top: 1px solid #000; /* Borde superior de celda (para líneas internas) */
    border-bottom: 1px solid #000; /* Borde inferior de celda (para líneas internas) */
    padding: 4px 8px;
    height: 20px; /* Altura mínima */
    vertical-align: middle;
}
.label-cell {
    font-weight: bold;
    white-space: nowrap;
}
.value-cell {
   width: auto; /* La celda de valor ocupa el espacio restante */
}
/* FIN: Reemplazo de estilos */


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
               border: none;
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

        
<table class="info-table">
    <tr>
        <td class="label-cell" style="width: 1%;">OS</td>
        <td class="value-cell" style="padding-left: 2px; width: 50%;">
            <?php echo htmlspecialchars($bitacora['os']); ?>
        </td>
        
        <td class="value-cell" colspan="2" style="font-weight: bold; text-align: center;">
            Pagina No. 1 de 1
        </td>
    </tr>
    <tr>
        <td class="label-cell" style="width: 1%;">Cliente</td>
        <td class="value-cell" style="padding-left: 2px; width: 50%;">
            <?php echo htmlspecialchars($bitacora['cliente']); ?>
        </td>
        
        <td class="label-cell" colspan="2" style="text-align: center;">
            Brigada
        </td>
    </tr>
    <tr>
        <td class="label-cell" style="width: 1%;">Sitio</td>
        <td class="value-cell" style="padding-left: 2px; width: 50%;">
            <?php echo htmlspecialchars($bitacora['sitio']); ?>
        </td>
        
        <td class="value-cell" colspan="2" style="text-align: center;">
            <?php echo htmlspecialchars($bitacora['brigada']); ?>
        </td>
    </tr>
    <tr>
        <td class="label-cell" style="width: 1%;">Fecha</td>
        <td class="value-cell" colspan="3" style="padding-left: 2px;">
            <?php echo htmlspecialchars($bitacora['fecha']); ?>
        </td>
    </tr>
</table>

        <div class="content">
            <div class="bitacora-section">
                <div class="bitacora-text"><?php echo htmlspecialchars($bitacora['bitacora']); ?></div>
            </div>
            
            <div class="images-grid">
                <?php
                // Función para generar URL de imagen
                function getGoogleDriveImageUrl($fileId) {
                    if (empty($fileId)) return '';
                    
                    // Opción 1: Usar thumbnail API (prueba primero esta)
                    return "https://drive.google.com/thumbnail?id=" . $fileId . "&sz=w1000";
                    
                    // Opción 2: Si no funciona, descomentar esta línea y comentar la anterior
                    // return "proxy_imagen.php?id=" . urlencode($fileId);
                }
                
                // DEBUG: Mostrar IDs de fotos
                echo "<!-- DEBUG INFO:\n";
                echo "foto_clock_in: " . ($bitacora['foto_clock_in'] ?? 'NULL') . "\n";
                echo "foto_clock_out: " . ($bitacora['foto_clock_out'] ?? 'NULL') . "\n";
                echo "foto_etiquetas: " . ($bitacora['foto_etiquetas'] ?? 'NULL') . "\n";
                echo "foto_extra: " . ($bitacora['foto_extra'] ?? 'NULL') . "\n";
                
                if (!empty($bitacora['foto_clock_in'])) {
                    echo "URL Clock In: " . getGoogleDriveImageUrl($bitacora['foto_clock_in']) . "\n";
                }
                echo "-->\n";
                ?>
                
                <div class="image-box">
                    <?php if (!empty($bitacora['foto_clock_in'])): ?>
                        <img src="<?php echo getGoogleDriveImageUrl($bitacora['foto_clock_in']); ?>" alt="Clock In">
                        <!-- ID: <?php echo htmlspecialchars($bitacora['foto_clock_in']); ?> -->
                    <?php else: ?>
                        <span class="no-image">Sin imagen</span>
                    <?php endif; ?>
                </div>
                
                <div class="image-box">
                    <?php if (!empty($bitacora['foto_clock_out'])): ?>
                        <img src="<?php echo getGoogleDriveImageUrl($bitacora['foto_clock_out']); ?>" alt="Clock Out">
                        <!-- ID: <?php echo htmlspecialchars($bitacora['foto_clock_out']); ?> -->
                    <?php else: ?>
                        <span class="no-image">Sin imagen</span>
                    <?php endif; ?>
                </div>
                
                <div class="image-box">
                    <?php if (!empty($bitacora['foto_etiquetas'])): ?>
                        <img src="<?php echo getGoogleDriveImageUrl($bitacora['foto_etiquetas']); ?>" alt="Etiquetas">
                        <!-- ID: <?php echo htmlspecialchars($bitacora['foto_etiquetas']); ?> -->
                    <?php else: ?>
                        <span class="no-image">Sin imagen</span>
                    <?php endif; ?>
                </div>
                
                <div class="image-box">
                    <?php if (!empty($bitacora['foto_extra'])): ?>
                        <img src="<?php echo getGoogleDriveImageUrl($bitacora['foto_extra']); ?>" alt="Extra">
                        <!-- ID: <?php echo htmlspecialchars($bitacora['foto_extra']); ?> -->
                    <?php else: ?>
                        <span class="no-image">Sin imagen</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>