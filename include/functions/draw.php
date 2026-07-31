<?php
// include/functions/draw.php

require_once dirname(dirname(__FILE__)) . '/functions/helpers.php';
require_once dirname(dirname(__FILE__)) . '/functions/participants.php';
require_once dirname(dirname(__FILE__)) . '/functions/assignments.php';

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
        return false;
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
            writeJsonFile($drawStatusPath, array(
                'status' => 'completed',
                'drawn_at' => date('Y-m-d H:i:s')
            ));
            
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
    $assignments = array();
    
    foreach ($participants as $giver) {
        // Excluir al mismo participante
        $possible = array();
        foreach ($available as $p) {
            if ($p['id'] !== $giver['id']) {
                $possible[] = $p;
            }
        }
        
        if (empty($possible)) {
            return false;
        }
        
        $index = array_rand($possible);
        $receiver = $possible[$index];
        
        $assignments[] = array(
            'giver_id' => $giver['id'],
            'giver_name' => $giver['name'],
            'giver_email' => $giver['email'],
            'receiver_id' => $receiver['id'],
            'receiver_name' => $receiver['name']
        );
        
        // Remover al receiver de disponibles
        $newAvailable = array();
        foreach ($available as $p) {
            if ($p['id'] !== $receiver['id']) {
                $newAvailable[] = $p;
            }
        }
        $available = $newAvailable;
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
    writeJsonFile($tenantPath . '/assignments.json', array());
    
    // Actualizar estado
    writeJsonFile($tenantPath . '/draw_status.json', array(
        'status' => 'pending',
        'drawn_at' => null
    ));
    
    return true;
}

/**
 * Obtiene el estado del sorteo
 */
function getDrawStatus($tenantId) {
    $data = getTenantData($tenantId);
    if ($data) {
        return $data['drawStatus'];
    }
    return null;
}

/**
 * Verifica si el sorteo ya fue realizado
 */
function isDrawCompleted($tenantId) {
    $status = getDrawStatus($tenantId);
    if ($status && $status['status'] === 'completed') {
        return true;
    }
    return false;
}

/**
 * Obtiene el número de asignaciones realizadas
 */
function countAssignments($tenantId) {
    $data = getTenantData($tenantId);
    if ($data) {
        return count($data['assignments']);
    }
    return 0;
}
?>