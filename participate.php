<?php
// participate.php

require_once 'include/mainApp.php';

$tenantId = $_GET['tenant'] ?? null;

// Verificar que el tenant existe
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
    
    // Recargar datos
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
    <style>
        /* ... estilos ... */
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="emoji">🎄</div>
            <h1>Amigo Secreto</h1>
            <p><?php echo htmlspecialchars($config['tenant_id']); ?></p>
            <small>Administrador: <?php echo htmlspecialchars($config['admin_name']); ?></small>
        </div>

        <div class="status-info">
            <?php if (isDrawCompleted($tenantId)): ?>
                <span style="color: #48bb78;">✅ Sorteo completado</span>
                <br>
                <small>Realizado: <?php echo formatDate($drawStatus['drawn_at']); ?></small>
            <?php else: ?>
                <span style="color: #ed8936;">⏳ Sorteo pendiente</span>
                <br>
                <small>Espera a que el administrador realice el sorteo</small>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>🔍 Consulta tu amigo secreto</h2>
            
            <?php displayFlashMessage(); ?>

            <?php if (isDrawCompleted($tenantId)): ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Ingresa tu email (registrado en el sistema)</label>
                        <input type="email" name="email" placeholder="ejemplo@email.com" required>
                    </div>
                    <button type="submit" name="check_assignment">🔎 Ver mi amigo secreto</button>
                </form>
            <?php else: ?>
                <p style="text-align: center; color: #888; padding: 20px 0;">
                    ⏳ El sorteo aún no se ha realizado. 
                    <br>Por favor, espera a que el administrador lo complete.
                </p>
            <?php endif; ?>
        </div>

        <a href="index.php" class="back-link">← Volver al inicio</a>
    </div>
</body>
</html>