<?php

require __DIR__ . "/../auth/php/functions.php";

is_logged_in();

?>

<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Crear una nueva vacante</title>
    <link rel="stylesheet" href="./../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="./../assets/css/style.css" />
    <link rel="stylesheet" href="./css/AdmVacantes.css" />
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
        <h1 class="fw-bold">Crea una nueva vacante</h1>

        <!-- Formulario para registrar la vacante -->
        <form id="formVacante" class="mt-5">
            <div class="mb-3">
                <label for="nombreVacante" class="form-label">Nombre de la vacante
                    <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" name="Nombre" id="nombreVacante" />
            </div>

            <div class="mb-3">
                <div class="row">
                    <div class="col-12 col-md my-auto">
                        <label for="carreraSelect" class="form-label my-auto mb-3">Carrera a la que está dirigida
                            <span class="text-danger">*</span>
                        </label>
                    </div>
                    <div class="col-12 col-md-7">
                        <select name="IdCarrera" id="carreraSelect" class="form-select">
                            <option value="">Seleccionar</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="informacionVacante" class="form-label">Descripción de la vacante
                    <span class="text-danger">*</span></label>
                <textarea name="InformacionVacante" id="informacionVacante" class="form-control" rows="3"></textarea>
            </div>

            <div class="mb-3">
                <label for="habilidades" class="form-label">Habilidades que debe tener el solicitante
                    <span class="text-danger">*</span></label>
                <textarea name="Habilidades" id="habilidades" class="form-control" rows="3"></textarea>
            </div>

            <div class="mb-3">
                <div class="row">
                    <div class="col-12 col-md my-auto">
                        <label for="genero" class="form-label my-auto mb-3">Género solicitado
                            <span class="text-danger">*</span>
                        </label>
                    </div>
                    <div class="col-12 col-md-7">
                        <select name="Genero" id="genero" class="form-select">
                            <option value="">Seleccionar</option>
                            <option value="Hombre">Hombre</option>
                            <option value="Mujer">Mujer</option>
                            <option value="Indistinto">Indistinto</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="funciones" class="form-label">Funciones del puesto
                    <span class="text-danger">*</span></label>
                <textarea name="Funciones" id="funciones" class="form-control" rows="3"></textarea>
            </div>

            <div class="mb-3 row">
                <div class="col my-auto">
                    <label for="numeroVacantes" class="form-label mb-3">Número de vacantes
                        <span class="text-danger">*</span></label>
                    <input type="number" min="0" max="99" class="form-control" name="NumeroVacantes" id="numeroVacantes" />
                </div>
                <div class="col my-auto">
                    <label for="experiencia" class="form-label mb-3">¿Experiencia necesaria?
                        <span class="text-danger">*</span></label>
                    <select name="Experiencia" id="experiencia" class="form-select">
                        <option value="">Seleccionar</option>
                        <option value="1">Sí</option>
                        <option value="0">No</option>
                    </select>
                </div>
            </div>

            <div class="mb-3 row">
                <div class="col-12 my-auto mb-3">
                    <label for="diasHorarioLaboral" class="form-label">Días y horario laboral
                        <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="DiasHorarioLaboral" id="diasHorarioLaboral" />
                </div>
            </div>

            <div class="mb-3">
                <label for="prestaciones" class="form-label">Escribe las prestaciones que ofrece en la vacante
                    <span class="text-danger">*</span></label>
                <textarea name="Prestaciones" id="prestaciones" class="form-control" rows="3"></textarea>
            </div>

            <div class="mb-3 row">
                <div class="col-12 col-sm my-auto mb-3">
                    <label for="prestacionesLey" class="form-label">¿Ofrece prestaciones de ley?
                        <span class="text-danger">*</span></label>
                    <select name="PrestacionesLey" id="prestacionesLey" class="form-select">
                        <option value="">Seleccionar</option>
                        <option value="1">Sí</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="col-12 col-sm my-auto mb-3">
                    <label for="ofertaEconomica" class="form-label">¿Ofrece una oferta económica?
                        <span class="text-danger">*</span></label>
                    <select name="OfertaEconomica" id="ofertaEconomica" class="form-select">
                        <option value="">Seleccionar</option>
                        <option value="1">Sí</option>
                        <option value="0">No</option>
                    </select>
                </div>
            </div>

            <div class="mb-3 row">
                <div class="col-12 col-sm my-auto mb-3">
                    <label for="capacitacion" class="form-label">¿Ofrece capacitación?
                        <span class="text-danger">*</span></label>
                    <select name="Capacitacion" id="capacitacion" class="form-select">
                        <option value="">Seleccionar</option>
                        <option value="1">Sí</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="col-12 col-sm my-auto mb-3">
                    <label for="condicionLaboral" class="form-label">Condición Laboral
                        <span class="text-danger">*</span></label>
                    <select name="CondicionLaboral" id="condicionLaboral" class="form-select">
                        <option value="">Seleccionar</option>
                        <option value="1">Sí</option>
                        <option value="0">No</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Registrar vacante
            </button>
        </form>
    </main>

    <script src="./../assets/js/jquery.js"></script>
    <script src="./../assets/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="./js/AdmCrearVacante.js"></script>
</body>

<!-- Incluir el footer -->
<?php
    include_once __DIR__ . '/../includes/footer.php';
?>

</html>