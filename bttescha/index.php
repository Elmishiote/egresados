<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Inciar sesión | SEyBT TESCHA</title>
    <link rel="stylesheet" href="./assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="./assets/css/style.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <!--<script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script> --></head>

<body class="vh-100 d-flex flex-column">
    <header class="color-fondo text-center py-2 px-5">
        <div class="col d-flex align-items-center justify-content-center gap-5">
            <img src="./assets/logo-header.png" alt="Logo" width="200px">
            <h1 class="color-oficial fw-bold text-center fs-3">
                Seguimiento de Egresados y Bolsa de Trabajooooo
            </h1>
        </div>
    </header>

    <main class="container flex-grow-1 mt-5">
        <h2 class="fw-bold mb-5 text-center">
            Inicia sesión para comenzar
        </h2>
        <form class="mx-auto" id="formLogin">
            <div class="mb-3">
                <label for="user" class="form-label">
                    Nombre de usuario
                    <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="user" name="user" />
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">
                    Contraseña
                    <span class="text-danger">*</span>
                </label>
                <input type="password" class="form-control" id="password" name="password" />
            </div>
            <button type="submit" class="btn btn-primary fw-bold w-100">
                Iniciar sesión
            </button>
            <p class="mt-4">¿Eres empleador? <a href="./AdmRepresentante/registro.php">Regístrate</a></p>
        </form>
    </main>

    <script src="./assets/js/jquery.js"></script>
    <script src="./assets/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="./auth/js/login.js"></script>
</body>

<?php
    include_once __DIR__ . '/includes/footer.php';
?>

</html>