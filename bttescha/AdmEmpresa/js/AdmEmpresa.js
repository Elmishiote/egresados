import { getUrl, crearAlerta } from '../../helpers/js/functions.js'

let empresa = {
    IdEmpresa: '',
    Nombre: '',
    RazonSocial: '',
    Correo: '',
    TelefonoEmpresa: '',
    Domicilio: '',
    IdColonia: '',
    NombreColonia: '',
    IdMunicipio: '',
}

// Construir la URL completa para hacer peticiones
const url = getUrl('/AdmEmpresa/php/AdmEmpresa.php')

/**
 * Genera la tabla en el HTML con la información de las empresas.
 * @param {object} respuesta Respuesta de las empresas.
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

    respuesta.data.forEach((empresa, index) => {
        const { IdEmpresa, Nombre, Correo, Direccion } = empresa

        template += `
            <tr data-id="${IdEmpresa}">
                <th scope="row">${index + 1}</th>
                <td>${Nombre}</td>
                <td>${Correo}</td>
                <td>${Direccion}</td>
                <td>
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
 * Lista todas las empresas.
 * @param {object} resultadosBusqueda Resultados de búsqueda realizados por el usuario.
 */
function listarEmpresas(resultadosBusqueda = {}) {
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
    $.get(url, { case: 'listarEmpresas' })
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
 * Llena el objeto local "empresa" con los valores insertados por el
 * usuario.
 */
function sincronizarObjLocal() {
    $('#nombreEmpresa').on('keyup', (e) => (empresa.Nombre = e.target.value))
    $('#razonSocial').on('keyup', (e) => (empresa.RazonSocial = e.target.value))
    $('#emailEmpresa').on('keyup', (e) => (empresa.Correo = e.target.value))
    $('#telefonoEmpresa').on(
        'keyup',
        (e) => (empresa.TelefonoEmpresa = e.target.value)
    )
    $('#calleEmpresa').on('keyup', (e) => (empresa.Domicilio = e.target.value))
}

/**
 * Reestablece el título del modal y todos los inputs del formulario de registro.
 */
function reestablecerModal() {
    $('#modalLabel').text('Registrar nueva empresa')
    $('#nombreEmpresa').val('')
    $('#razonSocial').val('')
    $('#emailEmpresa').val('')
    $('#telefonoEmpresa').val('')
    $('#calleEmpresa').val('')
    $('#numDomEmpresa').val('')
    $('#coloniaEmpresa').val('')
    $('#municipioEmpresa').val('')
}

/**
 * Llena los input del formulario si estamos editando un registro.
 * Verifica que el objeto local "empresa" tenga un ID.
 */
function llenarFormModal() {
    if (empresa.IdEmpresa) {
        $('#modalLabel').text('Editar empresa')
        $('#nombreEmpresa').val(empresa.Nombre)
        $('#razonSocial').val(empresa.RazonSocial)
        $('#emailEmpresa').val(empresa.Correo)
        $('#telefonoEmpresa').val(empresa.TelefonoEmpresa)
        $('#calleEmpresa').val(empresa.Domicilio)
        $('#coloniaEmpresa').val(empresa.NombreColonia)
        $('#municipioEmpresa').val(empresa.NombreMunicipio)
    } else {
        reestablecerModal()
    }
}

/**
 * Listar convenios cuando se cargue la página.
 */
$(document).ready(() => listarEmpresas())

/**
 * Autocompleta el input de colonia.
 */
$('#coloniaEmpresa').on('keyup', function (e) {
    const colonia = e.target.value
    $('#divColonia ul').remove()

    if (colonia) {
        $.get(url, { case: 'listarColonias', colonia })
            .done(function (data) {
                const respuesta = JSON.parse(data)

                const ul = document.createElement('UL')
                ul.className = 'z-3 position-absolute list-group shadow w-100'

                respuesta.data.forEach((direccion) => {
                    const {
                        IdColonia,
                        NombreColonia,
                        IdMunicipio,
                        NombreMunicipio,
                        NombreEstado,
                    } = direccion

                    const li = document.createElement('LI')
                    li.className = 'list-group-item list-group-item-action'
                    li.role = 'button'
                    li.textContent = `${NombreColonia}, ${NombreMunicipio}, ${NombreEstado}`
                    li.onclick = () => {
                        $('#divColonia ul').remove()

                        empresa.IdColonia = IdColonia
                        empresa.IdMunicipio = IdMunicipio

                        $('#coloniaEmpresa').val(NombreColonia)
                        $('#municipioEmpresa').val(
                            `${NombreMunicipio}, ${NombreEstado}`
                        )
                    }

                    ul.appendChild(li)
                })

                $('#divColonia ul').remove()
                $('#divColonia').append(ul)
            })
            .fail(function (err) {
                const error = JSON.parse(err.responseText)

                $('#municipioEmpresa').val('')
                empresa.IdColonia = ''
                empresa.NombreColonia = $('#coloniaEmpresa').val()
                empresa.IdMunicipio = ''
            })
    } else {
        $('#divColonia ul').remove()
        empresa.IdColonia = ''
        empresa.IdMunicipio = ''
        $('#coloniaEmpresa').val('')
        $('#municipioEmpresa').val('')
    }
})

/**
 * Autocompleta el input de municipio y estado.
 */
$('#municipioEmpresa').on('keyup', function (e) {
    const municipio = e.target.value
    $('#divMunicipio ul').remove()

    if (municipio) {
        $.get(url, { case: 'listarMunicipios', municipio }, function (data) {
            const respuesta = JSON.parse(data)

            const ul = document.createElement('UL')
            ul.className = 'z-3 position-absolute list-group shadow w-100'

            respuesta.data.forEach((municipio) => {
                const { IdMunicipio, NombreMunicipio, NombreEstado } = municipio

                const li = document.createElement('LI')
                li.className = 'list-group-item list-group-item-action'
                li.role = 'button'
                li.textContent = `${NombreMunicipio}, ${NombreEstado}`
                li.onclick = () => {
                    $('#divMunicipio ul').remove()
                    empresa.IdMunicipio = IdMunicipio
                    $('#municipioEmpresa').val(
                        `${NombreMunicipio}, ${NombreEstado}`
                    )
                }

                ul.appendChild(li)
            })

            $('#divMunicipio ul').remove()
            $('#divMunicipio').append(ul)
        })
    } else {
        $('#divMunicipio ul').remove()
        empresa.IdMunicipio = ''
        $('#municipioEmpresa').val('')
    }
})

/**
 * Sincronizar el objeto local cuando el modal este abierto.
 * Con "shown.bs.modal" escuchamos por el evento cuando el modal se abre.
 */
$('#formModal').on('shown.bs.modal', function () {
    llenarFormModal()
    sincronizarObjLocal()
})

/**
 * Reestablecer el objeto local cuando el modal se cierra.
 * Con "hidden.bs.modal" escuchamos por el evento cuando el modal se cierra.
 */
$('#formModal').on('hidden.bs.modal', function () {
    empresa.IdEmpresa = ''
    empresa.Nombre = ''
    empresa.RazonSocial = ''
    empresa.Correo = ''
    empresa.TelefonoEmpresa = ''
    empresa.Domicilio = ''
    empresa.IdColonia = ''
    empresa.IdMunicipio = ''
    empresa.Domicilio = ''
    reestablecerModal()
})

$('#formEmpresa').submit(function (e) {
    e.preventDefault()

    /**
     * Validar que no existan campos vacíos.
     */
    const camposVacios = Object.keys(empresa).map((key) => {
        if (key !== 'IdEmpresa' || key !== 'IdColonia') {
            empresa[key] === '' ? true : false
        }
    })

    if (camposVacios.includes(true)) {
        const alerta = crearAlerta(
            'Todos los campos son obligatorios',
            'alert-danger'
        )
        $('#formEmpresa').before(alerta)
        // Eliminar alerta después de 3s
        setTimeout(() => $('.alert').remove(), 3000)
        return
    }

    const data = {
        case: empresa.IdEmpresa ? 'editarEmpresa' : 'registrarEmpresa',
        data: { ...empresa },
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
            listarEmpresas()
        })
        .fail(function (err) {
            const error = JSON.parse(err.responseText)

            const alerta = crearAlerta(error.mensaje, 'alert-danger')
            $('#formEmpresa').before(alerta)
            // Eliminar alerta después de 3s
            setTimeout(() => $('.alert').remove(), 3000)
        })
})

