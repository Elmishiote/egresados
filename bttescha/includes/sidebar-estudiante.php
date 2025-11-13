<?php

/**
 * Obtener la ruta actual.
 */
$current_url = $_SERVER['REQUEST_URI'];

/**
 * Añadir dinámicamente la clase de CSS, dependiendo de la ruta
 * actual.
 */
$estudiante_class = (strpos($current_url, "AdmEstudiante") !== false) ? "bg-primary" : "";
$postulaciones_class = (strpos($current_url, "AdmEstudiante/mis-postulaciones.php") !== false) ? "bg-primary" : "";
$logros_class = (strpos($current_url, "AdmEstudiante/logros-profesionales.php") !== false) ? "bg-primary" : "";

?>

<!-- Button to toggle sidebar (Hamburger icon) -->
<button class="btn btn-dark d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvas" aria-controls="offcanvas">
    <i class="fa-solid fa-bars"></i> <!-- Icono de hamburguesa -->
</button>

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
            <a href="./" class="text-decoration-none text-light <?php echo $estudiante_class ?>">
                <li class="py-3 px-4 my-3 rounded-pill" role="button">
                    <i class="fa-solid fa-house me-2"></i>
                    Inicio
                </li>
            </a>
            <a href="./mis-postulaciones.php" class="text-decoration-none text-light <?php echo $postulaciones_class ?>">
                <li class="py-3 px-4 my-3 rounded-pill" role="button">
                    <i class="fa-solid fa-list-ul me-2"></i>
                    Mis postulaciones
                </li>
            </a>
            <!-- Nueva opción: Logros Profesionales -->
            <a href="./logros-profesionales.php" class="text-decoration-none text-light <?php echo $logros_class ?>">
                <li class="py-3 px-4 my-3 rounded-pill" role="button">
                    <i class="fa-solid fa-trophy me-2"></i> <!-- Icono de trofeo -->
                    Logros Profesionales
                </li>
            </a>
        </ul>
    </div>
</div>
<!-- Sidebar -->
