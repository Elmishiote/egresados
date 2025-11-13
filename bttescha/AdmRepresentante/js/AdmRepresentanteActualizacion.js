import { getUrl, crearAlerta } from '../../helpers/js/functions.js'

/**
 * Con el ID del empleador será posible encontrar la empresa a la que
 * pertenece dicho empleador para llenar el formulario.
 * Este ID debería guardarse en el navegador cuando se inicia sesión.
 */
const idEmpleador = localStorage.hasOwnProperty('user_info')
    ? JSON.parse(localStorage.getItem('user_info')).IdEmpleador
    : null

const empresa = {
    IdEmpresa: '',
    NombreEmpresa: '',
    Correo: '',
    RazonSocial: '',
    Domicilio: '',
    IdColonia: '',
    NombreColonia: '',
    IdMunicipio: '',
    NombreMunicipio: '',
    NombreEstado: '',
    TelefonoEmpresa: '',
}

// Construir la URL completa para hacer peticiones
const url = getUrl('/AdmRepresentante/php/AdmRepresentanteActualizacion.php')

/**
 * Llena todos los input del formulario con los valores guardados
 * en el objeto local `empresa`.
 */
function llenarForm() {
    $('#nombre').val(empresa.NombreEmpresa)
    $('#razonSocial').val(empresa.RazonSocial)
    $('#email').val(empresa.Correo)
    $('#telefono').val(empresa.TelefonoEmpresa)
    $('#calle').val(empresa.Domicilio)
    $('#colonia').val(empresa.NombreColonia)
    $('#municipio').val(`${empresa.NombreMunicipio}, ${empresa.NombreEstado}`)
}

/**
 * Sincroniza el valor introducido por el usuario en cada input del
 * formulario con la propiedad correspondiente en el objeto local `empresa`.
 */
function sincronizarInputObjLocal() {
    const mapeoCampos = {
        nombre: 'NombreEmpresa',
        razonSocial: 'RazonSocial',
        email: 'Correo',
        telefono: 'TelefonoEmpresa',
        calle: 'Domicilio',
        colonia: 'NombreColonia',
        municipio: 'NombreMunicipio',
    }

    $('input').keyup(function () {
        const idInput = $(this).attr('id')
        const valorInput = $(this).val()

        empresa[mapeoCampos[idInput]] = valorInput
    })
}

/**
 * Llenar el formulario cuando se cargue el contenido del DOM.
 */
$(document).ready(function () {
    $.get(url, { case: 'obtenerEmpresa', idEmpleador })
        .done(function (res) {
            const response = JSON.parse(res).data

            empresa.IdEmpresa = response.IdEmpresa
            empresa.NombreEmpresa = response.NombreEmpresa
            empresa.Correo = response.Correo
            empresa.RazonSocial = response.RazonSocial
            empresa.Domicilio = response.Domicilio
            empresa.IdColonia = response.IdColonia
            empresa.NombreColonia = response.NombreColonia
            empresa.IdMunicipio = response.IdMunicipio
            empresa.NombreMunicipio = response.NombreMunicipio
            empresa.NombreEstado = response.NombreEstado
            empresa.TelefonoEmpresa = response.TelefonoEmpresa

            llenarForm()
            sincronizarInputObjLocal()
        })
        .fail(function (error) {
            console.log(JSON.parse(error.responseText))
        })
})

/**
 * Lista las colonias registradas en la base de datos para la sugerencia
 * al usuario.
 */
$('#colonia').on('keyup', function (e) {
    const nombreColonia = e.target.value
    $('#divColonia ul').remove()

    if (nombreColonia) {
        $.get(url, { case: 'listarColonias', nombreColonia })
            .done(function (res) {
                const respuesta = JSON.parse(res)
                const colonias = respuesta.data

                /**
                 * Crear listado debajo del input del formulario.
                 */
                const ul = document.createElement('UL')
                ul.className = 'z-3 position-absolute list-group shadow w-100'

                colonias.forEach((colonia) => {
                    const {
                        IdColonia,
                        NombreColonia,
                        IdMunicipio,
                        NombreMunicipio,
                        NombreEstado,
                    } = colonia

                    const li = document.createElement('LI')
                    li.className = 'list-group-item list-group-item-action'
                    li.role = 'button'
                    li.textContent = `${NombreColonia}, ${NombreMunicipio}, ${NombreEstado}`
                    li.onclick = () => {
                        $('#divColonia ul').remove()

                        // Actualizar los ID a la colonia seleccionada
                        empresa.IdColonia = IdColonia
                        empresa.NombreColonia = NombreColonia
                        empresa.IdMunicipio = IdMunicipio
                        empresa.NombreMunicipio = NombreMunicipio

                        // Mostrar en el input la información sobre la colonia
                        $('#colonia').val(NombreColonia)
                        $('#municipio').val(
                            `${NombreMunicipio}, ${NombreEstado}`
                        )
                    }

                    ul.appendChild(li)
                })

                $('#divColonia ul').remove()
                $('#divColonia').append(ul)
            })
            .fail(function () {
                empresa.IdColonia = ''
            })
    }
})

