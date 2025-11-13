<?php

/**
 * Cierra sesión.
 * Elimina el arreglo global `$_SESSION` y redirecciona al formulario de
 * login.
 */
function logout()
{
    session_start();
    session_destroy();

    /**
     * Ruta de la raíz del proyecto.
     * @var string
     */
    $ruta_login = "../";

    /**
     * Redireccionar al formulario de login.
     */
    header("Location: " . $ruta_login);
}

logout();
