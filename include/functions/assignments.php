<?php
// include/functions/assignments.php

require_once dirname(dirname(__FILE__)) . '/functions/helpers.php';
require_once dirname(dirname(__FILE__)) . '/functions/participants.php';

/**
 * Obtiene la asignación de un participante por email
 */
function getAssignmentByEmail($tenantId, $email) {
    $data = getTenantData($tenantId);
    if (!$data || $data['drawStatus']['status'] !== 'completed') {
        return null;
    }
    
    $email = strtolower(trim($email));
    $assignments = $data['assignments'];
    
    foreach ($assignments as $assignment) {
        if (strtolower($assignment['giver_email']) === $email) {
            return $assignment;
        }
    }
    
    return null;
}

/**
 * Obtiene la asignación de un participante por ID
 */
function getAssignmentByGiverId($tenantId, $giverId) {
    $data = getTenantData($tenantId);
    if (!$data || $data['drawStatus']['status'] !== 'completed') {
        return null;
    }
    
    $assignments = $data['assignments'];
    
    foreach ($assignments as $assignment) {
        if ($assignment['giver_id'] === $giverId) {
            return $assignment;
        }
    }
    
    return null;
}

/**
 * Obtiene todas las asignaciones de un tenant
 */
function getAssignments($tenantId) {
    $data = getTenantData($tenantId);
    if ($data) {
        return $data['assignments'];
    }
    return array();
}

/**
 * Verifica si un participante ya tiene asignación
 */
function hasAssignment($tenantId, $participantId) {
    $assignments = getAssignments($tenantId);
    
    foreach ($assignments as $assignment) {
        if ($assignment['giver_id'] === $participantId || 
            $assignment['receiver_id'] === $participantId) {
            return true;
        }
    }
    
    return false;
}

/**
 * Obtiene el amigo secreto de un participante (quién le toca regalar)
 */
function getSecretFriend($tenantId, $participantId) {
    $assignment = getAssignmentByGiverId($tenantId, $participantId);
    if ($assignment) {
        return $assignment['receiver_name'];
    }
    return null;
}

/**
 * Obtiene quién es el amigo secreto de un participante (quién le regala a él)
 */
function getSecretSanta($tenantId, $participantId) {
    $assignments = getAssignments($tenantId);
    
    foreach ($assignments as $assignment) {
        if ($assignment['receiver_id'] === $participantId) {
            return $assignment['giver_name'];
        }
    }
    
    return null;
}

/**
 * Exporta las asignaciones en formato texto
 */
function exportAssignmentsText($tenantId) {
    $assignments = getAssignments($tenantId);
    if (empty($assignments)) {
        return "No hay asignaciones disponibles\n";
    }
    
    $output = "=== ASIGNACIONES DE AMIGO SECRETO ===\n\n";
    $output .= "Tenant: " . $tenantId . "\n";
    $output .= "Fecha: " . date('d/m/Y H:i:s') . "\n\n";
    
    foreach ($assignments as $assignment) {
        $output .= "📝 " . $assignment['giver_name'];
        $output .= " → 🎁 " . $assignment['receiver_name'] . "\n";
    }
    
    return $output;
}
?>