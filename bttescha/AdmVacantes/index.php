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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <!--<script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script> --></head>

<body>
    <?php
    /**
     * Añadir el header del empleador.
     */
    include_once __DIR__ . '/../includes/header.php';
    include_once __DIR__ . '/../includes/sidebar-empleador.php';
    ?>

    <main class="container">
        <div class="row row-gap-3 my-4">
            <div class="col-12 col-md-6">
                <h1>Vacantes</h1>
            </div>
            <div class="col-12 col-sm">
                <!-- Barra de búsqueda -->
                <form role="search" class="position-relative" id="formBusqueda">
                    <i class="fa-solid fa-magnifying-glass text-secondary position-absolute top-50 translate-middle ps-5"></i>
                    <input class="form-control ps-5" type="search" placeholder="Buscar por nombre de vacante o carrera" id="inputBuscar" />
                </form>
            </div>
            <div class="col-12 col-sm-auto">
                <a class="btn btn-primary w-100" href="./crear-vacante.php">
                    <i class="fa-solid fa-plus me-2"></i>
                    Nueva vacante
                </a>
            </div>
        </div>

        <!-- Listado de vacantes -->
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nombre de la vacante</th>
                        <th scope="col">Carrera</th>
                        <th scope="col">Postulaciones</th>
                        <th scope="col">Fecha de publicación</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <!-- .table-responsive -->
    </main>

    <script src="./../assets/js/jquery.js"></script>
    <script src="./../assets/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="./js/AdmVacantes.js"></script>
</body>

<!-- Incluir el footer -->
<?php
    include_once __DIR__ . '/../includes/footer.php';
?>

</html>