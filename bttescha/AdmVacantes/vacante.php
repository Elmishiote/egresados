<?php

require __DIR__ . "/../auth/php/functions.php";

is_logged_in();

?>

<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vacante</title>
    <link rel="stylesheet" href="./../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="./../assets/css/style.css">
    <link rel="stylesheet" href="./css/AdmVacantes.css">
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

    <main class="container-fluid my-5">
        <h1 id="Nombre">Cargando...</h1>
        <p class="text-secondary">Publicada el: <span id="FechaSolicitud">Cargando...</span></p>

        <div class="my-5">
            <div class="mb-4">
                <h2>Información sobre la vacante</h2>

                <p>
                    Vacante para la carrera: <span class="fw-bold" id="NombreCarrera">Cargando...</span>
                    <br />
                    Días y horario laboral: <span class="fw-bold" id="DiasHorarioLaboral">Cargando...</span>
                    <br />
                    Número de vacantes: <span class="fw-bold" id="NumeroVacantes">Cargando...</span>
                    <br />
                    ¿Ofrece prestaciones de ley? <span class="fw-bold" id="PrestacionesLey">Cargando...</span>
                    <br />
                    ¿Ofrece una oferta económica? <span class="fw-bold" id="OfertaEconomica">Cargando...</span>
                    <br />
                    ¿Ofrece capacitación? <span class="fw-bold" id="Capacitacion">Cargando...</span>
                    <br />
                    Condición Laboral <span class="fw-bold" id="CondicionLaboral">Cargando...</span>
                </p>
            </div>

            <div class="mb-4">
                <h3>Descripción</h3>
                <p id="InformacionVacante">Cargando...</p>
            </div>

            <div class="mb-4">
                <h3>Funciones del puesto</h3>
                <p id="Funciones">Cargando...</p>
            </div>

            <div class="mb-4">
                <h3>Prestaciones</h3>
                <p id="Prestaciones">Cargando...</p>
            </div>

            <div class="mb-4">
                <h2>Sobre el solicitante</h2>
                <p>
                    Género: <span class="fw-bold" id="Genero">Cargando...</span>
                    <br />
                    ¿Requiere experiencia? <span class="fw-bold" id="Experiencia">Cargando...</span>
                </p>

                <div class="mb-4">
                    <h3>Habilidades que debe tener</h3>
                    <p id="Habilidades">Cargando...</p>
                </div>
            </div>
        </div>
    </main>

    <script src="./../assets/js/jquery.js"></script>
    <script src="./../assets/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="./js/AdmVerVacante.js"></script>
</body>

<!-- Incluir el footer -->
<?php
    include_once __DIR__ . '/../includes/footer.php';
?>

</html>