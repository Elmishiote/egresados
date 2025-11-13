<?php

require __DIR__ . "/../auth/php/functions.php";

is_logged_in();

?>

<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Vacante</title>
    <link rel="stylesheet" href="./../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="./../assets/css/style.css" />
    <link rel="stylesheet" href="./css/Vacante.css" />
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

    <main class="container-fluid my-5 contenedor-vacante">
        <div class="row gap-3">
            <div class="col">
                <div class="border-bottom mb-3">
                    <h1 class="fw-bolder" id="Nombre">Cargando...</h1>
                    <p class="text-secondary fw-bold fs-5 mb-1" id="NombreEmpresa">
                        Cargando...
                    </p>
                    <p class="text-secondary">
                        Publicada el:
                        <span id="FechaSolicitud">Cargando...</span>
                    </p>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <h3>Descripción de la vacante</h3>
                        <p id="InformacionVacante">Cargando...</p>
                    </div>
                    <div class="mb-3">
                        <h3>Funciones</h3>
                        <p id="Funciones">Cargando...</p>
                    </div>
                    <div class="mb-3">
                        <h3>Habilidades</h3>
                        <p id="Habilidades">Cargando...</p>
                    </div>
                    <div class="mb-3">
                        <h3>Prestaciones</h3>
                        <p id="Prestaciones">Cargando...</p>
                    </div>
                    <div class="mb-3">
                        <h3>Información adicional</h3>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item fw-bold">
                                Días y horario laboral:
                                <span class="fw-normal" id="DiasHorarioLaboral">Cargando...</span>
                            </li>
                            <li class="list-group-item fw-bold">
                                Carrera:
                                <span class="fw-normal" id="NombreCarrera">Cargando...</span>
                            </li>
                            <li class="list-group-item fw-bold">
                                Número de vacantes:
                                <span class="fw-normal" id="NumeroVacantes">Cargando...</span>
                            </li>
                            <li class="list-group-item fw-bold">
                                Género solicitado:
                                <span class="fw-normal" id="Genero">Cargando...</span>
                            </li>
                            <li class="list-group-item fw-bold">
                                Requieres experiencia:
                                <span class="fw-normal" id="Experiencia">Cargando...</span>
                            </li>
                            <li class="list-group-item fw-bold">
                                Ofrece oferta económica:
                                <span class="fw-normal" id="OfertaEconomica">Cargando...</span>
                            </li>
                            <li class="list-group-item fw-bold">
                                Ofrece condición laboral:
                                <span class="fw-normal" id="CondicionLaboral">Cargando...</span>
                            </li>
                            <li class="list-group-item fw-bold">
                                Ofrece capacitación:
                                <span class="fw-normal" id="Capacitacion">Cargando...</span>
                            </li>
                            <li class="list-group-item fw-bold">
                                Ofrece prestaciones de ley:
                                <span class="fw-normal" id="PrestacionesLey">Cargando...</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- .row -->
            </div>
            <div class="col-sm-4 postulacion-container">
                <div>
                    <!-- Boton para abrir el modal de postulacion -->
                    <button id="btn-postulacion" type="button" class="btn btn-primary w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#add-cv">
                        Postularme
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal -->
    <div class="modal fade" id="add-cv" tabindex="-1" aria-labelledby="cvModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="cvModalLabel">
                        Añade tu CV para continuar
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>
                        Para postularte a esta vacante es necesario que
                        agregues tu CV actualizado en formato PDF.
                    </p>

                    <!-- Form para subir CV -->
                    <form id="form-cv" enctype="multipart/form-data">
                        <div class="mb-3">
                            <input class="form-control" type="file" accept=".pdf" id="cv" name="cv" />
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmar-postulacion">
                        Postularme
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="./../assets/js/jquery.js"></script>
    <script src="./../assets/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="./js/Vacante.js"></script>
</body>

<!-- Incluir el footer -->
<?php
    include_once __DIR__ . '/../includes/footer.php';
?>

</html>