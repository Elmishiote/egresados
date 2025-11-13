<?php

require __DIR__ . "/../auth/php/functions.php";

is_logged_in();

?>

<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Editar datos de la empresa</title>
    <link rel="stylesheet" href="./../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="./../assets/css/style.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <!--<script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script> --></head>

<body>
    <?php
    /**
     * Añadir el header del administrador.
     */
    include_once __DIR__ . '/../includes/header.php';
    include_once __DIR__ . '/../includes/sidebar-empleador.php';
    ?>

    <main class="container-fluid my-5">
        <h1 class="text-center">Actualiza los datos de la empresa</h1>

        <div class="row w-100 mt-4 mx-auto">
            <div class="col-12 col-sm-6 col-lg-4 mx-auto">
                <form id="formActualizarDatosEmpresa" class="z-0 position-relative">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre
                            <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="nombre" class="form-control" />
                    </div>

                    <div class="mb-3">
                        <label for="razonSocial" class="form-label">Razón social
                            <span class="text-danger">*</span></label>
                        <input type="text" name="razonSocial" id="razonSocial" class="form-control" />
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico
                            <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" class="form-control" />
                    </div>

                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono
                            <span class="text-danger">*</span></label>
                        <input type="number" name="telefono" id="telefono" class="form-control" />
                    </div>

                    <fieldset>
                        <legend>Domicilio</legend>

                        <div class="mb-3">
                            <label for="calle" class="form-label">Calle y número
                                <span class="text-danger">*</span></label>
                            <input type="text" name="calle" id="calle" class="form-control" />
                        </div>

                        <div class="mb-3" id="divColonia">
                            <label for="colonia" class="form-label">Colonia
                                <span class="text-danger">*</span></label>
                            <input type="text" name="colonia" id="colonia" class="form-control" />
                        </div>

                        <div class="mb-3" id="divMunicipio">
                            <label for="municipio" class="form-label">Municipio/delegación y estado
                                <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="municipio" />
                        </div>
                    </fieldset>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa-solid fa-floppy-disk me-1"></i>
                        Guardar
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script src="./../assets/js/jquery.js"></script>
    <script src="./../assets/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="./js/AdmRepresentanteActualizacion.js"></script>
</body>

<!-- Incluir el footer -->
<?php
    include_once __DIR__ . '/../includes/footer.php';
?>

</html>