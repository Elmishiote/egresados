import { getUrl, formatearFecha } from '../../helpers/js/functions.js'

const url = getUrl('/AdmVacantes/php/AdmVacantes.php')

/**
 * Con el ID de la empresa registraremos nuevas vacantes.
 * Este ID debería guardarse en el navegador cuando se inicia sesión.
 */
const idEmpresa = localStorage.hasOwnProperty('user_info')
    ? JSON.parse(localStorage.getItem('user_info')).IdEmpresa
    : null

/**
 * ID de la vacante que obtenemos de la URL (método GET).
 */
const idVacante = new URL(location.href).searchParams.get('v')

const vacante = {
    Nombre: '',
    InformacionVacante: '',
    FechaSolicitud: '',
    IdCarrera: '',
    Habilidades: '',
    Funciones: '',
    Genero: '',
    NumeroVacantes: '',
    Experiencia: '',
    DiasHorarioLaboral: '',
    Prestaciones: '',
    OfertaEconomica: '',
    CondicionLaboral: '',
    Capacitacion: '',
    PrestacionesLey: '',
    IdEmpresa: '',
}

const charValues = { 0: 'No', 1: 'Sí' }

/**
 * Hace la petición al PHP para obtener la información de la vacante
 * con el ID dado en la URL. Llena el objeto local `vacante` con la información
 * recibida.
 */
function getVacante() {
    $.get(url, { case: 'obtenerVacante', idVacante, idEmpresa })
        .done(function (res) {
            const respuesta = JSON.parse(res)

            /**
             * Llenar el objeto `vacante` con los datos recibidos en la petición.
             */
            const keys = Object.keys(respuesta.data)
            keys.forEach((key) => (vacante[key] = respuesta.data[key]))

            printInfo()
        })
        .fail(function (err) {
            const error = JSON.parse(err.responseText)
            alert(error.mensaje)
            location.href = './'
        })
}

/**
 * Plasma toda la información en pantalla.
 */
function printInfo() {
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
                elemento.text(value)
            }
        }
    })
}

$(document).ready(() => {
    $.get(url, { case: 'validarRegistro', idEmpresa }).done(function (res) {
        const response = JSON.parse(res)
        if (!response.validado) {
            location.href = '../AdmRepresentante'
        }
    })

    getVacante()
})
