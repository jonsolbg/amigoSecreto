<?php
// participate.php

require_once 'include/mainApp.php';

$tenantId = $_GET['tenant'] ?? null;
requireAuth($tenantId);

$data = getTenantData($tenantId);
$config = $data['config'];
$drawStatus = $data['drawStatus'];
$assignment = null;

// Buscar asignación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_assignment'])) {
    $email = $_POST['email'];
    $assignment = getAssignmentByEmail($tenantId, $email);
    
    if ($assignment) {
        handleError("✅ ¡Hola {$assignment['giver_name']}! Tu amigo secreto es: <strong>{$assignment['receiver_name']}</strong>", 'success');
    } else {
        handleError("❌ No se encontró asignación para este email. Asegúrate de que el sorteo se haya realizado y que tu email esté registrado correctamente.", 'error');
    }
    
    $data = getTenantData($tenantId);
    $drawStatus = $data['drawStatus'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amigo Secreto - <?php echo htmlspecialchars($tenantId); ?></title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body>
    <div class="w3-container" style="max-width: 800px; margin: 0 auto; padding: 20px;">
        
        <!-- Header -->
        <div class="w3-card header-gradient w3-text-white w3-padding w3-margin-bottom">
            <div class="participate-hero">
                <span class="emoji">🎄</span>
                <h1 class="w3-xxxlarge">Amigo Secreto</h1>
                <p class="w3-large"><?php echo htmlspecialchars($config['tenant_id']); ?></p>
                <small><i class="fas fa-user"></i> Administrador: <?php echo htmlspecialchars($config['admin_name']); ?></small>
            </div>
        </div>

        <!-- Estado del sorteo -->
        <div class="w3-panel w3-round w3-padding <?php echo isDrawCompleted($tenantId) ? 'w3-pale-green' : 'w3-pale-yellow'; ?>">
            <div class="w3-center">
                <?php if (isDrawCompleted($tenantId)): ?>
                    <span class="status-badge status-completed">
                        <i class="fas fa-check-circle"></i> Sorteo completado
                    </span>
                    <br>
                    <small><i class="fas fa-calendar"></i> Realizado: <?php echo formatDate($drawStatus['drawn_at']); ?></small>
                <?php else: ?>
                    <span class="status-badge status-pending">
                        <i class="fas fa-clock"></i> Sorteo pendiente
                    </span>
                    <br>
                    <small>Espera a que el administrador realice el sorteo</small>
                <?php endif; ?>
            </div>
        </div>

        <!-- Consulta -->
        <div class="w3-card-4 w3-padding fade-in">
            <h2 class="w3-text-purple w3-center"><i class="fas fa-search"></i> Consulta tu amigo secreto</h2>
            <hr>
            
            <?php displayFlashMessage(); ?>

            <?php if (isDrawCompleted($tenantId)): ?>
                <form method="POST" class="w3-container">
                    <div class="w3-margin-bottom">
                        <label class="w3-text-grey"><i class="fas fa-envelope"></i> Ingresa tu email registrado</label>
                        <input class="w3-input w3-border w3-round w3-large" type="email" name="email" 
                               placeholder="ejemplo@email.com" required>
                    </div>
                    
                    <button type="submit" name="check_assignment" 
                            class="w3-btn w3-purple w3-round-large w3-block w3-hover-purple w3-large">
                        <i class="fas fa-gift"></i> Ver mi amigo secreto
                    </button>
                </form>
            <?php else: ?>
                <div class="w3-center w3-padding-64">
                    <i class="fas fa-clock" style="font-size: 64px; color: #ccc;"></i>
                    <p class="w3-large w3-text-grey">El sorteo aún no se ha realizado</p>
                    <p class="w3-text-grey">Por favor, espera a que el administrador lo complete</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Botón volver -->
        <div class="w3-center w3-margin-top">
            <a href="index.php" class="w3-btn w3-grey w3-round-large w3-hover-grey">
                <i class="fas fa-arrow-left"></i> Volver al inicio
            </a>
        </div>

        <!-- Footer -->
        <div class="w3-center w3-padding-24 w3-text-grey">
            <small>
                <i class="fas fa-heart w3-text-red"></i> 
                <?php echo APP_NAME; ?> - v<?php echo APP_VERSION; ?>
                <i class="fas fa-heart w3-text-red"></i>
            </small>
        </div>
        
    </div>
</body>
</html>