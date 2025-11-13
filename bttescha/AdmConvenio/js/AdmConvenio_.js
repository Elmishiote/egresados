/**
 * VERISÓN ANTERIOR.
 * Está versión funciona en el archivo "index_.html".
 * Funciona correctamente, a excepción de la actualización de datos, ya que
 * se hace la petición dos veces y actualiza dos registros a la vez.
 */

/**
 * Abrir y cerrar sidebar
 */
$(function(){
    //Ejecutar función en el evento click
document.getElementById("btn_open").addEventListener("click", open_close_menu);

//Declaramos variables
var side_menu = document.getElementById("menu_side");
var btn_open = document.getElementById("btn_open");
var body = document.getElementById("body");

//Evento para mostrar y ocultar menú
    function open_close_menu(){
        body.classList.toggle("body_move");
        side_menu.classList.toggle("menu__side_move");
    }

//Si el ancho de la página es menor a 760px, ocultará el menú al recargar la página

if (window.innerWidth < 760){

    body.classList.add("body_move");
    side_menu.classList.add("menu__side_move");
}

//Haciendo el menú responsive(adaptable)

window.addEventListener("resize", function(){

    if (window.innerWidth > 760){

        body.classList.remove("body_move");
        side_menu.classList.remove("menu__side_move");
    }

    if (window.innerWidth < 760){

        body.classList.add("body_move");
        side_menu.classList.add("menu__side_move");
    }

});
})

let editar = false;

/**
 * Llenar las opciones de empresas del formulario
 */
$(document).ready(function() {
    $.get('php/AdmConvenio.php', { case: 'listarEmpresas' }, function(data) {
        const respuesta = JSON.parse(data);
    
        if(!respuesta.error) {
            const { empresas } = respuesta;
            let optionTag = '';
    
            empresas.forEach(empresa => {
                optionTag += `
                    <option value="${ empresa.idEmpresa }">${ empresa.nombre }</option>
                `;
            });
    
            $('#empresa').append(optionTag)
        }
    })
})

/**
 * Registrar convenio
 */
$('#registrarConvenio').submit( function(e) {
    e.preventDefault();

    if(editar) {
        return;
    }

    const numConvenio = $('#numConvenio').val();
    const idEmpresa = $('#empresa').val();
    const data = {
        case: 'registrarConvenio',
        data: {
            numConvenio, idEmpresa
        }
    }

    $.post('php/AdmConvenio.php', data, function(data) {
        respuesta = JSON.parse(data)
        console.log( respuesta )
        
        // TODO: Mostrar alerta de registro exitoso
        
        // Resetear formulario
        if(!respuesta.error) {
            $('#registrarConvenio').trigger('reset')
            listarConvenios();
        }
    })

} )

/**
 * Mostrar todos los convenios
 */
$(document).ready( listarConvenios() )

/**
 * Lista todos los convenios haciendo una petición para obtener
 * los registros.
 */
function listarConvenios() {
    const tbody = $('tbody');
    tbody.empty(); // Eliminar contenido

    $.get('php/AdmConvenio.php', { case: 'listarConvenios' }, function(data) {
        const respuesta = JSON.parse(data);
        let template = '';

        if(respuesta.error) {
            template += `
                <tr>
                    <td colspan="3">${ respuesta.mensaje }</td>
                </tr>
            `;

            tbody.append(template);
            return;
        }

        respuesta.data.forEach(convenio => {
            const { IdConvenio, NumeroConvenio, nombreEmpresa } = convenio;

            template += `
                <tr data-id="${ IdConvenio }">
                    <td>${ NumeroConvenio }</td>
                    <td>${ nombreEmpresa }</td>
                    <td>
                        <button class="btnEditar">Editar</button>
                        <button class="btnEliminar">Eliminar</button>
                        <button>Desactivar</button>
                    </td>
                </tr>
            `;
        })

        tbody.append(template);
    })
}

/**
 * Eliminar un registro (actualiza el campo "Borrado")
 */
$(document).on('click', '.btnEliminar', function() {
    // Obtener el ID del convenio
    const idConvenio = $(this).parent().parent().data('id');
    const data = { case: 'eliminarConvenio', data: { idConvenio } }

    const confirmacion = confirm('¿Seguro que quieres eliminar este convenio?')

    if(confirmacion) {
        $.post('php/AdmConvenio.php', data, function(data) {
            const response = JSON.parse(data);
            
            if(response.error) {
                return;
            }

            $('tr[data-id="' + idConvenio + '"]').remove(); // Eliminar solo el tr eliminado
            // listarConvenios(); // Actualizar toda la tabla

            // TODO: Mostrar alerta de exito
        })
    }
})

/**
 * Editar un registro
 */
$(document).on('click', '.btnEditar', function() {
    // Obtener el ID del convenio
    const idConvenio = $(this).parent().parent().data('id');
    editar = true;
    $('#registrarConvenio').data('edit-mode', 'true');

    llenarFormulario(idConvenio)

    if(editar) {
        $('#registrarConvenio').submit( function(e) {
            
            if ($(this).data('edit-mode') === "true") {
            e.preventDefault();
        
            const numConvenio = $('#numConvenio').val();
            const idEmpresa = $('#empresa').val();
            const data = {
                case: 'editarConvenio',
                data: {
                    idConvenio, numConvenio, idEmpresa
                }
            }

            $.post('php/AdmConvenio.php', data, function(data) {
                console.log( JSON.parse(data) )
                
                // TODO: Mostrar alerta de edición exitoso
                
                // Resetear formulario
                listarConvenios();
                editar = false;
                $('#registrarConvenio').trigger('reset')
                $('#btnSubmit').val('Registrar');
            })
            }
        } )
    }
})

function llenarFormulario(idConvenio) {
    const inputNumConvenio = $('#numConvenio');
    const inputEmpresa = $('#empresa');
    const btnSubmit = $('#btnSubmit');

    $.get('php/AdmConvenio.php', { case: 'obtenerConvenio', data: { idConvenio } }, function(data) {
        const respuesta = JSON.parse(data);
        
        inputNumConvenio.val( respuesta.data.NumeroConvenio );
        inputEmpresa.val( respuesta.data.IdEmpresa )
        btnSubmit.val('Guardar')
    })
}