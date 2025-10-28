<?php
require_once '../auth.php';
require_once '../config.php';

// Requiere autenticación
requerirAutenticacion();

$usuario = obtenerUsuarioActual();
$conn = getConnection();

// Configuración de paginación
$registros_por_pagina = isset($_GET['size']) ? (int)$_GET['size'] : 50;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Obtener parámetros de ordenamiento y filtros
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'fecha_registro_desc';
$filtro_cliente = isset($_GET['filtro_cliente']) ? trim($_GET['filtro_cliente']) : '';
$filtro_brigada = isset($_GET['filtro_brigada']) ? trim($_GET['filtro_brigada']) : '';

// Construir cláusula WHERE para filtros
$where_conditions = [];
$params = [];
$types = '';

if (!empty($filtro_cliente)) {
    $where_conditions[] = "cliente LIKE ?";
    $params[] = "%$filtro_cliente%";
    $types .= 's';
}

if (!empty($filtro_brigada)) {
    $where_conditions[] = "brigada LIKE ?";
    $params[] = "%$filtro_brigada%";
    $types .= 's';
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Determinar ORDER BY según selección
$order_by = match($orden) {
    'fecha_registro_asc' => 'fecha_registro ASC, id ASC',
    'fecha_registro_desc' => 'fecha_registro DESC, id DESC',
    'fecha_asc' => 'fecha ASC, id ASC',
    'fecha_desc' => 'fecha DESC, id DESC',
    'os_asc' => 'CAST(os AS UNSIGNED) ASC, os ASC',
    'os_desc' => 'CAST(os AS UNSIGNED) DESC, os DESC',
    'cliente_asc' => 'cliente ASC',
    'cliente_desc' => 'cliente DESC',
    'sitio_asc' => 'sitio ASC',
    'sitio_desc' => 'sitio DESC',
    default => 'fecha_registro DESC, id DESC'
};

// Contar total de registros con filtros
$sql_count = "SELECT COUNT(*) as total FROM bitacoras $where_clause";
if (!empty($params)) {
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->bind_param($types, ...$params);
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $stmt_count->close();
} else {
    $result_count = $conn->query($sql_count);
}
$total_registros = $result_count->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Obtener bitácoras con ordenamiento y filtros
$sql = "SELECT * FROM bitacoras $where_clause ORDER BY $order_by LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

// Bind params dinámicamente
if (!empty($params)) {
    $types .= 'ii';
    $params[] = $registros_por_pagina;
    $params[] = $offset;
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $registros_por_pagina, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

// Obtener listas únicas para filtros
$clientes = $conn->query("SELECT DISTINCT cliente FROM bitacoras ORDER BY cliente ASC");
$brigadas = $conn->query("SELECT DISTINCT brigada FROM bitacoras ORDER BY brigada ASC");
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
        .stats-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .stats-info {
            font-weight: bold;
            color: #333;
        }
        .stats-info span {
            color: #0077C8;
        }
        
        /* Filtros y ordenamiento */
        .filters-section {
            background: #e7f5ff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #0077C8;
        }
        .filters-title {
            font-weight: bold;
            color: #0077C8;
            margin-bottom: 15px;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .filter-group label {
            font-weight: bold;
            font-size: 12px;
            color: #333;
            text-transform: uppercase;
        }
        .filter-group select,
        .filter-group input {
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border 0.3s;
        }
        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #0077C8;
        }
        .filters-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .btn-filter {
            padding: 10px 25px;
            background: #0077C8;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-filter:hover {
            background: #005a96;
        }
        .btn-clear {
            padding: 10px 25px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-clear:hover {
            background: #5a6268;
        }
        .toggle-filters {
            background: #0077C8;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }
        .toggle-filters:hover {
            background: #005a96;
        }
        .filters-collapsed {
            display: none;
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
        
        /* Estilos de paginación */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .pagination a,
        .pagination span {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
            font-weight: bold;
            transition: all 0.3s;
        }
        .pagination a:hover {
            background: #0077C8;
            color: white;
            border-color: #0077C8;
        }
        .pagination .active {
            background: #0077C8;
            color: white;
            border-color: #0077C8;
        }
        .pagination .disabled {
            opacity: 0.5;
            pointer-events: none;
        }
        .page-size-selector {
            margin-left: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .page-size-selector select {
            padding: 8px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
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

        <div class="stats-bar">
            <div class="stats-info">
                Total de registros: <span><?php echo $total_registros; ?></span>
            </div>
            <div class="stats-info">
                Página <span><?php echo $pagina_actual; ?></span> de <span><?php echo $total_paginas; ?></span>
            </div>
            <div class="page-size-selector">
                <label>Mostrar:</label>
                <select id="pageSize" onchange="changePageSize(this.value)">
                    <option value="50" <?php echo $registros_por_pagina == 50 ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo $registros_por_pagina == 100 ? 'selected' : ''; ?>>100</option>
                    <option value="200" <?php echo $registros_por_pagina == 200 ? 'selected' : ''; ?>>200</option>
                    <option value="500" <?php echo $registros_por_pagina == 500 ? 'selected' : ''; ?>>500</option>
                    <option value="<?php echo $total_registros; ?>">Todos</option>
                </select>
            </div>
        </div>

        <div style="text-align: center; margin-bottom: 20px;">
            <button class="toggle-filters" onclick="toggleFilters()">
                🔽 Mostrar Filtros y Ordenamiento
            </button>
        </div>

        <!-- Sección de Filtros y Ordenamiento -->
        <div id="filtersSection" class="filters-section filters-collapsed">
            <div class="filters-title">
                🎯 Filtros y Ordenamiento
            </div>
            
            <form method="GET" action="">
                <div class="filters-grid">
                    <!-- Ordenar por -->
                    <div class="filter-group">
                        <label>📊 Ordenar por</label>
                        <select name="orden" id="orden">
                            <option value="fecha_registro_desc" <?php echo $orden == 'fecha_registro_desc' ? 'selected' : ''; ?>>
                                Fecha de Registro (Más reciente)
                            </option>
                            <option value="fecha_registro_asc" <?php echo $orden == 'fecha_registro_asc' ? 'selected' : ''; ?>>
                                Fecha de Registro (Más antiguo)
                            </option>
                            <option value="fecha_desc" <?php echo $orden == 'fecha_desc' ? 'selected' : ''; ?>>
                                Fecha de Actividad (Más reciente)
                            </option>
                            <option value="fecha_asc" <?php echo $orden == 'fecha_asc' ? 'selected' : ''; ?>>
                                Fecha de Actividad (Más antiguo)
                            </option>
                            <option value="os_desc" <?php echo $orden == 'os_desc' ? 'selected' : ''; ?>>
                                OS (Mayor a menor)
                            </option>
                            <option value="os_asc" <?php echo $orden == 'os_asc' ? 'selected' : ''; ?>>
                                OS (Menor a mayor)
                            </option>
                            <option value="cliente_asc" <?php echo $orden == 'cliente_asc' ? 'selected' : ''; ?>>
                                Cliente (A-Z)
                            </option>
                            <option value="cliente_desc" <?php echo $orden == 'cliente_desc' ? 'selected' : ''; ?>>
                                Cliente (Z-A)
                            </option>
                            <option value="sitio_asc" <?php echo $orden == 'sitio_asc' ? 'selected' : ''; ?>>
                                Sitio (A-Z)
                            </option>
                            <option value="sitio_desc" <?php echo $orden == 'sitio_desc' ? 'selected' : ''; ?>>
                                Sitio (Z-A)
                            </option>
                        </select>
                    </div>

                    <!-- Filtrar por Cliente -->
                    <div class="filter-group">
                        <label>🏢 Filtrar por Cliente</label>
                        <select name="filtro_cliente" id="filtro_cliente">
                            <option value="">Todos los clientes</option>
                            <?php while($cliente = $clientes->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($cliente['cliente']); ?>" 
                                        <?php echo $filtro_cliente == $cliente['cliente'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cliente['cliente']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Filtrar por Brigada -->
                    <div class="filter-group">
                        <label>👥 Filtrar por Brigada</label>
                        <select name="filtro_brigada" id="filtro_brigada">
                            <option value="">Todas las brigadas</option>
                            <?php while($brigada = $brigadas->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($brigada['brigada']); ?>" 
                                        <?php echo $filtro_brigada == $brigada['brigada'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($brigada['brigada']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="filters-actions">
                    <button type="submit" class="btn-filter">🔍 Aplicar Filtros</button>
                    <button type="button" class="btn-clear" onclick="limpiarFiltros()">🔄 Limpiar</button>
                </div>

                <!-- Mantener parámetros de paginación -->
                <input type="hidden" name="pagina" value="1">
                <input type="hidden" name="size" value="<?php echo $registros_por_pagina; ?>">
            </form>
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
                            <td><?php echo htmlspecialchars($row['fecha']); ?></td>
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

            <!-- Paginación -->
            <div class="pagination">
                <?php
                // Construir URL con parámetros actuales
                $params_url = [];
                if (!empty($orden)) $params_url[] = "orden=$orden";
                if (!empty($filtro_cliente)) $params_url[] = "filtro_cliente=" . urlencode($filtro_cliente);
                if (!empty($filtro_brigada)) $params_url[] = "filtro_brigada=" . urlencode($filtro_brigada);
                if (!empty($_GET['size'])) $params_url[] = "size=" . $_GET['size'];
                $base_url = "?" . implode("&", $params_url);
                $separator = empty($params_url) ? "?" : "&";
                ?>
                
                <!-- Primera página -->
                <a href="<?php echo $base_url . $separator; ?>pagina=1" class="<?php echo $pagina_actual <= 1 ? 'disabled' : ''; ?>">
                    ⏮️ Primera
                </a>
                
                <!-- Página anterior -->
                <a href="<?php echo $base_url . $separator; ?>pagina=<?php echo max(1, $pagina_actual - 1); ?>" 
                   class="<?php echo $pagina_actual <= 1 ? 'disabled' : ''; ?>">
                    ◀️ Anterior
                </a>

                <!-- Páginas numeradas -->
                <?php
                $rango = 2;
                $inicio = max(1, $pagina_actual - $rango);
                $fin = min($total_paginas, $pagina_actual + $rango);

                if ($inicio > 1) {
                    echo '<a href="' . $base_url . $separator . 'pagina=1">1</a>';
                    if ($inicio > 2) echo '<span>...</span>';
                }

                for ($i = $inicio; $i <= $fin; $i++) {
                    if ($i == $pagina_actual) {
                        echo '<span class="active">' . $i . '</span>';
                    } else {
                        echo '<a href="' . $base_url . $separator . 'pagina=' . $i . '">' . $i . '</a>';
                    }
                }

                if ($fin < $total_paginas) {
                    if ($fin < $total_paginas - 1) echo '<span>...</span>';
                    echo '<a href="' . $base_url . $separator . 'pagina=' . $total_paginas . '">' . $total_paginas . '</a>';
                }
                ?>

                <!-- Página siguiente -->
                <a href="<?php echo $base_url . $separator; ?>pagina=<?php echo min($total_paginas, $pagina_actual + 1); ?>" 
                   class="<?php echo $pagina_actual >= $total_paginas ? 'disabled' : ''; ?>">
                    Siguiente ▶️
                </a>
                
                <!-- Última página -->
                <a href="<?php echo $base_url . $separator; ?>pagina=<?php echo $total_paginas; ?>" 
                   class="<?php echo $pagina_actual >= $total_paginas ? 'disabled' : ''; ?>">
                    Última ⏭️
                </a>
            </div>

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

        function changePageSize(size) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('pagina', '1');
            urlParams.set('size', size);
            window.location.href = '?' + urlParams.toString();
        }

        function toggleFilters() {
            const section = document.getElementById('filtersSection');
            const button = document.querySelector('.toggle-filters');
            
            if (section.classList.contains('filters-collapsed')) {
                section.classList.remove('filters-collapsed');
                button.textContent = '🔼 Ocultar Filtros y Ordenamiento';
            } else {
                section.classList.add('filters-collapsed');
                button.textContent = '🔽 Mostrar Filtros y Ordenamiento';
            }
        }

        function limpiarFiltros() {
            const urlParams = new URLSearchParams(window.location.search);
            const size = urlParams.get('size') || '50';
            window.location.href = '?pagina=1&size=' + size;
        }

        // Auto-expandir filtros si hay filtros aplicados
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('orden') || urlParams.has('filtro_cliente') || urlParams.has('filtro_brigada')) {
                const section = document.getElementById('filtersSection');
                const button = document.querySelector('.toggle-filters');
                section.classList.remove('filters-collapsed');
                button.textContent = '🔼 Ocultar Filtros y Ordenamiento';
            }
        });
    </script>
</body>
</html>
<?php 
$stmt->close();
$conn->close(); 
?>