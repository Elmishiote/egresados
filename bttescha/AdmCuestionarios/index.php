<?php

require __DIR__ . "/../auth/php/functions.php";

// Si el usuario ya está autenticado (logueado con nick/pass), lo mandamos al index por defecto
if (is_logged_in()) {
    header("Location: ../AdmEstudiante/");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cuestionarios Egresados | SEYBT</title>
    <link rel="stylesheet" href="./../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="./../assets/css/style.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <style>
        .form-cuestionario { max-width: 450px; }
    </style>
</head>

<body class="vh-100 d-flex flex-column">
    <header class="color-fondo text-center py-2 px-5 sticky-top">
        <div class="col d-flex align-items-center justify-content-center gap-5">
            <img src="./../assets/logo-header.png" alt="Logo" width="200px">
            <h1 class="color-oficial fw-bold text-center fs-3">
                Acceso a Cuestionarios
            </h1>
        </div>
    </header>

    <main class="container flex-grow-1 mt-5 d-flex flex-column align-items-center">
        <h2 class="fw-bold mb-4 text-center">
            Verificación por Matrícula / Control
        </h2>
        <p class="text-center text-secondary mb-5" style="max-width: 450px;">
            Ingresa tu matrícula y selecciona el cuestionario para el cual has sido convocado.
        </p>

        <form class="mx-auto form-cuestionario" id="formCuestionarioAcceso">
            <div class="mb-4">
                <label for="matricula" class="form-label">
                    Matrícula o Número de Control
                    <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="matricula" name="matricula" required />
            </div>

            <div class="mb-4">
                <label for="tipoCuestionario" class="form-label">
                    Selecciona el Cuestionario
                    <span class="text-danger">*</span>
                </label>
                <select class="form-select" id="tipoCuestionario" name="tipoCuestionario" required>
                    <option value="">Seleccionar...</option>
                    <option value="1">1. Actualización de Datos (Post-Octavo)</option>
                    <option value="2">2. Oportunidad Laboral (Post-Residencias)</option>
                    <option value="3">3. Satisfacción (Pre-Titulación)</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary fw-bold w-100">
                Verificar y Acceder
            </button>
        </form>
        <div id="alerta-cuestionario"></div>

        <p class="mt-4">¿Eres usuario regular? <a href="../index.php">Inicia Sesión</a></p>
    </main>

    <script src="./../assets/js/jquery.js"></script>
    <script src="./../assets/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="./js/AdmCuestionarios.js"></script>
</body>
<?php include_once __DIR__ . '/../includes/footer.php'; ?>
</html>