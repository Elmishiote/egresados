import {
    getUrl,
    crearAlerta,
    formatearFecha,
} from '../../helpers/js/functions.js'

const url = getUrl('/AdmVacantes/php/AdmVacantes.php')

/**
 * Con el ID de la empresa registraremos nuevas vacantes.
 * Este ID debería guardarse en el navegador cuando se inicia sesión.
 */
const idEmpresa = localStorage.hasOwnProperty('user_info')
    ? JSON.parse(localStorage.getItem('user_info')).IdEmpresa
    : null

/**
 * Genera la tabla en el HTML con la información de las vacantes.
 * @param {object} respuesta Respuesta de las vacantes.
 */
function generarTabla(respuesta) {
    const tbody = $('tbody')
    tbody.empty() // Eliminar contenido
    let template = ''

    if (!respuesta.ok) {
        template += `
            <tr>
                <td colspan="5" class="text-center text-secondary py-4">${respuesta.mensaje}</td>
            </tr>
        `

        tbody.append(template)
        return
    }

    respuesta.data.forEach((vacante, index) => {
        const {
            id_vacante,
            nombre,
            carrera,
            num_postulaciones,
            fecha_publicacion,
        } = vacante
        const fechaFormateada = formatearFecha(fecha_publicacion)

        template += `
            <tr data-id="${id_vacante}">
                <th scope="row">${index + 1}</th>
                <td>${nombre}</td>
                <td>${carrera}</td>
                <td>
                    <a href="./postulaciones.php?p=${id_vacante}" role="button" class="btn btn-primary position-relative">
                        Ver
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            ${num_postulaciones}
                            <span class="visually-hidden">Postulaciones</span>
                        </span>
                    </a>
                </td>
                <td>${fechaFormateada}</td>
                <td class="">
                    <a role="button" href="./vacante.php?v=${id_vacante}" target="_blank" class="btn btn-primary btnVer" title="Ver vacante">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                    <a role="button" href="./editar-vacante.php?v=${id_vacante}" class="btn btn-secondary btnEditar" title="Editar">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <button class="btn btn-warning btnEliminar" title="Desactivar">
                        <i class="fa-solid fa-power-off"></i>
                    </button>
                </td>
            </tr>
        `
    })
    tbody.append(template)
}

/**
 * Lista todas las vacantes.
 * @param {object} resultadosBusqueda Resultados de búsqueda realizados por el usuario.
 */
function listarVacantes(resultadosBusqueda = {}) {
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
    $.get(url, { case: 'listarVacantes', idEmpresa })
        .done(function (data) {
            const respuesta = JSON.parse(data)
            generarTabla(respuesta)
        })
        .fail(function (err) {
            const respuesta = JSON.parse(err.responseText)
            generarTabla(respuesta)
        })
}

$(document).ready(() => {
    $.get(url, { case: 'validarRegistro', idEmpresa }).done(function (res) {
        const response = JSON.parse(res)
        if (!response.validado) {
            location.href = '../AdmRepresentante'
        }
    })

    listarVacantes()
})

/**
 * Eliminar una vacante dando clic en el botón Eliminar.
 */
$(document).on('click', '.btnEliminar', function () {
    // Obtener el ID de la vacante
    const idVacante = $(this).parent().parent().data('id')
    const data = {
        case: 'eliminarVacante',
        data: { idVacante },
    }

    const confirmacion = confirm('¿Estás seguro de desactivar esta vacante?')

    if (confirmacion) {
        $.post(url, data)
            .done(function (res) {
                const respuesta = JSON.parse(res)

                const alerta = crearAlerta(respuesta.mensaje, 'alert-success')
                alerta.classList.add('my-4')
                $('.table-responsive').before(alerta)

                // Eliminar alerta después de 3s
                setTimeout(() => $('.alert').remove(), 3000)

                // Actualizar listado de vacantes
                listarVacantes()
            })
            .fail(function (err) {
                const error = JSON.parse(err.responseText)

                const alerta = crearAlerta(error.mensaje, 'alert-danger')
                alerta.classList.add('my-4')
                $('.table-responsive').before(alerta)

                // Eliminar alerta después de 3s
                setTimeout(() => $('.alert').remove(), 3000)
            })
    }
})

/**
 * Evitar que se recargue la página cuando se de clic en el form.
 */
const form = $('#formBusqueda')
form.submit((e) => e.preventDefault())

/**
 * Buscar vacantes
 */
$('#inputBuscar').on('input', function (e) {
    const terminoBusqueda = e.target.value
    const data = {
        case: 'buscarVacantes',
        idEmpresa,
        terminoBusqueda,
    }

    $.get(url, data)
        .done(function (res) {
            const respuesta = JSON.parse(res)
            listarVacantes(respuesta)
        })
        .fail(function (err) {
            const error = JSON.parse(err.responseText)
            listarVacantes(error)
        })
})
