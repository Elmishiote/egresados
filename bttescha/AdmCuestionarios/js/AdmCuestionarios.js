import { getUrl, crearAlerta } from '../../helpers/js/functions.js';

const API_URL = getUrl('/AdmCuestionarios/php/AdmCuestionarios.php');
const form = $('#formCuestionarioAcceso');
const alertaDiv = $('#alerta-cuestionario');
const submitButton = form.children('button[type="submit"]');

/**
 * Muestra una alerta temporal en el div #alerta-cuestionario.
 * @param {string} mensaje
 * @param {string} clase
 */
function mostrarAlerta(mensaje, clase) {
    alertaDiv.empty();
    const alerta = crearAlerta(mensaje, clase);
    alerta.classList.add('mt-4');
    alertaDiv.append(alerta);
    // Eliminar alerta después de 5s
    setTimeout(() => alertaDiv.empty(), 5000); 
}

form.submit(function (e) {
    e.preventDefault();
    submitButton.prop('disabled', true).text('Verificando...');

    const matricula = $('#matricula').val().trim();
    const tipoCuestionario = $('#tipoCuestionario').val();

    if (!matricula || !tipoCuestionario) {
        mostrarAlerta('Todos los campos son obligatorios.', 'alert-danger');
        submitButton.prop('disabled', false).text('Verificar y Acceder');
        return;
    }

    const data = {
        case: 'validarAcceso',
        matricula: matricula,
        tipo: tipoCuestionario,
    };

    $.post(API_URL, data)
        .done(function (res) {
            const respuesta = JSON.parse(res);
            mostrarAlerta(respuesta.mensaje, 'alert-success');
            
            // Redirección al cuestionario específico
            // Creamos un URL de redirección dinámico (ej: cuestionario-1.php)
            setTimeout(() => {
                // Asumo que tienes páginas específicas para cada cuestionario
                location.href = `./cuestionario-${respuesta.cuestionario}.php?id=${respuesta.id_estudiante}`;
            }, 1000); 

        })
        .fail(function (err) {
            const error = JSON.parse(err.responseText);
            mostrarAlerta(error.mensaje, 'alert-danger');
        })
        .always(function() {
            submitButton.prop('disabled', false).text('Verificar y Acceder');
        });
});