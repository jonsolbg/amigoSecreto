<?php
// include/functions/draw.php

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/participants.php';
require_once __DIR__ . '/assignments.php';

/**
 * Realiza el sorteo de amigo secreto
 */
function performDraw($tenantId) {
    $tenantId = sanitizeTenantId($tenantId);
    
    if (!tenantExists($tenantId)) {
        return false;
    }
    
    $participants = getParticipants($tenantId);
    $count = count($participants);
    
    if ($count < 2) {
        return false; // Necesita al menos 2 participantes
    }
    
    // Intentar asignar hasta 100 veces
    $attempts = 0;
    while ($attempts < 100) {
        $result = tryAssignment($participants);
        if ($result) {
            // Guardar asignaciones
            $assignmentsPath = getTenantPath($tenantId) . '/assignments.json';
            writeJsonFile($assignmentsPath, $result);
            
            // Actualizar estado del sorteo
            $drawStatusPath = getTenantPath($tenantId) . '/draw_status.json';
            writeJsonFile($drawStatusPath, [
                'status' => 'completed',
                'drawn_at' => date('Y-m-d H:i:s')
            ]);
            
            return true;
        }
        $attempts++;
    }
    
    return false;
}

/**
 * Intenta realizar una asignación válida
 */
function tryAssignment($participants) {
    // Mezclar participantes aleatoriamente
    shuffle($participants);
    $available = $participants;
    $assignments = [];
    
    foreach ($participants as $giver) {
        // Excluir al mismo participante
        $possible = array_filter($available, function($p) use ($giver) {
            return $p['id'] !== $giver['id'];
        });
        
        if (empty($possible)) {
            return false; // No hay asignación posible
        }
        
        $possible = array_values($possible);
        $index = array_rand($possible);
        $receiver = $possible[$index];
        
        $assignments[] = [
            'giver_id' => $giver['id'],
            'giver_name' => $giver['name'],
            'giver_email' => $giver['email'],
            'receiver_id' => $receiver['id'],
            'receiver_name' => $receiver['name']
        ];
        
        // Remover al receiver de disponibles
        $available = array_filter($available, function($p) use ($receiver) {
            return $p['id'] !== $receiver['id'];
        });
    }
    
    return $assignments;
}

/**
 * Reinicia el sorteo
 */
function resetDraw($tenantId) {
    $tenantId = sanitizeTenantId($tenantId);
    
    if (!tenantExists($tenantId)) {
        return false;
    }
    
    $tenantPath = getTenantPath($tenantId);
    
    // Limpiar asignaciones
    writeJsonFile("$tenantPath/assignments.json", []);
    
    // Actualizar estado
    writeJsonFile("$tenantPath/draw_status.json", [
        'status' => 'pending',
        'drawn_at' => null
    ]);
    
    return true;
}

/**
 * Obtiene el estado del sorteo
 */
function getDrawStatus($tenantId) {
    $data = getTenantData($tenantId);
    return $data ? $data['drawStatus'] : null;
}

/**
 * Verifica si el sorteo ya fue realizado
 */
function isDrawCompleted($tenantId) {
    $status = getDrawStatus($tenantId);
    return $status && $status['status'] === 'completed';
}

/**
 * Obtiene el número de asignaciones realizadas
 */
function countAssignments($tenantId) {
    $data = getTenantData($tenantId);
    return $data ? count($data['assignments']) : 0;
}
?>