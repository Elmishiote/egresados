import { getUrl, crearAlerta } from '../../helpers/js/functions.js'

const convenio = {
    idConvenio: '',
    numConvenio: '',
    idEmpresa: '',
}

// Construir la URL completa para hacer peticiones
const url = getUrl('/AdmConvenio/php/AdmConvenio.php')

/**
 * Genera la tabla en el HTML con la información de los convenios.
 * @param {object} respuesta Respuesta de los convenios.
 */
function generarTabla(respuesta) {
    const tbody = $('tbody')
    tbody.empty() // Eliminar contenido
    let template = ''

    if (!respuesta.ok) {
        template += `
            <tr>
                <td colspan="4" class="text-center text-secondary py-4">${respuesta.mensaje}</td>
            </tr>
        `

        tbody.append(template)
        return
    }

    respuesta.data.forEach((convenio, index) => {
        const { IdConvenio, NumeroConvenio, nombreEmpresa } = convenio

        template += `
            <tr data-id="${IdConvenio}">
                <th scope="row">${index + 1}</th>
                <td>${NumeroConvenio}</td>
                <td>${nombreEmpresa}</td>
                <td class="w-25">
                    <button class="btn btn-secondary btnEditar" title="Editar">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button class="btn btn-warning btnDesactivar" title="Desactivar">
                        <i class="fa-solid fa-power-off"></i>
                    </button>
                </td>
            </tr>
        `
    })

    tbody.append(template)
}

/**
 * Lista todos los convenios.
 * @param {object} resultadosBusqueda Resultados de búsqueda realizados por el usuario.
 */
function listarConvenios(resultadosBusqueda = {}) {
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
    $.get(url, { case: 'listarConvenios' })
        .done(function (data) {
            const respuesta = JSON.parse(data)
            generarTabla(respuesta)
        })
        .fail(function (err) {
            const respuesta = JSON.parse(err.responseText)
            generarTabla(respuesta)
        })
}

/**
 * Reestablece el título del modal y todos los inputs del formulario de registro.
 */
function reestablecerModal() {
    $('#modalLabel').text('Registrar nuevo convenio')
    $('#numConvenio').val('')
    $('#empresa').val('')
}

/**
 * Llena los input del formulario si estamos editando un registro.
 * Verifica que el objeto local "convenio" tenga un ID.
 */
function llenarFormModal() {
    if (convenio.idConvenio) {
        $('#modalLabel').text('Editar convenio')
        $('#numConvenio').val(convenio.numConvenio)
        $('#empresa').val(convenio.idEmpresa)
    } else {
        reestablecerModal()
    }
}

/**
 * Llena el objeto local "convenio" con los valores insertados por el
 * usuario el input y select del formulario.
 */
function sincronizarObjLocal() {
    $('#numConvenio').on('keyup', function (e) {
        convenio.numConvenio = e.target.value
    })

    $('#empresa').on('change', function (e) {
        convenio.idEmpresa = e.target.value
    })
}

/**
 * Listar convenios cuando se cargue la página.
 */
$(document).ready(() => listarConvenios())

/**
 * Llenar el campo "select" del formulario cuando este se abre.
 * Con "shown.bs.modal" escuchamos por el evento cuando el modal se abre.
 */
$('#formModal').on('shown.bs.modal', function () {
    $('#empresa').empty()
    const optionSeleccionar = '<option value="">Seleccionar</value>'
    $('#empresa').append(optionSeleccionar)

    $.get(url, { case: 'listarEmpresas' })
        .done(function (data) {
            const respuesta = JSON.parse(data)

            const { data: empresas } = respuesta
            let optionTag = ''

            empresas.forEach((empresa) => {
                optionTag += `
                    <option value="${empresa.idEmpresa}">${empresa.nombre}</option>
                `
            })

            $('#empresa').append(optionTag)

            // Llenar los input si estamos editando
            llenarFormModal()
        })
        .fail(function (err) {
            console.log(JSON.parse(err.responseText))
        })

    sincronizarObjLocal()
})

/**
 * Reestablecer el objeto local cuando el modal se cierra.
 * Con "hidden.bs.modal" escuchamos por el evento cuando el modal se cierra.
 */
$('#formModal').on('hidden.bs.modal', function () {
    convenio.idConvenio = ''
    convenio.numConvenio = ''
    convenio.idEmpresa = ''
    reestablecerModal()
})

