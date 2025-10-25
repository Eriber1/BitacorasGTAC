<?php
session_start();

// Verificar si el usuario está autenticado
function estaAutenticado() {
    return isset($_SESSION['usuario_id']) && isset($_SESSION['usuario']);
}

// Requerir autenticación (redirige al login si no está autenticado)
function requerirAutenticacion() {
    if (!estaAutenticado()) {
        header('Location: ../login.php');
        exit;
    }
}

// Obtener datos del usuario actual
function obtenerUsuarioActual() {
    if (!estaAutenticado()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['usuario_id'],
        'usuario' => $_SESSION['usuario'],
        'nombre' => $_SESSION['nombre']
    ];
}

// Cerrar sesión
function cerrarSesion() {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// Iniciar sesión
function iniciarSesion($usuario_id, $usuario, $nombre) {
    $_SESSION['usuario_id'] = $usuario_id;
    $_SESSION['usuario'] = $usuario;
    $_SESSION['nombre'] = $nombre;
}
?>