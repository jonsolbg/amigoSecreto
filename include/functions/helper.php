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
?>