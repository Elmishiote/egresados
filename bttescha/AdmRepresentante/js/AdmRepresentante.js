import { getUrl, crearAlerta } from '../../helpers/js/functions.js'

let idEmpresa = ''

// Construir la URL completa para hacer peticiones
const url = getUrl('/AdmRepresentante/php/AdmRepresentante.php')

function validarConvenio(numConvenio) {
    const data = { case: 'validarConvenio', numConvenio }

    if (!numConvenio) {
        const alerta = crearAlerta(
            'Debes ingresar un número de convenio',
            'alert-danger'
        )
        alerta.classList.add('mx-auto')
        form.before(alerta)

        setTimeout(() => $('.alert').remove(), 3000)
        return
    }

    $.get(url, data)
        .done(function (data) {
            const respuesta = JSON.parse(data)

            // Generar formulario para registro de representante
            form.empty()
            const { IdEmpresa, NombreEmpresa, RazonSocial, NumeroConvenio } =
                respuesta.data
            idEmpresa = IdEmpresa

            const template = `
                <div class="card mb-3">
                    <div class="card-body">
                        <h3 class="card-title">${NombreEmpresa}</h3>
                        <h6 class="card-subtitle mb-2 text-body-secondary">${RazonSocial}</h6>
                        <div class="badge bg-primary text-wrap">
                            Número de convenio: ${NumeroConvenio}
                        </div>                    
                    </div>
                </div>
    
                <fieldset>
                    <legend class="fw-bold">Información personal</legend>
                    <div class="mb-3">
                        <label for="nombreEmpleador" class="form-label">
                            Nombre
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nombreEmpleador" id="nombreEmpleador" class="form-control">
                    </div>
    
                    <div class="mb-3">
                        <div class="row">
                            <div class="col">
                                <label for="primerApellido" class="form-label">
                                    Primer apellido
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="primerApellido" id="primerApellido" class="form-control">
                            </div>
                            <div class="col">
                                <label for="segundoApellido" class="form-label">
                                    Segundo apellido
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="segundoApellido" id="segundoApellido" class="form-control">
                            </div>
                        </div>
                    </div>
    
                    <div class="mb-3">
                        <label for="telefono" class="form-label">
                            Número de teléfono
                            <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="telefono" id="telefono" class="form-control">
                    </div>
    
                    <div class="mb-3">
                        <label for="puesto" class="form-label">
                            Puesto
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="puesto" id="puesto" class="form-control">
                    </div>
                </fieldset>
    
                <fieldset>
                    <legend class="fw-bold">Información sobre tu cuenta</legend>
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            Nombre de usuario
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="username" id="username" class="form-control">
                    </div>
    
                    <div class="mb-3">
                        <label for="pass1" class="form-label">
                            Contraseña
                            <span class="text-danger">*</span>
                        </label>
                        <input type="password" name="pass1" id="pass1" class="form-control">
                    </div>
    
                    <div class="mb-3">
                        <label for="pass2" class="form-label">
                            Repetir contraseña
                            <span class="text-danger">*</span>
                        </label>
                        <input type="password" name="pass2" id="pass2" class="form-control">
                    </div>
                </fieldset>
    
                <button type="submit" class="btn btn-primary w-100">Registrar</button>
            `

            form.append(template)
        })
        .fail(function (err) {
            const error = JSON.parse(err.responseText)

            const alerta = crearAlerta(error.mensaje, 'alert-danger')
            alerta.classList.add('mx-auto')
            form.before(alerta)

            setTimeout(() => $('.alert').remove(), 3000)
        })
}

const form = $('#form')
form.submit(function (e) {
    e.preventDefault()

    if ($('#numConvenio').length) {
        const numConvenio = $('#numConvenio').val()
        validarConvenio(numConvenio)
    } else {
        const inputUsuario = form.serializeArray()
        let datos = {}

        $.each(inputUsuario, function (i, campo) {
            datos[campo.name] = campo.value
        })
        datos.idEmpresa = idEmpresa

        // Validar que no existan campos vacíos
        for (let key in datos) {
            if (datos.hasOwnProperty(key)) {
                if (datos[key] === '') {
                    const alerta = crearAlerta(
                        'Todos los campos son obligatorios',
                        'alert-danger'
                    )
                    alerta.classList.add('mt-4')
                    form.append(alerta)

                    setTimeout(() => $('.alert').remove(), 3000)
                    return
                }
            }
        }

        // Validar que ambos campos de contraseñas coincidan
        if (datos.pass1 !== datos.pass2) {
            const alerta = crearAlerta(
                'Las contraseñas no coinciden',
                'alert-danger'
            )
            alerta.classList.add('mt-4')
            form.append(alerta)

            setTimeout(() => $('.alert').remove(), 3000)
            return
        }

        /**
         * Si todo está correcto, hacemos la petición para registrar el usuario.
         */
        const data = {
            case: 'registrarEmpleador',
            data: {
                usuario: {
                    nick: datos.username,
                    password: datos.pass1,
                },
                empleador: {
                    nombre: datos.nombreEmpleador,
                    primerApellido: datos.primerApellido,
                    segundoApellido: datos.segundoApellido,
                    telefono: datos.telefono,
                    puesto: datos.puesto,
                },
                idEmpresa,
            },
        }

        // Peticion
        $.post(url, data)
            .done(function (data) {
                const respuesta = JSON.parse(data)

                const main = $('main')
                main.empty()

                const template = `
                <div class="d-flex flex-column align-items-center h-100 my-5">
                    <img with="250px" height="250px" src="${getUrl(
                        '/AdmRepresentante/assets/green-check.png'
                    )}" alt="Ícono green check">
                    <h2 class="mb-3">Registro realizado exitosamente</h2>
                    <a href="../">Iniciar sesión</a>
                </div>
                `

                main.append(template)
            })
            .fail(function (err) {
                const error = JSON.parse(err.responseText)

                const alerta = crearAlerta(error.mensaje, 'alert-danger')
                alerta.classList.add('mt-4')
                form.append(alerta)

                setTimeout(() => $('.alert').remove(), 3000)
            })
    }
})
