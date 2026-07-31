<?php
// admin.php

require_once 'include/mainApp.php';

$tenantId = $_GET['tenant'] ?? null;

// Verificar que el tenant existe
requireAuth($tenantId);

$data = getTenantData($tenantId);
$config = $data['config'];
$participants = $data['participants'];
$assignments = $data['assignments'];
$drawStatus = $data['drawStatus'];

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar CSRF (opcional)
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        handleError('Token de seguridad inválido', 'error');
    } else {
        if (isset($_POST['add_participant'])) {
            $name = $_POST['name'];
            $email = $_POST['email'];
            if (addParticipant($tenantId, $name, $email)) {
                handleError('Participante agregado exitosamente', 'success');
            } else {
                handleError('Error: El email ya está registrado o los datos son inválidos', 'error');
            }
        } elseif (isset($_POST['remove_participant'])) {
            $participantId = $_POST['participant_id'];
            if (removeParticipant($tenantId, $participantId)) {
                handleError('Participante eliminado', 'success');
            } else {
                handleError('Error al eliminar el participante', 'error');
            }
        } elseif (isset($_POST['perform_draw'])) {
            if (performDraw($tenantId)) {
                handleError('¡Sorteo realizado exitosamente!', 'success');
            } else {
                handleError('Error: Se necesitan al menos ' . MIN_PARTICIPANTS . ' participantes', 'error');
            }
        } elseif (isset($_POST['reset_draw'])) {
            if (resetDraw($tenantId)) {
                handleError('Sorteo reiniciado', 'success');
            } else {
                handleError('Error al reiniciar el sorteo', 'error');
            }
        }
    }
    
    // Recargar datos y redirigir
    header('Location: admin.php?tenant=' . urlencode($tenantId));
    exit;
}

// Recargar datos después de posible redirección
$data = getTenantData($tenantId);
$participants = $data['participants'];
$assignments = $data['assignments'];
$drawStatus = $data['drawStatus'];
$canDraw = canPerformDraw($participants);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?php echo htmlspecialchars($tenantId); ?></title>
    <style>
        /* ... estilos ... */
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏢 <?php echo htmlspecialchars($tenantId); ?></h1>
            <p>Admin: <?php echo htmlspecialchars($config['admin_name']); ?> (<?php echo htmlspecialchars($config['admin_email']); ?>)</p>
            <p>Estado: <?php echo getDrawStatusText($drawStatus['status']); ?></p>
            <?php if ($drawStatus['drawn_at']): ?>
                <p>Realizado: <?php echo formatDate($drawStatus['drawn_at']); ?></p>
            <?php endif; ?>
        </div>

        <?php displayFlashMessage(); ?>

        <div class="grid-2">
            <div class="card">
                <h2>📝 Agregar Participante</h2>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <div class="form-group">
                        <label>Nombre completo</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>
                    <button type="submit" name="add_participant">Agregar</button>
                </form>
            </div>

            <div class="card">
                <h2>👥 Participantes (<?php echo count($participants); ?>)</h2>
                <?php if (count($participants) > 0): ?>
                    <ul class="participant-list">
                        <?php foreach ($participants as $p): ?>
                            <li class="participant-item">
                                <span>
                                    <strong><?php echo htmlspecialchars($p['name']); ?></strong>
                                    <span style="color: #888; font-size: 14px;">&lt;<?php echo htmlspecialchars($p['email']); ?>&gt;</span>
                                    <br>
                                    <small style="color: #999;">Registrado: <?php echo formatDate($p['registered_at']); ?></small>
                                </span>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                    <input type="hidden" name="participant_id" value="<?php echo $p['id']; ?>">
                                    <button type="submit" name="remove_participant" class="btn-danger btn-sm" onclick="return confirm('¿Eliminar este participante?')">Eliminar</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="color: #888; text-align: center; padding: 20px;">No hay participantes registrados</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <h2>🎯 Acciones del Sorteo</h2>
            <div class="actions">
                <?php if ($drawStatus['status'] === 'pending'): ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <button type="submit" name="perform_draw" class="btn-success" 
                                <?php echo !$canDraw ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>
                                onclick="return confirm('¿Realizar sorteo? Esto asignará los amigos secretos.')">
                            🎲 Realizar Sorteo <?php echo !$canDraw ? '(necesitas ' . MIN_PARTICIPANTS . '+ participantes)' : ''; ?>
                        </button>
                    </form>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <button type="submit" name="reset_draw" class="btn-warning" onclick="return confirm('¿Reiniciar sorteo? Se perderán las asignaciones actuales.')">
                            🔄 Reiniciar Sorteo
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            
            <?php if ($drawStatus['status'] === 'completed'): ?>
                <div style="margin-top: 15px; padding: 10px; background: #f0f9ff; border-radius: 5px;">
                    <small>📊 <?php echo count($assignments); ?> asignaciones realizadas</small>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($drawStatus['status'] === 'completed' && count($assignments) > 0): ?>
            <div class="card">
                <h2>📋 Asignaciones</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 10px;">
                    <?php foreach ($assignments as $assignment): ?>
                        <div class="assignment-item">
                            <strong><?php echo htmlspecialchars($assignment['giver_name']); ?></strong>
                            <span style="color: #888;">→</span>
                            <strong style="color: #48bb78;"><?php echo htmlspecialchars($assignment['receiver_name']); ?></strong>
                            <br>
                            <small style="color: #888;"><?php echo htmlspecialchars($assignment['giver_email']); ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <a href="index.php" class="back-link">← Volver al inicio</a>
    </div>
</body>
</html>