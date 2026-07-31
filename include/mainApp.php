<?php
// include/mainApp.php
// Archivo de inicialización principal - Carga todas las dependencias y configuración

// Prevenir ejecución directa
if (!defined('MAIN_APP_LOADED')) {
    define('MAIN_APP_LOADED', true);
}

// Configuración de errores (solo en desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Cambiar a 1 en desarrollo

// Configuración de zona horaria
date_default_timezone_set('America/Costa_Rica');

// Definir constantes de rutas
define('ROOT_PATH', dirname(__DIR__));
define('INCLUDE_PATH', ROOT_PATH . '/include');
define('FUNCTIONS_PATH', INCLUDE_PATH . '/functions');
define('TENANTS_PATH', ROOT_PATH . '/tenants');

// Cargar todas las funciones
require_once FUNCTIONS_PATH . '/helpers.php';
require_once FUNCTIONS_PATH . '/tenant.php';
require_once FUNCTIONS_PATH . '/participants.php';
require_once FUNCTIONS_PATH . '/draw.php';
require_once FUNCTIONS_PATH . '/assignments.php';

// Inicializar sesión si es necesario
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Función para manejar errores de manera amigable
function handleError($message, $type = 'error') {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Función para obtener mensajes flash
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

// Función para mostrar mensajes flash en HTML
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $class = $flash['type'] === 'error' ? 'error' : 'message';
        echo "<div class='$class'>{$flash['message']}</div>";
    }
}

// Función de autenticación básica (opcional)
function requireAuth($tenantId = null) {
    // Aquí puedes implementar autenticación si lo deseas
    // Por ahora, solo verificamos que el tenant exista
    if ($tenantId && !tenantExists($tenantId)) {
        handleError('Tenant no encontrado', 'error');
        header('Location: index.php');
        exit;
    }
    return true;
}

// Función para generar token CSRF (opcional)
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Función para verificar token CSRF (opcional)
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Configuración adicional del sistema
define('APP_NAME', 'Sistema de Amigo Secreto');
define('APP_VERSION', '1.0.0');
define('MIN_PARTICIPANTS', 2);

// Cargar variables de entorno (opcional)
if (file_exists(ROOT_PATH . '/.env')) {
    $env = parse_ini_file(ROOT_PATH . '/.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

// Inicialización completada
define('APP_INITIALIZED', true);
?>