/**
 * Lista las colonias registradas en la base de datos para la sugerencia
 * al usuario.
 */
$('#municipio').on('keyup', function (e) {
    const nombreMunicipio = e.target.value
    $('#divMunicipio ul').remove()

    if (nombreMunicipio) {
        $.get(url, { case: 'listarMunicipios', nombreMunicipio })
            .done(function (res) {
                const respuesta = JSON.parse(res)
                const municipios = respuesta.data

                /**
                 * Crear listado debajo del input del formulario.
                 */
                const ul = document.createElement('UL')
                ul.className = 'z-3 position-absolute list-group shadow w-100'

                municipios.forEach((municipio) => {
                    const { IdMunicipio, NombreMunicipio, NombreEstado } =
                        municipio

                    const li = document.createElement('LI')
                    li.className = 'list-group-item list-group-item-action'
                    li.role = 'button'
                    li.textContent = `${NombreMunicipio}, ${NombreEstado}`
                    li.onclick = () => {
                        $('#divMunicipio ul').remove()

                        // Actualizar los ID a la colonia seleccionada
                        empresa.IdMunicipio = IdMunicipio
                        empresa.NombreMunicipio = NombreMunicipio
                        empresa.NombreEstado = NombreEstado

                        // Mostrar en el input la información sobre la colonia
                        $('#municipio').val(
                            `${NombreMunicipio}, ${NombreEstado}`
                        )
                    }

                    ul.appendChild(li)
                })

                $('#divMunicipio ul').remove()
                $('#divMunicipio').append(ul)
            })
            .fail(function () {
                empresa.IdMunicipio = ''
            })
    }
})

$('#formActualizarDatosEmpresa').submit(function (e) {
    e.preventDefault()

    /**
     * Validar si existen campos vacíos.
     */
    const keys = Object.keys(empresa)
    const camposVacios = keys.map((key) => {
        if (key !== 'IdColonia' && empresa[key] === '') return true
        return false
    })

    if (camposVacios.includes(true)) {
        const alerta = crearAlerta(
            'Todos los campos son obligatorios',
            'alert-danger'
        )
        alerta.classList.add('mt-3')
        $(this).append(alerta)

        setTimeout(() => $('.alert').remove(), 3000)
        return // Detener ejecución si existen campos vacíos
    }

    const data = {
        case: 'editarEmpresa',
        data: {
            empresa: { ...empresa },
            idEmpleador,
        },
    }

    $.post(url, data)
        .done(function (res) {
            const response = JSON.parse(res)

            let cuentaRegresiva = 3
            const main = $('main')
            main.empty()

            const template = `
                <div class="d-flex flex-column align-items-center h-100 my-5">
                    <img width="250px" height="250px" src="${getUrl(
                        '/AdmRepresentante/assets/green-check.png'
                    )}" alt="Ícono green check">
                    <h2 class="mb-3">${response.mensaje}</h2>
                    <p class="text-center mt-3" id="cuentaReg">Redireccionando en ${cuentaRegresiva}...</p>
                </div>
            `

            main.append(template)

            /**
             * Mostrar la cuenta regresiva en pantalla
             */
            setInterval(() => {
                cuentaRegresiva--
                $('#cuentaReg').text(`Redireccionando en ${cuentaRegresiva}...`)
            }, 1000)

            /**
             * Redireccionar al módulo de AdmVacantes después de 3s.
             */
            setTimeout(() => {
                location.href = '../AdmVacantes/'
            }, 3000)
        })
        .fail(function (err) {
            const error = JSON.parse(err.responseText)

            const alerta = crearAlerta(error.mensaje, 'alert-danger')
            alerta.classList.add('mt-3')
            $('#formActualizarDatosEmpresa').append(alerta)

            setTimeout(() => $('.alert').remove(), 3000)
        })
})
