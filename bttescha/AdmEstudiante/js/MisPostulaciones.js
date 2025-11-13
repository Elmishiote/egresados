import { getUrl, formatearFecha } from '../../helpers/js/functions.js'

const ID_ESTUDIANTE = localStorage.hasOwnProperty('user_info')
    ? JSON.parse(localStorage.getItem('user_info')).IdEstudiante
    : null

const API_URL = getUrl('/AdmEstudiante/php/Vacantes.php')

/**
 * Genera la tabla en el HTML con la información de las postulaciones.
 * @param {object} respuesta Respuesta de las vacantes.
 */
function generarTabla(respuesta) {
    const tbody = $('tbody')
    tbody.empty() // Eliminar contenido
    let template = ''

    if (!respuesta.ok) {
        template += `
            <tr>
                <td colspan="6" class="text-center text-secondary py-4">${respuesta.mensaje}</td>
            </tr>
        `

        tbody.append(template)
        return
    }

    respuesta.data.forEach((vacante, index) => {
        const {
            IdPostulacion,
            IdVacante,
            NombreVacante,
            NombreEmpresa,
            FechaPostulacion,
            estatus,
            NombreEstatus,
        } = vacante
        const fechaFormateada = formatearFecha(FechaPostulacion)

        template += `
            <tr data-id="${IdPostulacion}">
                <th scope="row">${index + 1}</th>
                <td>${NombreVacante}</td>
                <td>${NombreEmpresa}</td>
                <td>${fechaFormateada}</td>
                <td>
                    ${
                        estatus == 3
                            ? `<span class="badge text-bg-danger">${NombreEstatus}</span>`
                            : estatus == 2
                            ? `<span class="badge text-bg-secondary">${NombreEstatus}</span>`
                            : `<span class="badge text-bg-warning">${NombreEstatus}</span>`
                    }
                </td>
                <td class="">
                    <a role="button" href="./vacante.php?v=${IdVacante}" target="_blank" class="btn btn-outline-dark btnVer" title="Ver vacante">
                        <i class="fa-solid fa-eye"></i>
                        Ver vacante
                    </a>
                </td>
            </tr>
        `
    })
    tbody.append(template)
}

/**
 * Lista todas las postulaciones.
 * @param {object} resultadosBusqueda Resultados de búsqueda realizados por el usuario.
 */
function listarPostulaciones(resultadosBusqueda = {}) {
    /**
     * Si existen resultados de búsqueda, genera la tabla en el HTML con
     * esos resultados.
     */
    if (Object.keys(resultadosBusqueda).length) {
        generarTabla(resultadosBusqueda)
        return
    }

    /**
     * Si no se está haciendo una búsqueda, crea la petición para obtener todos
     * los registros.
     */
    $.get(API_URL, { case: 'misPostulaciones', idEstudiante: ID_ESTUDIANTE })
        .done(function (res) {
            const respuesta = JSON.parse(res)
            generarTabla(respuesta)
        })
        .fail(function (err) {
            const errRes = JSON.parse(err.responseText)
            generarTabla(errRes)
        })
}

$(document).ready(function () {
    listarPostulaciones()
})
