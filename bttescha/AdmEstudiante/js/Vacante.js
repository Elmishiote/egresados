import { getUrl, formatearFecha } from '../../helpers/js/functions.js'

const API_URL = getUrl('/AdmEstudiante/php/Vacantes.php')

/**
 * ID de la carrera a la que pertenece el estudiante.
 * Esta debe ser almacenada internamente en el navegador cuando se inicie
 * sesión.
 * Para este archivo, es necesaria para evitar que el estudiante acceda a
 * vacantes de otras carreras.
 */
const ID_CARRERA = localStorage.hasOwnProperty('user_info')
    ? JSON.parse(localStorage.getItem('user_info')).IdCarrera
    : null

/**
 * ID del estudiante que ha iniciado sesión y va a postularse a una vacante.
 * Es necesario para poder realizar la postulación.
 */
const ID_ESTUDIANTE = localStorage.hasOwnProperty('user_info')
    ? JSON.parse(localStorage.getItem('user_info')).IdEstudiante
    : null

let vacante = {}
const charValues = { 0: 'No', 1: 'Sí' }

/**
 * Obtiene el ID de la vacante desde la URL.
 * @returns {number}
 */
function getIdVacante() {
    const idVacante = new URL(location.href).searchParams.get('v')
    return Number(idVacante)
}

/**
 * Valida si el estudiante ya se postuló a la vacante al cargar
 * el contenido.
 */
function validarPostulacion() {
    const data = {
        case: 'validarPostulacion',
        IdEstudiante: ID_ESTUDIANTE,
        IdVacante: getIdVacante(),
    }

    $.get(API_URL, data).done(function (res) {
        const respuesta = JSON.parse(res)

        if (respuesta.postulacion) {
            postulacionEnviada(respuesta.fecha_postulacion)
        }
    })
}

/**
 * Consulta a la API la vacante dada por su ID.
 * @param {number} idVacante ID de la vacante.
 */
function consultarVacante(idVacante) {
    $.get(API_URL, { case: 'obtenerVacante', idVacante, idCarrera: ID_CARRERA })
        .done(function (res) {
            const respuesta = JSON.parse(res)
            vacante = { ...respuesta.data }
            mostrarInfo()
        })
        .fail(function (err) {
            const errResponse = JSON.parse(err.responseText)
            alert(errResponse.mensaje)
            location.href = 'index.php'
        })
}

/**
 * Evalua si el texto contiene saltos de línea (`\r\n`).
 * @param {string} texto Texto a validar.
 * @returns `true` si contiene saltos de línea o `false` si no.
 */
function validarSaltosLinea(texto) {
    const regex = /\r\n/
    return regex.test(texto)
}

/**
 * Muestra la información en el HTML.
 */
function mostrarInfo() {
    $.each(vacante, function (key, value) {
        let elemento = $('#' + key)

        if (elemento.length > 0) {
            if (
                key !== 'NumeroVacantes' &&
                (Number(value) === 0 || Number(value) === 1)
            ) {
                elemento.text(charValues[value])
            } else if (key === 'FechaSolicitud') {
                const fechaFormateada = formatearFecha(value, 'long')
                elemento.text(fechaFormateada)
            } else {
                let texto = ''

                if (validarSaltosLinea(value)) {
                    texto = value.replace(/\r\n/g, '<br>')
                } else {
                    texto = value
                }

                elemento.html(texto)
            }
        }
    })
}

/**
 * Cambia el aspecto visual cuando el estudiante se ha postulado a la
 * vacante.
 * El botón de postulación se desactiva e indica la fecha de postulación.
 *
 * @param {string} fecha Fecha de postulación.
 */
function postulacionEnviada(fecha) {
    const btnPostulacion = $('#btn-postulacion')
    btnPostulacion.text('Postulado')
    btnPostulacion.prop('disabled', true)

    const mensajePostulacion = `
        <div class="mt-3 card">
            <div class="card-body text-secondary">
                Te postulaste a esta vacante el ${formatearFecha(
                    fecha,
                    'long'
                )}.
            </div>
        </div>
    `

    $('.postulacion-container div').append(mensajePostulacion)
}

/**
 * Cuando la postulación ha sido enviada (en el momento en que el estudiante
 * sube su CV), esta función cambia el contenido del modal por un mensaje de
 * éxito y lo cierra 3s después.
 *
 * @param {string} mensaje Respuesta de la API.
 */
function postulacionEnviadaModal(mensaje) {
    $('.modal-footer #confirmar-postulacion').remove()
    $('.modal-footer .btn.btn-secondary').text('Aceptar')

    const modal = $('#add-cv .modal-body')

    const template = `
        <div class="d-flex flex-column align-items-center my-5">
            <img
                class="mb-3"
                width="100px"
                height="100px"
                src="../assets/green-check.png"
                alt="Ícono green check" />
            <h2 class="mb-3 text-center fw-bold">
                ${mensaje}
            </h2>
        </div>
    `

    modal.html(template)

    setTimeout(() => {
        $('#add-cv').modal('hide')
    }, 3000)
}

$(document).ready(() => {
    const idVacante = getIdVacante()
    consultarVacante(idVacante)

    validarPostulacion()
})

$('#confirmar-postulacion').on('click', function () {
    $(this).prop('disabled', true)
    $(this).text('Postulando...')
    const cvInput = document.querySelector('#cv')
    const cvArchivo = cvInput.files[0]

    // Validar que se haya subido un archivo
    if (!cvArchivo) {
        $(this).text('Postularme')
        alert('Por favor, selecciona un archivo')
        return
    }

    // Validar que sea PDF
    const extensionesPermitidas = ['pdf']
    const extension = cvArchivo.name.split('.').pop().toLowerCase()

    if (extensionesPermitidas.indexOf(extension) === -1) {
        $(this).text('Postularme')
        alert('Solo se permiten archivos PDF.')
        return
    }

    /**
     * Asignar los valores que van a ser enviados.
     */
    const formData = new FormData()
    formData.append('cv', cvArchivo)
    formData.append('case', 'postulacion')
    formData.append('IdEstudiante', ID_ESTUDIANTE)
    formData.append('IdVacante', getIdVacante())

    // Realizar petición
    $.ajax({
        url: API_URL,
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (res) {
            const respuesta = JSON.parse(res)
            const { mensaje, fecha_postulacion } = respuesta

            postulacionEnviadaModal(mensaje)
            postulacionEnviada(fecha_postulacion)
        },
        error: function (err) {
            const errRespuesta = JSON.parse(err.responseText)
            alert(errRespuesta.mensaje)
        },
    })
})
