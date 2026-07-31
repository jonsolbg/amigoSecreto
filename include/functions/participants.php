<?php
// include/functions/participants.php

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/draw.php';

/**
 * Agrega un nuevo participante
 */
function addParticipant($tenantId, $name, $email) {
    $tenantId = sanitizeTenantId($tenantId);
    
    if (!tenantExists($tenantId) || empty($name) || !validateEmail($email)) {
        return false;
    }
    
    $participantsPath = getTenantPath($tenantId) . '/participants.json';
    $participants = readJsonFile($participantsPath) ?? [];
    
    // Verificar email duplicado
    foreach ($participants as $p) {
        if (strtolower($p['email']) === strtolower($email)) {
            return false;
        }
    }
    
    // Agregar participante
    $participants[] = [
        'id' => generateParticipantId(),
        'name' => trim($name),
        'email' => strtolower(trim($email)),
        'registered_at' => date('Y-m-d H:i:s')
    ];
    
    if (!writeJsonFile($participantsPath, $participants)) {
        return false;
    }
    
    // Resetear sorteo si ya se había realizado
    resetDraw($tenantId);
    
    return true;
}

/**
 * Elimina un participante
 */
function removeParticipant($tenantId, $participantId) {
    $tenantId = sanitizeTenantId($tenantId);
    
    if (!tenantExists($tenantId) || empty($participantId)) {
        return false;
    }
    
    $participantsPath = getTenantPath($tenantId) . '/participants.json';
    $participants = readJsonFile($participantsPath) ?? [];
    
    $found = false;
    $participants = array_filter($participants, function($p) use ($participantId, &$found) {
        if ($p['id'] === $participantId) {
            $found = true;
            return false;
        }
        return true;
    });
    
    if (!$found) {
        return false;
    }
    
    if (!writeJsonFile($participantsPath, array_values($participants))) {
        return false;
    }
    
    // Resetear sorteo
    resetDraw($tenantId);
    
    return true;
}

/**
 * Obtiene todos los participantes de un tenant
 */
function getParticipants($tenantId) {
    $data = getTenantData($tenantId);
    return $data ? $data['participants'] : [];
}

/**
 * Busca un participante por email
 */
function findParticipantByEmail($tenantId, $email) {
    $participants = getParticipants($tenantId);
    $email = strtolower(trim($email));
    
    foreach ($participants as $p) {
        if (strtolower($p['email']) === $email) {
            return $p;
        }
    }
    
    return null;
}

/**
 * Busca un participante por ID
 */
function findParticipantById($tenantId, $participantId) {
    $participants = getParticipants($tenantId);
    
    foreach ($participants as $p) {
        if ($p['id'] === $participantId) {
            return $p;
        }
    }
    
    return null;
}

/**
 * Obtiene el número de participantes
 */
function countParticipants($tenantId) {
    return count(getParticipants($tenantId));
}

/**
 * Verifica si un email ya está registrado
 */
function isEmailRegistered($tenantId, $email) {
    return findParticipantByEmail($tenantId, $email) !== null;
}
?>