/**
 * Escucha por el evento "submit" del formulario. Si existe un ID en el
 * objeto local "convenio", hace una edición, de lo contrario, registra el nuevo convenio,
 */
$('#formConvenio').submit(function (e) {
    e.preventDefault()

    const data = {
        case: convenio.idConvenio ? 'editarConvenio' : 'registrarConvenio',
        data: { ...convenio },
    }

    /**
     * Validar que no existan campos vacíos.
     */
    const camposVacios = Object.keys(convenio).map((key) =>
        convenio[key] === '' && key !== 'idConvenio' ? true : false
    )
    if (camposVacios.includes(true)) {
        const alerta = crearAlerta(
            'Todos los campos son obligatorios',
            'alert-danger'
        )
        $('#formConvenio').before(alerta)
        // Eliminar alerta después de 3s
        setTimeout(() => $('.alert').remove(), 3000)
        return
    }

    $.post(url, data)
        .done(function (data) {
            const respuesta = JSON.parse(data)

            $('#formConvenio').trigger('reset') // Resetear formulario
            $('#formModal').modal('hide') // Cerra modal

            // Crear alerta de éxito
            const alerta = crearAlerta(respuesta.mensaje, 'alert-success')
            alerta.classList.add('mt-4')
            $('.row.my-4').append(alerta)

            // Eliminar alerta después de 3s
            setTimeout(() => $('.alert').remove(), 3000)

            // Actualizar listado
            listarConvenios()
        })
        .fail(function (resErr) {
            const error = JSON.parse(resErr.responseText)

            const alerta = crearAlerta(error.mensaje, 'alert-danger')
            $('#formConvenio').before(alerta)
            // Eliminar alerta después de 3s
            setTimeout(() => $('.alert').remove(), 3000)
        })
})

/**
 * Abrir modal cuando se da clic en el botón de editar.
 * Asigna los valores al objeto local "convenio".
 */
$(document).on('click', '.btnEditar', function () {
    // Obtener el ID del convenio
    const idConvenio = $(this).parent().parent().data('id')

    $.get(url, { case: 'obtenerConvenio', idConvenio })
        .done(function (data) {
            const respuesta = JSON.parse(data)
            const { IdConvenio, NumeroConvenio, IdEmpresa } = respuesta.data

            // Asignar los valores al objeto local
            convenio.idConvenio = IdConvenio
            convenio.numConvenio = NumeroConvenio
            convenio.idEmpresa = IdEmpresa

            // Abrir modal
            $('#formModal').modal('show')
        })
        .fail(function (err) {
            const error = JSON.parse(err.responseText)
            alert(error.mensaje)
        })
})

/**
 * Eliminar un registro.
 * Actualiza el campo "Borrado" en el registro seleccionado.
 */
$(document).on('click', '.btnDesactivar', function () {
    // Obtener el ID del convenio
    const idConvenio = $(this).parent().parent().data('id')
    const data = {
        case: 'eliminarConvenio',
        data: { idConvenio },
    }
    const confirmacion = confirm('¿Estás seguro de desactivar este convenio?')

    if (confirmacion) {
        $.post(url, data)
            .done(function (data) {
                const response = JSON.parse(data)

                const alerta = crearAlerta(response.mensaje, 'alert-success')
                alerta.classList.add('mt-4')
                $('.row.my-4').append(alerta)

                // Eliminar alerta después de 3s
                setTimeout(() => $('.alert').remove(), 3000)

                // Actualizar listado de convenios
                listarConvenios()
            })
            .fail(function (errRes) {
                const error = JSON.parse(errRes.responseText)

                const alerta = crearAlerta(error.mensaje, 'alert-danger')
                alerta.classList.add('mt-4')
                $('.row.my-4').append(alerta)
                // Eliminar alerta después de 3s
                setTimeout(() => $('.alert').remove(), 3000)
            })
    }
})

/**
 * Obtiene el texto ingresado por el usuario en el cuadro de búsqueda.
 */
$('#formBusqueda').submit((e) => e.preventDefault())
$('#inputBuscar').on('input', function (e) {
    const terminoBusqueda = e.target.value
    const data = {
        case: 'buscarConvenios',
        terminoBusqueda,
    }

    $.get(url, data)
        .done(function (data) {
            const respuesta = JSON.parse(data)
            listarConvenios(respuesta)
        })
        .fail(function (err) {
            const error = JSON.parse(err.responseText)
            listarConvenios(error)
        })
})
