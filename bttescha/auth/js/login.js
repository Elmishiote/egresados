import { getUrl, crearAlerta } from '../../helpers/js/functions.js'

/**
 * URL a la que se realizarán peticiones HTTP.
 */
const API_URL = getUrl('/auth/php/login.php')

/**
 * URL por defecto a la que redireccionará una vez que se iniciar sesión.
 */
const URL_USUARIO = {
    1: '/AdmEmpresa',
    2: '/AdmVacantes',
    3: '/AdmEstudiante',
    4: '/AdmCuestionarios',
}

const form = $('#formLogin')
const submitButton = form.children('button[type="submit"]')

/**
 * Valida que los valores no estén vacíos.
 * Si están vacíos (ambos o solo uno) crea una alerta.
 *
 * @param {string} user     Nombre de usuario.
 * @param {string} password Contraseña.
 * @returns                 `true` si ambos campos están llenos o `false` si uno
 *                          o ambos campos están vacíos.
 */
function validarFormulario(user, password) {
    $('.alerta-login').remove()

    if (!user || !password) {
        submitButton.text('Iniciar sesión')

        const alerta = crearAlerta(
            'Todos los campos son obligatorios',
            'alert-danger'
        )
        alerta.classList.add('mx-auto', 'alerta-login', 'mt-4')
        $('main').append(alerta)

        /**
         * Eliminar alerta después de 3s.
         */
        setTimeout(() => {
            $('.alerta-login').remove()
        }, 3000)

        return false
    }

    return true
}

form.submit(function (e) {
    e.preventDefault()

    /**
     * Obtener los valores.
     */
    const user = $('#user').val()
    const password = $('#password').val()

    /**
     * Detener la ejecución si los campos están vacíos.
     */
    if (!validarFormulario(user, password)) return

    /**
     * Cambiar el texto del botón a 'Iniciando sesión' cuando se de
     * clic en él.
     */
    submitButton.text('Iniciando sesión...')

    /**
     * Información que se va a enviar en la petición.
     */
    const data = {
        case: 'login',
        data: { user, password },
    }

    $.post(API_URL, data)
        .done(function (res) {
            const response = JSON.parse(res)
            const { redirect } = response.data // Obtener la URL de redirección de la respuesta

            if (redirect) {
                // Redireccionar a la URL proporcionada
                location.href = redirect
            } else {
                // Si no hay URL de redirección, redirigir al usuario según el tipo
                location = getUrl() + URL_USUARIO[response.data.tipo_usuario]
            }

            /**
             * Guardar en localStorage los datos necesarios.
             */
            localStorage.setItem('user_info', JSON.stringify(response.data.user_info))
        })
        .fail(function (err) {
            const error = JSON.parse(err.responseText)
            alert(error.mensaje)
            submitButton.text('Iniciar sesión')
        })
})
