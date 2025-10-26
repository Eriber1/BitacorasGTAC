<?php
require_once '../auth.php';
require_once '../config.php';

// Requiere autenticación
requerirAutenticacion();

$usuario = obtenerUsuarioActual();
$conn = getConnection();

// Obtener todas las bitácoras ordenadas por fecha
$sql = "SELECT * FROM bitacoras ORDER BY fecha DESC, id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Bitácoras - GTAC</title>
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
        .user-info {
            text-align: right;
            font-size: 14px;
            color: #666;
        }
        .user-info strong {
            color: #333;
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
        .menu a.logout:hover {
            background: #c82333;
        }
        .search-box {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .search-box input {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #0077C8;
            color: white;
            font-weight: bold;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 12px;
            margin-right: 5px;
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
        .no-data {
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
                <h1>📋 Bitácoras Registradas</h1>
            </div>
            <div class="user-info">
                👤 <strong><?php echo htmlspecialchars($usuario['nombre']); ?></strong><br>
                <small><?php echo htmlspecialchars($usuario['usuario']); ?></small>
            </div>
        </div>

        <div class="menu">
            <a href="../index.php">📝 Formulario Público</a>
            <a href="buscar.php">🔎 Buscar</a>
            <a href="importar_csv.php">📤 Importar CSV</a>
            <a href="../logout.php" class="logout">🚪 Cerrar Sesión</a>
        </div>

        <div class="search-box">
            <input type="text" id="searchInput" placeholder="🔍 Buscar por OS, Cliente, Sitio o Brigada..." onkeyup="filtrarTabla()">
        </div>

        <?php if ($result->num_rows > 0): ?>
            <table id="tablaBitacoras">
                <thead>
                    <tr>
                        <th>OS</th>
                        <th>Cliente</th>
                        <th>Sitio</th>
                        <th>Fecha</th>
                        <th>Brigada</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['os']); ?></td>
                            <td><?php echo htmlspecialchars($row['cliente']); ?></td>
                            <td><?php echo htmlspecialchars($row['sitio']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['fecha'])); ?></td>
                            <td><?php echo htmlspecialchars($row['brigada']); ?></td>
                            <td>
                                <a href="generar_pdf.php?id=<?php echo $row['id']; ?>" class="btn btn-pdf" target="_blank">📄 PDF</a>
                                <a href="ver_bitacora.php?id=<?php echo $row['id']; ?>" class="btn btn-view">👁️ Ver</a>
                                <a href="eliminar_bitacora.php?id=<?php echo $row['id']; ?>" class="btn btn-delete" onclick="return confirm('¿Está seguro de eliminar esta bitácora?')">🗑️</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                <h3>No hay bitácoras registradas</h3>
                <p>Las bitácoras aparecerán aquí cuando se registren desde el formulario público</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function filtrarTabla() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('tablaBitacoras');
            const tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < td.length - 1; j++) {
                    if (td[j]) {
                        const txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                
                tr[i].style.display = found ? '' : 'none';
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>