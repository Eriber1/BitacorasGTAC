<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Bitácora - GTAC</title>
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
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #0077C8;
        }
        .header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .header p {
            color: #666;
            font-size: 14px;
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
        .form-group {
            margin-bottom: 20px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        label {
            display: block;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
            font-size: 14px;
        }
        input[type="text"],
        input[type="date"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: Arial, sans-serif;
            transition: border 0.3s;
        }
        input[type="text"]:focus,
        input[type="date"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #0077C8;
        }
        textarea {
            min-height: 150px;
            resize: vertical;
        }
        .file-upload {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }
        .file-upload input[type="file"] {
            position: absolute;
            left: -9999px;
        }
        .file-label {
            display: block;
            padding: 10px;
            background: #f5f5f5;
            border: 2px dashed #ddd;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .file-label:hover {
            background: #e8e8e8;
            border-color: #0077C8;
        }
        .file-label.has-file {
            background: #e7f5ff;
            border-color: #0077C8;
            color: #0077C8;
        }
        .images-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 10px;
        }
        .btn-submit {
            width: 100%;
            padding: 15px;
            background: #0077C8;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 20px;
        }
        .btn-submit:hover {
            background: #005a96;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Nueva Bitácora de Sitio</h1>
            <p>Sistema de Gestión de Bitácoras GTAC</p>
        </div>

        <div class="menu">
            <a href="index.php">🏠 Nueva Bitácora</a>
            <a href="login.php">🔐 Acceso Administrativo</a>
        </div>

        <div id="mensaje"></div>

        <form id="formBitacora" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="os">OS *</label>
                    <input type="text" id="os" name="os" required>
                </div>
                <div class="form-group">
                    <label for="cliente">Cliente *</label>
                    <input type="text" id="cliente" name="cliente" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="sitio">Sitio *</label>
                    <input type="text" id="sitio" name="sitio" required>
                </div>
                <div class="form-group">
                    <label for="fecha">Fecha *</label>
                    <input type="date" id="fecha" name="fecha" required>
                </div>
            </div>

            <div class="form-group">
                <label for="brigada">Brigada *</label>
                <input type="text" id="brigada" name="brigada" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="fm_acceso">FM que da acceso</label>
                    <input type="text" id="fm_acceso" name="fm_acceso">
                </div>
                <div class="form-group">
                    <label for="noc_acceso">NOC que da acceso</label>
                    <input type="text" id="noc_acceso" name="noc_acceso">
                </div>
            </div>

            <div class="form-group">
                <label for="bitacora">Bitácora *</label>
                <textarea id="bitacora" name="bitacora" required placeholder="Describa las actividades realizadas..."></textarea>
            </div>

            <h3 style="margin: 30px 0 15px 0; color: #333;">📸 Fotografías</h3>
            <div class="images-grid">
                <div class="form-group">
                    <label>Foto Clock In</label>
                    <div class="file-upload">
                        <input type="file" id="foto_clock_in" name="foto_clock_in" accept="image/*" onchange="updateFileName(this)">
                        <label for="foto_clock_in" class="file-label">
                            <span>📁 Seleccionar imagen</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Foto Clock Out</label>
                    <div class="file-upload">
                        <input type="file" id="foto_clock_out" name="foto_clock_out" accept="image/*" onchange="updateFileName(this)">
                        <label for="foto_clock_out" class="file-label">
                            <span>📁 Seleccionar imagen</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Foto Etiquetas</label>
                    <div class="file-upload">
                        <input type="file" id="foto_etiquetas" name="foto_etiquetas" accept="image/*" onchange="updateFileName(this)">
                        <label for="foto_etiquetas" class="file-label">
                            <span>📁 Seleccionar imagen</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Foto Extra</label>
                    <div class="file-upload">
                        <input type="file" id="foto_extra" name="foto_extra" accept="image/*" onchange="updateFileName(this)">
                        <label for="foto_extra" class="file-label">
                            <span>📁 Seleccionar imagen</span>
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit">💾 Guardar Bitácora</button>
        </form>
    </div>

    <script>
        function updateFileName(input) {
            const label = input.parentElement.querySelector('.file-label');
            if (input.files && input.files[0]) {
                label.querySelector('span').textContent = '✅ ' + input.files[0].name;
                label.classList.add('has-file');
            }
        }

        document.getElementById('formBitacora').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const mensajeDiv = document.getElementById('mensaje');
            
            try {
                const response = await fetch('guardar_bitacora.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    mensajeDiv.innerHTML = `<div class="alert alert-success">✅ ${result.message}<br>Gracias por registrar tu bitácora.</div>`;
                    this.reset();
                    document.querySelectorAll('.file-label').forEach(label => {
                        label.querySelector('span').textContent = '📁 Seleccionar imagen';
                        label.classList.remove('has-file');
                    });
                    
                    // No redirigir, solo limpiar el formulario
                    setTimeout(() => {
                        mensajeDiv.innerHTML = '';
                    }, 5000);
                } else {
                    mensajeDiv.innerHTML = `<div class="alert alert-error">❌ ${result.message}</div>`;
                }
            } catch (error) {
                mensajeDiv.innerHTML = `<div class="alert alert-error">❌ Error al guardar la bitácora</div>`;
            }
        });

        // Establecer fecha actual por defecto
        document.getElementById('fecha').valueAsDate = new Date();
    </script>
</body>
</html>