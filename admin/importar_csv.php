<?php
require_once '../auth.php';
require_once '../config.php';

// Requiere autenticación
requerirAutenticacion();

$usuario = obtenerUsuarioActual();
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    try {
        $file = $_FILES['csv_file'];
        
        // Validar archivo
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error al subir el archivo');
        }
        
        if ($file['type'] !== 'text/csv' && !str_ends_with($file['name'], '.csv')) {
            throw new Exception('El archivo debe ser un CSV');
        }
        
        // Leer archivo CSV
        $handle = fopen($file['tmp_name'], 'r');
        if ($handle === false) {
            throw new Exception('No se pudo abrir el archivo CSV');
        }
        
        // Leer encabezados
        $headers = fgetcsv($handle);
        if ($headers === false) {
            throw new Exception('El archivo CSV está vacío');
        }
        
        // Limpiar BOM (Byte Order Mark) y espacios
        $headers = array_map(function($h) {
            // Remover BOM UTF-8
            $h = str_replace("\xEF\xBB\xBF", '', $h);
            // Remover espacios y convertir a minúsculas
            return strtolower(trim($h));
        }, $headers);
        
        // Mapeo de nombres alternativos
        $header_map = [
            'foto_clok_in' => 'foto_clock_in',
            'foto_clok_out' => 'foto_clock_out',
            'bitacora ' => 'bitacora',  // Con espacio
        ];
        
        // Normalizar encabezados
        foreach ($headers as $key => $header) {
            if (isset($header_map[$header])) {
                $headers[$key] = $header_map[$header];
            }
        }
        
        // Validar encabezados requeridos
        $required = ['os', 'cliente', 'sitio', 'fecha', 'brigada', 'bitacora'];
        $missing = [];
        foreach ($required as $req) {
            if (!in_array($req, $headers)) {
                $missing[] = $req;
            }
        }
        
        if (!empty($missing)) {
            throw new Exception("Faltan encabezados requeridos: " . implode(', ', $missing) . ". Encabezados encontrados: " . implode(', ', $headers));
        }
        
        $conn = getConnection();
        $insertados = 0;
        $errores = [];
        
        // Preparar statement
        $stmt = $conn->prepare("INSERT INTO bitacoras 
            (os, cliente, sitio, fecha, brigada, bitacora, foto_clock_in, foto_clock_out, foto_etiquetas, foto_extra, fm_acceso, noc_acceso) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        // Leer cada fila
        $linea = 2; // Empezamos en 2 (línea 1 son encabezados)
        while (($data = fgetcsv($handle)) !== false) {
            try {
                // Crear array asociativo
                $row = array_combine($headers, $data);
                
                // Validar campos requeridos
                foreach ($required as $field) {
                    if (empty(trim($row[$field]))) {
                        throw new Exception("Campo '$field' vacío en línea $linea");
                    }
                }
                
                // Convertir fecha de DD/MM/YYYY a YYYY-MM-DD
                $fecha = trim($row['fecha']);
                if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $fecha, $matches)) {
                    // Formato DD/MM/YYYY
                    $dia = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                    $mes = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                    $año = $matches[3];
                    $fecha = "$año-$mes-$dia";
                } elseif (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $fecha)) {
                    // Ya está en formato YYYY-MM-DD, no hacer nada
                } else {
                    throw new Exception("Formato de fecha inválido: $fecha (use DD/MM/YYYY o YYYY-MM-DD)");
                }
                
                // Preparar datos con normalización de nombres
                $os = isset($row['os']) ? trim($row['os']) : '';
                $cliente = isset($row['cliente']) ? trim($row['cliente']) : '';
                $sitio = isset($row['sitio']) ? trim($row['sitio']) : '';
                $brigada = isset($row['brigada']) ? trim($row['brigada']) : '';
                $bitacora = isset($row['bitacora']) ? trim($row['bitacora']) : '';
                
                // Campos opcionales con nombres alternativos
                $foto_clock_in = null;
                if (isset($row['foto_clock_in'])) {
                    $foto_clock_in = trim($row['foto_clock_in']);
                } elseif (isset($row['foto_clok_in'])) {
                    $foto_clock_in = trim($row['foto_clok_in']);
                }
                
                $foto_clock_out = null;
                if (isset($row['foto_clock_out'])) {
                    $foto_clock_out = trim($row['foto_clock_out']);
                } elseif (isset($row['foto_clok_out'])) {
                    $foto_clock_out = trim($row['foto_clok_out']);
                }
                
                $foto_etiquetas = isset($row['foto_etiquetas']) ? trim($row['foto_etiquetas']) : null;
                $foto_extra = isset($row['foto_extra']) ? trim($row['foto_extra']) : null;
                $fm_acceso = isset($row['fm_acceso']) ? trim($row['fm_acceso']) : null;
                $noc_acceso = isset($row['noc_acceso']) ? trim($row['noc_acceso']) : null;
                
                // Limpiar valores vacíos
                $foto_clock_in = empty($foto_clock_in) ? null : $foto_clock_in;
                $foto_clock_out = empty($foto_clock_out) ? null : $foto_clock_out;
                $foto_etiquetas = empty($foto_etiquetas) ? null : $foto_etiquetas;
                $foto_extra = empty($foto_extra) ? null : $foto_extra;
                $fm_acceso = empty($fm_acceso) ? null : $fm_acceso;
                $noc_acceso = empty($noc_acceso) ? null : $noc_acceso;
                
                // Insertar en BD
                $stmt->bind_param(
                    "ssssssssssss",
                    $os, $cliente, $sitio, $fecha, $brigada, $bitacora,
                    $foto_clock_in, $foto_clock_out, $foto_etiquetas, $foto_extra,
                    $fm_acceso, $noc_acceso
                );
                
                if ($stmt->execute()) {
                    $insertados++;
                } else {
                    $errores[] = "Línea $linea: Error al insertar - " . $stmt->error;
                }
                
            } catch (Exception $e) {
                $errores[] = "Línea $linea: " . $e->getMessage();
            }
            
            $linea++;
        }
        
        fclose($handle);
        $stmt->close();
        $conn->close();
        
        // Mensaje de resultado
        $mensaje = "✅ Importación completada: $insertados registros insertados.";
        if (count($errores) > 0) {
            $mensaje .= "\n⚠️ " . count($errores) . " errores encontrados:\n\n";
            // Mostrar primeros 10 errores
            $errores_mostrar = array_slice($errores, 0, 10);
            foreach ($errores_mostrar as $error) {
                $mensaje .= "• " . $error . "\n";
            }
            if (count($errores) > 10) {
                $mensaje .= "\n... y " . (count($errores) - 10) . " errores más.";
            }
        }
        $tipo_mensaje = $insertados > 0 ? 'success' : 'error';
        
    } catch (Exception $e) {
        $mensaje = "❌ Error: " . $e->getMessage();
        $tipo_mensaje = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar CSV - GTAC</title>
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
            max-width: 900px;
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
        .header-left p {
            color: #666;
            font-size: 14px;
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
        .menu a.logout {
            background: #dc3545;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            white-space: pre-line;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .upload-section {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .file-upload {
            margin: 20px 0;
        }
        .file-upload input[type="file"] {
            display: block;
            width: 100%;
            padding: 10px;
            border: 2px dashed #ddd;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-upload {
            width: 100%;
            padding: 15px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 20px;
        }
        .btn-upload:hover {
            background: #218838;
        }
        .info-box {
            background: #e7f5ff;
            padding: 20px;
            border-left: 4px solid #0077C8;
            border-radius: 5px;
            margin-top: 20px;
        }
        .info-box h3 {
            color: #0077C8;
            margin-bottom: 10px;
        }
        .info-box ul {
            margin-left: 20px;
            color: #333;
        }
        .info-box code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-left">
                <h1>📤 Importar Bitácoras desde CSV</h1>
                <p>Carga masiva de registros</p>
            </div>
            <div class="user-info">
                👤 <strong><?php echo htmlspecialchars($usuario['nombre']); ?></strong>
            </div>
        </div>

        <div class="menu">
            <a href="consultar.php">📋 Ver Bitácoras</a>
            <a href="buscar.php">🔎 Buscar</a>
            <a href="../logout.php" class="logout">🚪 Cerrar Sesión</a>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <div class="upload-section">
            <h2>Subir archivo CSV</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="file-upload">
                    <input type="file" name="csv_file" accept=".csv" required>
                </div>
                <button type="submit" class="btn-upload">📤 Importar CSV</button>
            </form>
        </div>

        <div class="info-box">
            <h3>📋 Formato del archivo CSV</h3>
            <p><strong>Columnas requeridas:</strong></p>
            <ul>
                <li><code>os</code> - Número de orden de servicio</li>
                <li><code>cliente</code> - Nombre del cliente</li>
                <li><code>sitio</code> - Ubicación del sitio</li>
                <li><code>fecha</code> - Formato: DD/MM/YYYY o YYYY-MM-DD</li>
                <li><code>brigada</code> - Nombre de la brigada</li>
                <li><code>bitacora</code> - Descripción de actividades</li>
            </ul>
            <p style="margin-top: 15px;"><strong>Columnas opcionales:</strong></p>
            <ul>
                <li><code>foto_clok_in</code> - ID de foto clock in</li>
                <li><code>foto_clok_out</code> - ID de foto clock out</li>
                <li><code>foto_etiquetas</code> - ID de foto etiquetas</li>
                <li><code>foto_extra</code> - ID de foto extra</li>
                <li><code>fm_acceso</code> - FM que da acceso</li>
                <li><code>noc_acceso</code> - NOC que da acceso</li>
            </ul>
            <p style="margin-top: 15px;"><strong>⚠️ Notas importantes:</strong></p>
            <ul>
                <li>El archivo debe ser UTF-8</li>
                <li>Primera fila debe contener los encabezados</li>
                <li>Los IDs de fotos son opcionales (Google Drive IDs)</li>
            </ul>
        </div>
    </div>
</body>
</html>