<?php
// admin.php

require_once 'include/mainApp.php';

$tenantId = $_GET['tenant'] ?? null;
requireAuth($tenantId);

$data = getTenantData($tenantId);
$config = $data['config'];
$participants = $data['participants'];
$assignments = $data['assignments'];
$drawStatus = $data['drawStatus'];

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    
    header('Location: admin.php?tenant=' . urlencode($tenantId));
    exit;
}

// Recargar datos
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
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body>
    <div class="w3-container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
        
        <!-- Header -->
        <div class="w3-card header-gradient w3-text-white w3-padding w3-margin-bottom">
            <div class="w3-row">
                <div class="w3-col s12 m8">
                    <h1><i class="fas fa-building"></i> <?php echo htmlspecialchars($tenantId); ?></h1>
                    <p><i class="fas fa-user"></i> Admin: <?php echo htmlspecialchars($config['admin_name']); ?></p>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($config['admin_email']); ?></p>
                </div>
                <div class="w3-col s12 m4 w3-center">
                    <div class="w3-margin-top">
                        <span class="w3-tag w3-round-large <?php echo $drawStatus['status'] === 'completed' ? 'w3-green' : 'w3-yellow'; ?>" style="font-size: 16px;">
                            <i class="fas fa-<?php echo $drawStatus['status'] === 'completed' ? 'check' : 'clock'; ?>"></i>
                            <?php echo getDrawStatusText($drawStatus['status']); ?>
                        </span>
                        <?php if ($drawStatus['drawn_at']): ?>
                            <br>
                            <small><i class="fas fa-calendar"></i> <?php echo formatDate($drawStatus['drawn_at']); ?></small>
                        <?php endif; ?>
                    </div>
                    <a href="index.php" class="w3-btn w3-white w3-text-purple w3-round-large w3-margin-top">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Mensajes Flash -->
        <?php displayFlashMessage(); ?>

        <!-- Grid de dos columnas -->
        <div class="w3-row-padding">
            <!-- Agregar Participante -->
            <div class="w3-col l6 m12 s12" style="padding: 4px;">
                <div class="w3-card-4 w3-padding fade-in">
                    <h2 class="w3-text-purple"><i class="fas fa-user-plus"></i> Agregar Participante</h2>
                    <hr>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        
                        <div class="w3-margin-bottom">
                            <label class="w3-text-grey"><i class="fas fa-user"></i> Nombre completo</label>
                            <input class="w3-input w3-border w3-round" type="text" name="name" required>
                        </div>
                        
                        <div class="w3-margin-bottom">
                            <label class="w3-text-grey"><i class="fas fa-envelope"></i> Email</label>
                            <input class="w3-input w3-border w3-round" type="email" name="email" required>
                        </div>
                        
                        <button type="submit" name="add_participant" class="w3-btn w3-purple w3-round-large w3-block w3-hover-purple">
                            <i class="fas fa-plus"></i> Agregar Participante
                        </button>
                    </form>
                </div>
            </div>

            <!-- Lista de Participantes -->
            <div class="w3-col l6 m12 s12" style="padding: 4px;">
                <div class="w3-card-4 w3-padding fade-in">
                    <h2 class="w3-text-purple">
                        <i class="fas fa-users"></i> Participantes 
                        <span class="w3-tag w3-blue w3-round-large"><?php echo count($participants); ?></span>
                    </h2>
                    <hr>
                    
                    <?php if (count($participants) > 0): ?>
                        <div style="max-height: 400px; overflow-y: auto;">
                            <?php foreach ($participants as $p): ?>
                                <div class="participant-item">
                                    <div>
                                        <strong><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($p['name']); ?></strong>
                                        <br>
                                        <small class="w3-text-grey"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($p['email']); ?></small>
                                    </div>
                                    <form method="POST">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                        <input type="hidden" name="participant_id" value="<?php echo $p['id']; ?>">
                                        <button type="submit" name="remove_participant" 
                                                class="w3-btn w3-red w3-round-large w3-hover-red"
                                                onclick="return confirm('¿Eliminar a <?php echo htmlspecialchars($p['name']); ?>?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="w3-center w3-padding-32">
                            <i class="fas fa-users" style="font-size: 48px; color: #ccc;"></i>
                            <p class="w3-text-grey">No hay participantes registrados</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Acciones del Sorteo -->
        <div class="w3-card-4 w3-padding w3-margin-top fade-in">
            <h2 class="w3-text-purple"><i class="fas fa-dice"></i> Acciones del Sorteo</h2>
            <hr>
            
            <div class="w3-row-padding">
                <?php if ($drawStatus['status'] === 'pending'): ?>
                    <div class="w3-col s12 m6" style="padding: 4px;">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <button type="submit" name="perform_draw" 
                                    class="w3-btn w3-green w3-round-large w3-block w3-hover-green w3-large"
                                    <?php echo !$canDraw ? 'disabled' : ''; ?>
                                    onclick="return confirm('¿Realizar sorteo? Esto asignará los amigos secretos.')">
                                <i class="fas fa-play"></i> Realizar Sorteo
                                <?php if (!$canDraw): ?>
                                    <br><small>(necesitas <?php echo MIN_PARTICIPANTS; ?>+ participantes)</small>
                                <?php endif; ?>
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="w3-col s12 m6" style="padding: 4px;">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <button type="submit" name="reset_draw" 
                                    class="w3-btn w3-orange w3-round-large w3-block w3-hover-orange w3-large"
                                    onclick="return confirm('¿Reiniciar sorteo? Se perderán las asignaciones actuales.')">
                                <i class="fas fa-redo"></i> Reiniciar Sorteo
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
                
                <?php if ($drawStatus['status'] === 'completed'): ?>
                    <div class="w3-col s12 m6" style="padding: 4px;">
                        <div class="w3-panel w3-pale-green w3-round">
                            <p><i class="fas fa-check-circle w3-text-green"></i> Sorteo completado</p>
                            <p><small><?php echo count($assignments); ?> asignaciones realizadas</small></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Asignaciones -->
        <?php if ($drawStatus['status'] === 'completed' && count($assignments) > 0): ?>
            <div class="w3-card-4 w3-padding w3-margin-top fade-in">
                <h2 class="w3-text-purple"><i class="fas fa-list"></i> Asignaciones</h2>
                <hr>
                
                <div class="assignments-grid">
                    <?php foreach ($assignments as $assignment): ?>
                        <div class="assignment-item">
                            <div class="w3-row">
                                <div class="w3-col s12">
                                    <strong><i class="fas fa-user"></i> <?php echo htmlspecialchars($assignment['giver_name']); ?></strong>
                                    <i class="fas fa-arrow-right w3-text-purple"></i>
                                    <strong class="w3-text-green"><i class="fas fa-gift"></i> <?php echo htmlspecialchars($assignment['receiver_name']); ?></strong>
                                    <br>
                                    <small class="w3-text-grey"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($assignment['giver_email']); ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>