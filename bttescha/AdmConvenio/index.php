<?php

require __DIR__ . "/../auth/php/functions.php";

is_logged_in();

?>

<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Convenios | SEYBT</title>
    <link rel="stylesheet" href="./../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="./../assets/css/style.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <!--<script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script> -->
</head>

<body>
    <?php
    /**
     * Añadir el header del administrador.
     */
    include_once __DIR__ . '/../includes/header.php';
    include_once __DIR__ . '/../includes/sidebar-admin.php';
    ?>

    <main class="container my-4">
        <h2>Convenios con Empresas</h2>

        <!-- Tabs -->
        <!-- <ul class="nav nav-tabs my-4">
            <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="#">Registrar convenio</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Otra opción</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Otra opción</a>
            </li>
        </ul> -->

        <!-- Contenido -->
        <div>
            <div class="row row-gap-3 my-4">
                <div class="col-12 col-md-6">
                    <h3></h3>
                </div>
                <div class="col-12 col-sm">
                    <!-- Barra de búsqueda -->
                    <form role="search" class="position-relative" id="formBusqueda">
                        <i class="fa-solid fa-magnifying-glass text-secondary position-absolute top-50 translate-middle ps-5"></i>
                        <input class="form-control ps-5" type="search" placeholder="Buscar por número de convenio o empresa" id="inputBuscar" />
                    </form>
                </div>
                <div class="col-12 col-sm-auto">
                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#formModal" id="abrirModalForm">
                        <i class="fa-solid fa-plus me-2"></i>
                        Nuevo convenio
                    </button>
                </div>
            </div>

            <!-- Listado de convenios -->
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Número de convenio</th>
                        <th scope="col">Empresa</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </main>

    <!-- Modal formulario -->
    <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalLabel">
                        Registrar nuevo convenio
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Formulario de registro -->
                    <form id="formConvenio">
                        <div class="mb-3">
                            <label for="numConvenio" class="form-label">Número de convenio
                                <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="numConvenio" />
                        </div>
                        <div class="mb-3">
                            <label for="empresa" class="form-label">Empresa
                                <span class="text-danger">*</span></label>
                            <select class="form-select" id="empresa"></select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa-solid fa-floppy-disk me-1"></i>
                            Guardar
                        </button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="./../assets/js/jquery.js"></script>
    <script src="./../assets/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="./js/AdmConvenio.js"></script>
</body>

<!-- Incluir el footer -->
<?php
    include_once __DIR__ . '/../includes/footer.php';
?>

</html>