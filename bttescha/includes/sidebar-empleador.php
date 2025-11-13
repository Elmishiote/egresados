<?php

/**
 * Obtener la ruta actual.
 */
$current_url = $_SERVER['REQUEST_URI'];

/**
 * Añadir dinámicamete la clase de CSS, dependiendo de la ruta
 * actual.
 */
$empleador_class = (strpos($current_url, "AdmVacantes") !== false) ? "bg-primary" : "";
$crear_vacante_class = (strpos($current_url, "AdmVacantes/crear-vacante.php") !== false) ? "bg-primary" : "";

?>

<!-- Sidebar -->
<div class="offcanvas offcanvas-start bg-dark" tabindex="-1" id="offcanvas" aria-labelledby="offcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title text-light" id="offcanvasLabel">
            SEYBT
        </h5>
        <button type="button" class="btn-close bg-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body text-light">
        <ul class="text-light p-0">
            <a href="./" class="text-decoration-none text-light <?php echo $empleador_class ?>">
                <li class="py-3 px-4 my-3 rounded-pill" role="button">
                    <i class="fa-solid fa-house me-2"></i>
                    Inicio
                </li>
            </a>
            <a href="./crear-vacante.php" class="text-decoration-none text-light <?php echo $crear_vacante_class ?>">
                <li class="py-3 px-4 my-3 rounded-pill" role="button">
                    <i class="fa-solid fa-plus me-2"></i>
                    Crear vacante
                </li>
            </a>
        </ul>
    </div>
</div>
<!-- Sidebar -->