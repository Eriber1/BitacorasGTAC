<?php
require_once '../auth.php';
require_once '../config.php';

// Requiere autenticación
requerirAutenticacion();

$usuario = obtenerUsuarioActual();
$conn = getConnection();

$resultados = [];
$busqueda = '';

if (isset($_GET['buscar'])) {
    $busqueda = trim($_GET['buscar']);
    
    if (!empty($busqueda)) {
        $stmt = $conn->prepare("SELECT * FROM bitacoras WHERE os LIKE ? OR sitio LIKE ? ORDER BY fecha DESC");
        $search_param = "%$busqueda%";
        $stmt->bind_param("ss", $search_param, $search_param);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $resultados[] = $row;
        }
        
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Bitácoras - GTAC</title>
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
            max-width: 1200px;
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
        .search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
        }
        .search-form input {
            flex: 1;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .search-form button {
            padding: 12px 30px;
            background: #0077C8;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        .result-count {
            margin-bottom: 20px;
            padding: 15px;
            background: #e7f5ff;
            border-left: 4px solid #0077C8;
            border-radius: 5px;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background: #f8f9fa;
        }
        .card h3 {
            color: #0077C8;
            margin-bottom: 15px;
        }
        .card-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-bottom: 15px;
        }
        .card-info-item {
            display: flex;
            flex-direction: column;
        }
        .card-info-label {
            font-weight: bold;
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        .card-info-value {
            font-size: 14px;
            color: #333;
        }
        .card-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 13px;
            font-weight: bold;
        }
        .btn-pdf {
            background: #dc3545;
            color: white;
        }
        .btn-view {
            background: #28a745;
            color: white;
        }
        .btn-delete {
            background: #6c757d;
            color: white;
        }
        .no-results {
            text-align: center;
            padding: 40px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-left">
                <h1>🔎 Buscar Bitácoras</h1>
                <p>Busque por número de OS o nombre del sitio</p>
            </div>
            <div class="user-info">
                👤 <strong><?php echo htmlspecialchars($usuario['nombre']); ?></strong>
            </div>
        </div>

        <div class="menu">
            <a href="consultar.php">📋 Todas las Bitácoras</a>
            <a href="importar_csv.php">📤 Importar CSV</a>
            <a href="../index.php">📝 Formulario Público</a>
            <a href="../logout.php" class="logout">🚪 Cerrar Sesión</a>
        </div>

        <form method="GET" class="search-form">
            <input type="text" name="buscar" placeholder="Ingrese OS o Sitio..." value="<?php echo htmlspecialchars($busqueda); ?>" required>
            <button type="submit">🔍 Buscar</button>
        </form>

        <?php if (isset($_GET['buscar'])): ?>
            <div class="result-count">
                <strong><?php echo count($resultados); ?></strong> resultado(s) encontrado(s) para: <strong>"<?php echo htmlspecialchars($busqueda); ?>"</strong>
            </div>

            <?php if (count($resultados) > 0): ?>
                <?php foreach ($resultados as $bitacora): ?>
                    <div class="card">
                        <h3>OS: <?php echo htmlspecialchars($bitacora['os']); ?> - <?php echo htmlspecialchars($bitacora['sitio']); ?></h3>
                        
                        <div class="card-info">
                            <div class="card-info-item">
                                <span class="card-info-label">Cliente</span>
                                <span class="card-info-value"><?php echo htmlspecialchars($bitacora['cliente']); ?></span>
                            </div>
                            <div class="card-info-item">
                                <span class="card-info-label">Fecha</span>
                                <span class="card-info-value"><?php echo htmlspecialchars($bitacora['fecha']); ?></span>
                            </div>
                            <div class="card-info-item">
                                <span class="card-info-label">Brigada</span>
                                <span class="card-info-value"><?php echo htmlspecialchars($bitacora['brigada']); ?></span>
                            </div>
                            <div class="card-info-item">
                                <span class="card-info-label">FM Acceso</span>
                                <span class="card-info-value"><?php echo htmlspecialchars($bitacora['fm_acceso'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="card-info-item">
                                <span class="card-info-label">NOC Acceso</span>
                                <span class="card-info-value"><?php echo htmlspecialchars($bitacora['noc_acceso'] ?? 'N/A'); ?></span>
                            </div>
                        </div>

                        <div class="card-info-item">
                            <span class="card-info-label">Bitácora</span>
                            <span class="card-info-value"><?php echo nl2br(htmlspecialchars(substr($bitacora['bitacora'], 0, 200))); ?>...</span>
                        </div>

                        <div class="card-actions">
                            <a href="generar_pdf.php?id=<?php echo $bitacora['id']; ?>" class="btn btn-pdf" target="_blank">📄 Generar PDF</a>
                            <a href="ver_bitacora.php?id=<?php echo $bitacora['id']; ?>" class="btn btn-view">👁️ Ver Completa</a>
                            <a href="eliminar_bitacora.php?id=<?php echo $bitacora['id']; ?>" class="btn btn-delete" onclick="return confirm('¿Está seguro de eliminar esta bitácora?')">🗑️ Eliminar</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">
                    <h3>No se encontraron resultados</h3>
                    <p>Intente con otro término de búsqueda</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>