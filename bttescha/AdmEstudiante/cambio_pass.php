<?php

require_once __DIR__ . "/../config/database.php";
require __DIR__ . "/../auth/php/functions.php";

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
is_logged_in(); 

$db = conectar_db();
if (!$db) {
    exit('Error de conexión a la base de datos');
}

$error = '';
$success = '';

// Verificar si el ID de usuario está configurado correctamente en la sesión
if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    exit('ID de usuario no encontrado en la sesión.');
}

// Obtener el ID del usuario actualmente autenticado
$idUsuario = $_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);

    // Validaciones
    if (empty($password) || empty($confirmPassword)) {
        $error = 'Ambos campos son obligatorios.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($password) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } else {
        // Hash de la nueva contraseña
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Actualizar la contraseña en la base de datos
        $query = "UPDATE usuario SET Contraseña = ? WHERE IdUsuario = ? AND Borrado = 1";
        $stmt = $db->prepare($query);

        if ($stmt) {
            $stmt->bind_param('si', $passwordHash, $idUsuario);

            if ($stmt->execute()) {
                $success = 'La contraseña se ha actualizado correctamente.';
                // Cerrar la sesión del usuario después del cambio, si lo deseas
                $stmt->close();
                header('Location: /seybt/bttescha/AdmEstudiante/perfil_egresado.php');
                exit();
            } else {
                $error = 'Ocurrió un error al actualizar la contraseña. Intente nuevamente.';
                error_log("Error al actualizar la contraseña: " . $stmt->error);
            }
        } else {
            $error = 'Error al preparar la consulta.';
            error_log("Error al preparar la consulta: " . $db->error);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cambiar Contraseña | SEyBT TESCHA</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <!--<script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script> -->
</head>

<body class="vh-100 d-flex flex-column">
    <header class="color-fondo text-center py-2 px-5">
        <div class="col d-flex align-items-center justify-content-center gap-5">
            <img src="../assets/logo-header.png" alt="Logo" width="200px">
            <h1 class="color-oficial fw-bold text-center fs-3">
                Seguimiento de Egresados y Bolsa de Trabajo
            </h1>
        </div>
    </header>

    <main class="container flex-grow-1 mt-5">
        <h2 class="fw-bold mb-5 text-center">
            Cambiar Contraseña
        </h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success text-center">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form class="mx-auto" style="max-width: 400px;" action="cambio_pass.php" method="POST">
            <div class="mb-3">
                <label for="password" class="form-label">
                    Nueva Contraseña
                    <span class="text-danger">*</span>
                </label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">
                    Confirmar Contraseña
                    <span class="text-danger">*</span>
                </label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-secondary fw-bold" onclick="window.location.href='index.php'">
                    Regresar
                </button>
                <button type="submit" class="btn btn-primary fw-bold">
                    Continuar
                </button>
            </div>
        </form>
    </main>

    <script src="../assets/js/jquery.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

<!-- Incluir el footer -->
<?php
    include_once __DIR__ . '/../includes/footer.php';
?>

</html>
