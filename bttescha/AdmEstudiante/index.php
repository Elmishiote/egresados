<?php

require __DIR__ . "/../auth/php/functions.php";

is_logged_in();

?>

<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Vacantes</title>
    <link rel="stylesheet" href="./../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="./../assets/css/style.css" />
    <link rel="stylesheet" href="./css/Vacantes.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <!--<script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script> -->
</head>

<body>
    <?php
    /**
     * Añadir el header del estudiante.
     */
    include_once __DIR__ . '/../includes/header.php';
    include_once __DIR__ . '/../includes/sidebar-estudiante.php';
    ?>

    <div class="hero container-fluid color-fondo px-sm-5 d-flex flex-column align-items-center">
        <h1 class="text-center text-dark mb-4 mb-sm-5 fw-bold">
            Busqueda de Vacantes
        </h1>

        <!-- Formulario para buscar chamba -->
        <form role="search" class="w-100 form-buscar" id="buscar-vacante">
            <div class="d-flex form-input flex-column gap-2 position-relative">
                <i class="fa-solid fa-magnifying-glass text-secondary position-absolute icono-buscar translate-middle ps-5"></i>
                <input type="text" class="form-control ps-5 py-md-3" placeholder="Puesto, empresa..." id="input-buscar" />
                <input class="btn btn-light px-md-4 py-md-3" type="submit" value="Buscar" />
            </div>
        </form>
    </div>

    <main class="container-fluid my-5">
        <h1 class="mb-4 fw-bold" id="titulo-vacantes">Últimas vacantes</h1>
        <div id="lista-vacantes"></div>
    </main>

    <script src="./../assets/js/jquery.js"></script>
    <script src="./../assets/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="./js/Vacantes.js"></script>
</body>

<!-- Incluir el footer -->
<?php
    include_once __DIR__ . '/../includes/footer.php';
?>

</html>