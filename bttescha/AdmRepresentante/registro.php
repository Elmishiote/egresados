<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar representante</title>
    <link rel="stylesheet" href="./../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="./../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <!--<script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script> --></head>

<body>
    <?php
    /**
     * Añadir el header del empleador.
     */
    include_once __DIR__ . '/../includes/header.php';
    ?>

    <main class="container-fluid d-flex flex-column align-items-center">
        <h2 class="text-center my-5">Registrarte como empleador</h2>

        <div class="row w-100 mb-5">
            <div class="col-12 col-sm-6 col-lg-4 mx-auto">
                <form id="form">
                    <div class="mb-3">
                        <label for="numConvenio" class="form-label">Ingresa el número de convenio</label>
                        <input type="number" name="numConvenio" id="numConvenio" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Validar</button>
                </form>
            </div>
        </div>
    </main>

    <script src="./../assets/js/jquery.js"></script>
    <script src="./../assets/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="./js/AdmRepresentante.js"></script>
</body>

<!-- Incluir el footer -->
<?php
    include_once __DIR__ . '/../includes/footer.php';
?>

</html>