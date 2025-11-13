<?php

require __DIR__ . "/../auth/php/functions.php";

is_logged_in();

?>
<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postulaciones</title>
    <link rel="stylesheet" href="./../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="./../assets/css/style.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <!--<script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script> -->
</head>
<style>
    .badge {
        font-size: 0.85em;
    }
</style>

<body>
    <?php
    /**
     * Añadir el header del empleador.
     */
    include_once __DIR__ . '/../includes/header.php';
    include_once __DIR__ . '/../includes/sidebar-empleador.php';
    ?>

    <main class="container my-5">
        <h1>Lista de postulantes</h1>

        <div class="card my-4">
            <div class="card-body">
                <h5 class="card-title">Cargando...</h5>
                <h6 class="card-subtitle mb-2 text-body-secondary">
                    Publicada el: <span id="fechaPublicacion"></span>
                </h6>
                <p class="card-text">Vacante dirigida a la carrera: <span id="carrera">Cargando...</span></p>
                <a href="#" id="btnVerVacante" class="btn btn-primary">Ver vacante</a>
            </div>
        </div>

        <!-- Listado de vacantes -->
        <div class="table-responsive">
            <table class="table align-middle table-hover" id="postulantes">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nombre del postulante</th>
                        <th scope="col">Fecha de postulación</th>
                        <th scope="col">CV</th>
                        <th scope="col">Estatus</th>
                        <th scope="col">Cambiar estatus</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <!-- .table-responsive -->
    </main>

    <script src="./../assets/js/jquery.js"></script>
    <script src="./../assets/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="./js/Postulaciones.js"></script>
</body>

<!-- Incluir el footer -->
<?php
    include_once __DIR__ . '/../includes/footer.php';
?>

</html>