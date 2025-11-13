<?php

require __DIR__ . "/../auth/php/functions.php";

is_logged_in();

?>

<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis postulaciones</title>
    <link rel="stylesheet" href="./../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="./../assets/css/style.css" />
    <link rel="stylesheet" href="./css/Vacantes.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <!--<script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script> -->
</head>

<style>
    body {
        display: flex;
        flex-direction: column;
        height: 100vh;
    }

    main {
        flex: 1;
    }

    .badge {
        font-size: 0.85em;
    }
</style>

<body>
    <?php
    /**
     * Añadir el header del estudiante.
     */
    include_once __DIR__ . '/../includes/header.php';
    include_once __DIR__ . '/../includes/sidebar-estudiante.php';
    ?>

    <main class="container my-5">
        <h2 class="fw-bold">Mis postulaciones</h2>

        <!-- Listado de postulaciones -->
        <div class="table-responsive my-5">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Empresa</th>
                        <th scope="col">Fecha de postulacion</th>
                        <th scope="col">Estatus</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Aquí puedes insertar los registros -->
                </tbody>
            </table>
        </div>
        <!-- .table-responsive -->
    </main>

    <!-- Incluir el footer -->
    <?php
    include_once __DIR__ . '/../includes/footer.php';
    ?>

    <script src="./../assets/js/jquery.js"></script>
    <script src="./../assets/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="./js/MisPostulaciones.js"></script>
</body>
<!-- Incluir el footer -->
<?php
    include_once __DIR__ . '/../includes/footer.php';
?>

</html>