/**
 * Editar información de la empresa.
 * Consulta a la API para llanar el objeto local `empresa` y así
 * llene automáticamente el formulario.
 */
$(document).on('click', '.btnEditar', function () {
    // Obtener el ID del convenio
    const idEmpresa = $(this).parent().parent().data('id')

    $.get(url, { case: 'obtenerEmpresa', idEmpresa })
        .done(function (res) {
            const respuesta = JSON.parse(res)
            const { data } = respuesta

            /**
             * Asignar los valores al objeto local.
             */
            empresa.IdEmpresa = data.IdEmpresa
            empresa.Nombre = data.Nombre
            empresa.RazonSocial = data.RazonSocial
            empresa.Correo = data.Correo
            empresa.TelefonoEmpresa = data.TelefonoEmpresa
            empresa.Domicilio = data.Domicilio
            empresa.IdColonia = data.IdColonia
            empresa.NombreColonia = data.NombreColonia
            empresa.IdMunicipio = data.IdMunicipio
            empresa.NombreMunicipio =
                data.NombreMunicipio + ', ' + data.NombreEstado

            // Abrir modal
            $('#formModal').modal('show')
        })
        .fail(function (err) {
            const error = JSON.parse(err.responseText)
            alert(error.mensaje)
        })
})

/**
 * Desactivar la empresa.
 */
$(document).on('click', '.btnDesactivar', function () {
    // Obtener el ID del convenio
    const idEmpresa = $(this).parent().parent().data('id')
    const data = {
        case: 'desactivarEmpresa',
        data: { idEmpresa },
    }

    const confirmacion = confirm('¿Estás seguro de desactivar esta empresa?')

    if (confirmacion) {
        $.post(url, data)
            .done(function (res) {
                const response = JSON.parse(res)

                const alerta = crearAlerta(response.mensaje, 'alert-success')
                alerta.classList.add('mt-4')
                $('.row.my-4').append(alerta)

                // Eliminar alerta después de 3s
                setTimeout(() => $('.alert').remove(), 3000)

                // Actualizar listado
                listarEmpresas()
            })
            .fail(function (err) {
                const error = JSON.parse(err.responseText)

                const alerta = crearAlerta(error.mensaje, 'alert-danger')
                alerta.classList.add('mt-4')
                $('.row.my-4').append(alerta)
                // Eliminar alerta después de 3s
                setTimeout(() => $('.alert').remove(), 3000)
            })
    }
})

/**
 * Buscar empresas cuando se escriba en el input de búsqueda.
 */
$('#formBusqueda').submit((e) => e.preventDefault())
$('#inputBuscar').on('input', function (e) {
    const terminoBusqueda = e.target.value
    const data = {
        case: 'buscarEmpresas',
        terminoBusqueda,
    }

    $.get(url, data)
        .done(function (res) {
            const response = JSON.parse(res)
            listarEmpresas(response)
        })
        .fail(function (err) {
            const error = JSON.parse(err.responseText)
            listarEmpresas(error)
        })
})
