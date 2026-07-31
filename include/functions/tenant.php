<?php
// include/functions/tenant.php

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/participants.php';
require_once __DIR__ . '/draw.php';
require_once __DIR__ . '/assignments.php';

/**
 * Crea un nuevo tenant
 */
function createTenant($tenantId, $adminName, $adminEmail) {
    $tenantId = sanitizeTenantId($tenantId);
    
    if (empty($tenantId) || empty($adminName) || !validateEmail($adminEmail)) {
        return false;
    }
    
    $tenantPath = getTenantPath($tenantId);
    
    if (tenantExists($tenantId)) {
        return false;
    }
    
    // Crear directorio
    if (!mkdir($tenantPath, 0755, true)) {
        return false;
    }
    
    // Configuración del tenant
    $config = [
        'tenant_id' => $tenantId,
        'admin_name' => $adminName,
        'admin_email' => $adminEmail,
        'created_at' => date('Y-m-d H:i:s'),
        'status' => 'active'
    ];
    
    if (!writeJsonFile("$tenantPath/config.json", $config)) {
        rmdir($tenantPath);
        return false;
    }
    
    // Inicializar archivos
    writeJsonFile("$tenantPath/participants.json", []);
    writeJsonFile("$tenantPath/assignments.json", []);
    writeJsonFile("$tenantPath/draw_status.json", [
        'status' => 'pending',
        'drawn_at' => null
    ]);
    
    return true;
}

/**
 * Obtiene todos los datos de un tenant
 */
function getTenantData($tenantId) {
    $tenantId = sanitizeTenantId($tenantId);
    
    if (!tenantExists($tenantId)) {
        return null;
    }
    
    $tenantPath = getTenantPath($tenantId);
    
    return [
        'config' => readJsonFile("$tenantPath/config.json"),
        'participants' => readJsonFile("$tenantPath/participants.json") ?? [],
        'assignments' => readJsonFile("$tenantPath/assignments.json") ?? [],
        'drawStatus' => readJsonFile("$tenantPath/draw_status.json") ?? ['status' => 'pending', 'drawn_at' => null]
    ];
}

/**
 * Obtiene la configuración de un tenant
 */
function getTenantConfig($tenantId) {
    $data = getTenantData($tenantId);
    return $data ? $data['config'] : null;
}

/**
 * Actualiza la configuración de un tenant
 */
function updateTenantConfig($tenantId, $data) {
    $tenantId = sanitizeTenantId($tenantId);
    
    if (!tenantExists($tenantId)) {
        return false;
    }
    
    $config = getTenantConfig($tenantId);
    if (!$config) {
        return false;
    }
    
    $config = array_merge($config, $data);
    return writeJsonFile(getTenantPath($tenantId) . '/config.json', $config);
}

/**
 * Elimina un tenant completo
 */
function deleteTenant($tenantId) {
    $tenantId = sanitizeTenantId($tenantId);
    $tenantPath = getTenantPath($tenantId);
    
    if (!tenantExists($tenantId)) {
        return false;
    }
    
    // Eliminar todos los archivos JSON
    $files = glob("$tenantPath/*.json");
    foreach ($files as $file) {
        unlink($file);
    }
    
    // Eliminar directorio
    return rmdir($tenantPath);
}

/**
 * Obtiene estadísticas de un tenant
 */
function getTenantStats($tenantId) {
    $data = getTenantData($tenantId);
    if (!$data) {
        return null;
    }
    
    return [
        'total_participants' => count($data['participants']),
        'draw_status' => $data['drawStatus']['status'],
        'drawn_at' => $data['drawStatus']['drawn_at'],
        'total_assignments' => count($data['assignments']),
        'created_at' => $data['config']['created_at']
    ];
}
?>