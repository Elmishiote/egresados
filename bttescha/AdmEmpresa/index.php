<?php

require __DIR__ . "/../auth/php/functions.php";

is_logged_in();

?>

<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Empresas | SEYBT</title>
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
    include_once __DIR__ . '/../includes/sidebar-admin.php';
    ?>

    <main class="container my-5">
        <!-- Contenido -->
        <div>
            <div class="row row-gap-3 my-4">
                <div class="col-12 col-md-6">
                    <h2>Empresas</h2>
                </div>
                <div class="col-12 col-sm">
                    <!-- Barra de búsqueda -->
                    <form role="search" class="position-relative" id="formBusqueda">
                        <i class="fa-solid fa-magnifying-glass text-secondary position-absolute top-50 translate-middle ps-5"></i>
                        <input class="form-control ps-5" type="search" placeholder="Buscar por empresa" id="inputBuscar" />
                    </form>
                </div>
                <div class="col-12 col-sm-auto">
                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#formModal" id="abrirModalForm">
                        <i class="fa-solid fa-plus me-2"></i>
                        Nueva empresa
                    </button>
                </div>
            </div>

            <!-- Listado de empresas -->
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Email</th>
                            <th scope="col">Dirección</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <!-- .table-responsive -->
        </div>
    </main>

    <!-- Modal formulario -->
    <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalLabel">
                        Registrar nueva empresa
                    </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Formulario de registro -->
                    <form class="z-0 position-relative" id="formEmpresa">
                        <div class="mb-2">
                            <label for="nombreEmpresa" class="form-label">Nombre de la empresa
                                <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombreEmpresa" />
                        </div>
                        <div class="mb-2">
                            <label for="razonSocial" class="form-label">Razón social
                                <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="razonSocial" />
                        </div>
                        <div class="mb-2 row">
                            <div class="col-12 col-md">
                                <label for="emailEmpresa" class="form-label">Correo electrónico
                                    <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="emailEmpresa" />
                            </div>
                            <div class="col-12 col-md">
                                <label for="telefonoEmpresa" class="form-label">Teléfono
                                    <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="telefonoEmpresa" />
                            </div>
                        </div>
                        <h5 class="mt-3">
                            Dirección <span class="text-danger">*</span>
                        </h5>
                        <div class="mb-2">
                            <label for="calleEmpresa" class="form-label">Calle</label>
                            <input type="text" class="form-control" id="calleEmpresa" />
                        </div>
                        <div class="mb-2" id="divColonia">
                            <label for="coloniaEmpresa" class="form-label">Colonia
                                <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="coloniaEmpresa" />
                        </div>
                        <div class="mb-3" id="divMunicipio">
                            <label for="municipioEmpresa" class="form-label">Municipio/delegación y estado
                                <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="municipioEmpresa" />
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
    <script type="module" src="./js/AdmEmpresa.js"></script>
</body>

<!-- Incluir el footer -->
<?php
    include_once __DIR__ . '/../includes/footer.php';
?>

</html>