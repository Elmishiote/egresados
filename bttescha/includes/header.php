<header class="color-fondo text-center py-2 px-5 sticky-top">
    <div class="row">
        <div class="col-2 d-flex align-items-center">
            <button class="btn btn-primary bg-oficial" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvas" aria-controls="offcanvas">
                <i role="button" class="fa-solid fa-bars text-light fs-5"></i>
            </button>
        </div>
        <div class="col d-flex align-items-center justify-content-center gap-5">
            <img src="../assets/logo-header.png" alt="Logo" width="200px">
            <h1 class="color-oficial fw-bold text-center fs-3">
                Seguimiento de Egresados y Bolsa de Trabajo
            </h1>
        </div>
        <div class="col-2 d-flex align-items-center justify-content-end text-white gap-2">
            <div class="dropdown">
                <button class="btn bg-white p-2 d-flex align-items-center justify-content-center dropdown-toggle rounded-pill" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-user color-oficial"></i>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <button class="dropdown-item" type="button" id="logout">Cerrar sesión</button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>

<script type="module">
    import {
        getUrl
    } from '../helpers/js/functions.js'

    const logoutBtn = document.querySelector('#logout')
    const URL_LOGOUT = getUrl('/auth/php/logout.php');

    logoutBtn.addEventListener('click', async function() {
        fetch(URL_LOGOUT)
            .then(response => {
                if (response.ok) {
                    /**
                     * Redirigir al formulario de login cuando se cierre sesión.
                     */
                    location.href = getUrl()
                } else {
                    console.error("Error al cerrar sesión");
                }
            })
            .catch(error => {
                console.error("Error al cerrar sesión:", error);
            });
    })
</script>