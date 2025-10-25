<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Validar campos requeridos
    $required = ['os', 'cliente', 'sitio', 'fecha', 'brigada', 'bitacora'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("El campo $field es requerido");
        }
    }

    // Procesar subida de imágenes
    $fotos = [];
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
    $foto_fields = ['foto_clock_in', 'foto_clock_out', 'foto_etiquetas', 'foto_extra'];

    foreach ($foto_fields as $field) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES[$field];
            
            // Validar tipo de archivo
            if (!in_array($file['type'], $allowed_types)) {
                throw new Exception("El archivo {$field} debe ser una imagen JPG o PNG");
            }
            
            // Validar tamaño
            if ($file['size'] > MAX_FILE_SIZE) {
                throw new Exception("El archivo {$field} es demasiado grande (máximo 5MB)");
            }
            
            // Generar nombre único
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $extension;
            $filepath = UPLOAD_DIR . $filename;
            
            // Mover archivo
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $fotos[$field] = $filename;
            } else {
                throw new Exception("Error al subir el archivo {$field}");
            }
        } else {
            $fotos[$field] = null;
        }
    }

    // Insertar en base de datos
    $conn = getConnection();
    
    $stmt = $conn->prepare("INSERT INTO bitacoras 
        (os, cliente, sitio, fecha, brigada, bitacora, foto_clock_in, foto_clock_out, foto_etiquetas, foto_extra, fm_acceso, noc_acceso) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param(
        "ssssssssssss",
        $_POST['os'],
        $_POST['cliente'],
        $_POST['sitio'],
        $_POST['fecha'],
        $_POST['brigada'],
        $_POST['bitacora'],
        $fotos['foto_clock_in'],
        $fotos['foto_clock_out'],
        $fotos['foto_etiquetas'],
        $fotos['foto_extra'],
        $_POST['fm_acceso'] ?? null,
        $_POST['noc_acceso'] ?? null
    );
    
    if ($stmt->execute()) {
        $id = $conn->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'Bitácora guardada exitosamente',
            'id' => $id
        ]);
    } else {
        throw new Exception('Error al guardar en la base de datos');
    }
    
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>