<?php
// include/functions/helpers.php

/**
 * Obtiene la ruta base del tenant
 */
function getTenantPath($tenantId) {
    return "tenants/$tenantId";
}

/**
 * Verifica si un tenant existe
 */
function tenantExists($tenantId) {
    return file_exists(getTenantPath($tenantId));
}

/**
 * Lee un archivo JSON de manera segura
 */
function readJsonFile($filePath) {
    if (!file_exists($filePath)) {
        return null;
    }
    
    $content = file_get_contents($filePath);
    return json_decode($content, true);
}

/**
 * Escribe un archivo JSON de manera segura
 */
function writeJsonFile($filePath, $data) {
    $json = json_encode($data, JSON_PRETTY_PRINT);
    return file_put_contents($filePath, $json) !== false;
}

/**
 * Sanitiza un ID de tenant (solo caracteres alfanuméricos y guiones)
 */
function sanitizeTenantId($tenantId) {
    return preg_replace('/[^a-zA-Z0-9_-]/', '', $tenantId);
}

/**
 * Valida un email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Genera un ID único para participantes
 */
function generateParticipantId() {
    return uniqid('p_', true);
}

/**
 * Obtiene todos los tenants
 */
function getAllTenants() {
    $tenants = [];
    $dirs = glob('tenants/*', GLOB_ONLYDIR);
    
    foreach ($dirs as $dir) {
        $tenantId = basename($dir);
        $configPath = "$dir/config.json";
        if (file_exists($configPath)) {
            $config = readJsonFile($configPath);
            if ($config) {
                $tenants[] = $config;
            }
        }
    }
    
    return $tenants;
}

/**
 * Formatea una fecha
 */
function formatDate($date) {
    if (!$date) return 'N/A';
    return date('d/m/Y H:i', strtotime($date));
}

/**
 * Obtiene el estado del sorteo en texto
 */
function getDrawStatusText($status) {
    $statuses = [
        'pending' => '⏳ Pendiente',
        'completed' => '✅ Completado'
    ];
    return $statuses[$status] ?? $status;
}

/**
 * Verifica si se puede realizar el sorteo (mínimo 2 participantes)
 */
function canPerformDraw($participants) {
    return count($participants) >= 2;
}

/**
 * Función para debuggear (solo en desarrollo)
 */
function debug($data) {
    if (getenv('APP_ENV') === 'development') {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
}

/**
 * Obtiene la URL base del sistema
 */
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script = dirname($_SERVER['SCRIPT_NAME']);
    return $protocol . '://' . $host . $script;
}

/**
 * Redirecciona a una URL
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Obtiene el IP del usuario
 */
function getUserIP() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

/**
 * Limpia texto para evitar XSS
 */
function sanitizeText($text) {
    return htmlspecialchars(trim($text), ENT_QUOTES, 'UTF-8');
}

/**
 * Genera un slug a partir de un texto
 */
function generateSlug($text) {
    $text = preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
    $text = strtolower(trim($text));
    $text = preg_replace('/\s+/', '-', $text);
    return $text;
}
?>