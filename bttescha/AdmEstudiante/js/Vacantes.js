import { getUrl } from '../../helpers/js/functions.js'

/**
 * ID de la carrera a la que pertenece el estudiante.
 * Esta debe ser almacenada internamente en el navegador cuando se inicie
 * sesión. Es necesaria para listar las vacantes de acuerdo a carreras.
 */
const ID_CARRERA = localStorage.hasOwnProperty('user_info')
    ? JSON.parse(localStorage.getItem('user_info')).IdCarrera
    : null

const API_URL = getUrl('/AdmEstudiante/php/Vacantes.php')

/**
 * Imprime las vacantes publicada o en su defecto, un mensaje de error.
 *
 * @param {Object}  res             Respuesta de la API.
 * @param {boolean} res.ok          Indica si hubo un error.
 * @param {array}   [res.data]      Información de la base de datos.
 * @param {string}  [res.mensaje]   Mensaje de error.
 */
function generarResultados(res) {
    const contenedorVacantes = $('#lista-vacantes')
    contenedorVacantes.empty()
    let template = ''

    if (!res.ok) {
        template += `
            <div class="py-5">
                <p class="text-center text-secondary">
                    ${res.mensaje}
                </p>
            </div>
        `
        contenedorVacantes.append(template)
        return
    }

    res.data.forEach((vacante) => {
        const { IdVacante, NombreVacante, NombreEmpresa, InformacionVacante } =
            vacante
        /**
         * Almacena la descripción de la vacante a solo 100 caracteres.
         */
        const descripcionCorta = InformacionVacante.slice(0, 100) + '...'

        template += `
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">${NombreVacante}</h5>
                    <h6 class="card-subtitle mb-2 text-body-secondary">
                        ${NombreEmpresa}
                    </h6>
                    <p class="card-text">${descripcionCorta}</p>
                    <a href="./vacante.php?v=${IdVacante}" class="btn btn-primary">Ver vacante</a>
                </div>
            </div>
        `
    })
    contenedorVacantes.html(template).show()
}

$(document).ready(function () {
    $.get(API_URL, { case: 'vacantesEstudiante', idCarrera: ID_CARRERA })
        .done(function (res) {
            const respuesta = JSON.parse(res)
            generarResultados(respuesta)
        })
        .fail(function (err) {
            const errRes = JSON.parse(err.responseText)
            generarResultados(errRes)
        })
})

$('#buscar-vacante').submit(function (e) {
    e.preventDefault()

    const terminoBusqueda = $('#input-buscar').val().trim()
    if (!terminoBusqueda) return

    const data = {
        case: 'buscarVacantes',
        idCarrera: ID_CARRERA,
        terminoBusqueda,
    }

    $('#titulo-vacantes').text(
        `Resultados de búsqueda para: "${terminoBusqueda}"`
    )

    $.get(API_URL, data)
        .done(function (res) {
            const respuesta = JSON.parse(res)
            generarResultados(respuesta)
        })
        .fail(function (err) {
            const errRes = JSON.parse(err.responseText)
            generarResultados(errRes)
        })
})
