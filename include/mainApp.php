<?php
// include/mainApp.php
// Archivo de inicialización principal - Carga todas las dependencias y configuración

// Prevenir ejecución directa
if (!defined('MAIN_APP_LOADED')) {
    define('MAIN_APP_LOADED', true);
}

// Verificar versión de PHP
if (version_compare(PHP_VERSION, '5.6.0', '<')) {
    die('Se requiere PHP 5.6 o superior para ejecutar esta aplicación.');
}

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 0); // Cambiar a 1 en desarrollo

// Configuración de zona horaria
date_default_timezone_set('America/Costa_Rica');

// Definir constantes de rutas
define('ROOT_PATH', dirname(dirname(__FILE__)));
define('INCLUDE_PATH', ROOT_PATH . '/include');
define('FUNCTIONS_PATH', INCLUDE_PATH . '/functions');
define('TENANTS_PATH', ROOT_PATH . '/tenants');

// Verificar que los directorios existan
if (!file_exists(TENANTS_PATH)) {
    mkdir(TENANTS_PATH, 0755, true);
}

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
    $_SESSION['flash_message'] = array(
        'type' => $type,
        'message' => $message
    );
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
        $class = ($flash['type'] === 'error') ? 'error' : 'message';
        echo "<div class='$class'>" . $flash['message'] . "</div>";
    }
}

// Función de autenticación básica (opcional)
function requireAuth($tenantId = null) {
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
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Función para verificar token CSRF (opcional)
function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return $_SESSION['csrf_token'] === $token;
}

// Configuración adicional del sistema
define('APP_NAME', 'Sistema de Amigo Secreto');
define('APP_VERSION', '1.0.0');
define('MIN_PARTICIPANTS', 2);

// Cargar variables de entorno (opcional)
if (file_exists(ROOT_PATH . '/.env')) {
    $lines = file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

// Inicialización completada
define('APP_INITIALIZED', true);
?>