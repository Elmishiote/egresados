const idEmpresa = localStorage.hasOwnProperty('user_info')
    ? JSON.parse(localStorage.getItem('user_info')).IdEmpresa
    : null

import { getUrl, formatearFecha } from '../../helpers/js/functions.js';

const API_URL = getUrl('/AdmVacantes/php/AdmVacantes.php');

/**
 * ID de la vacante que obtenemos de la URL (método GET).
 */
const idVacante = new URL(location.href).searchParams.get('p');

function listarPostulaciones(data = {}) {
    const { vacante } = data.data;

    $('.card-title').text(vacante.Nombre);
    $('#fechaPublicacion').text(formatearFecha(vacante.FechaPublicacion));
    $('#btnVerVacante').attr('href', 'vacante.php?v=' + vacante.IdVacante);
    $('#carrera').text(vacante.NombreCarrera);
    let template = '';
    const tabla = $('#postulantes tbody');
    tabla.empty();

    if (!data.ok) {
        template += `
            <tr>
                <td colspan="6" class="text-center text-secondary py-4">${data.data.mensaje}</td>
            </tr>
        `;

        tabla.append(template);
        return;
    }

    data.data.postulaciones.forEach((postulacion, index) => {
        template += `
            <tr data-id="${postulacion.IdPostulacion}">
                <th scope="row">${index + 1}</th>
                <td>${postulacion.NombreEstudiante}</td>
                <td>${formatearFecha(postulacion.FechaPostulacion)}</td>
                <td>
                    <a role="button" href="${
                        getUrl() + postulacion.cv
                    }" target="_blank" class="btn btn-primary" title="Ver CV">
                        <i class="fa-solid fa-file me-1"></i>
                        Ver CV
                    </a>
                </td>
                <td>
                    ${
                        postulacion.estatus == 3
                            ? `<span class="badge text-bg-danger">${postulacion.EstatusNombre}</span>`
                            : postulacion.estatus == 2
                            ? `<span class="badge text-bg-secondary">${postulacion.EstatusNombre}</span>`
                            : `<span class="badge text-bg-warning">${postulacion.EstatusNombre}</span>`
                    }
                </td>
                <td>
                    <select class="form-select cambiar-estatus" name="estado">
                        <option selected value="">Seleccionar</option>
                        <option value="2">En espera</option>
                        <option value="3">Rechazar</option>
                    </select>
                </td>
            </tr>
        `;
    });

    tabla.append(template);
}

function consultarApi() {
    $.get(API_URL, { case: 'listarPostulaciones', idVacante })
        .done(function (res) {
            const response = JSON.parse(res);
            listarPostulaciones(response);
        })
        .fail(function (err) {
            const errResponse = JSON.parse(err.responseText);
            listarPostulaciones(errResponse);
        });
}

$(document).ready(() => {
    $.get(API_URL, { case: 'validarRegistro', idEmpresa }).done(function (res) {
        const response = JSON.parse(res);
        if (!response.validado) {
            location.href = '../AdmRepresentante';
        }
    });

    consultarApi();
});

/**
 * Cambia el estatus de la postulación.
 */
$(document).on('change', '.cambiar-estatus', function (e) {
    const idPostulacion = $(this).parent().parent().data('id');
    const idEstatus = e.target.value;

    const data = {
        case: 'editarEstatus',
        idPostulacion,
        idEstatus,
    };

    $.post(API_URL, data)
        .done(function (res) {
            const response = JSON.parse(res);
            consultarApi();
        })
        .fail(function (err) {
            const errorResponse = JSON.parse(err.responseText);
        });
});
