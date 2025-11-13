import { getUrl, crearAlerta } from '../../helpers/js/functions.js'

const url = getUrl('/AdmVacantes/php/AdmVacantes.php')

/**
 * Con el ID de la empresa registraremos nuevas vacantes.
 * Este ID debería guardarse en el navegador cuando se inicia sesión.
 */
const idEmpresa = localStorage.hasOwnProperty('user_info')
    ? JSON.parse(localStorage.getItem('user_info')).IdEmpresa
    : null

/**
 * ID de la vacante que obtenemos de la URL (método GET).
 */
const idVacante = new URL(location.href).searchParams.get('v')

const vacante = {
    Nombre: '',
    InformacionVacante: '',
    FechaSolicitud: '',
    IdCarrera: '',
    Habilidades: '',
    Funciones: '',
    Genero: '',
    NumeroVacantes: '',
    Experiencia: '',
    DiasHorarioLaboral: '',
    Prestaciones: '',
    OfertaEconomica: '',
    CondicionLaboral: '',
    Capacitacion: '',
    PrestacionesLey: '',
    IdEmpresa: '',
}

/**
 * Llena el `<select>` con las carreras registradas en la base de datos.
 */
function llenarSelectCarreras() {
    const optionSeleccionar = '<option value="">Seleccionar</value>'
    const carrerasSelect = $('#carreraSelect')
    carrerasSelect.empty()
    carrerasSelect.append(optionSeleccionar)

    $.get(url, { case: 'listarCarreras' }).done(function (res) {
        const respuesta = JSON.parse(res)

        const { data: carreras } = respuesta
        let optionTag = ''

        carreras.forEach((carrera) => {
            optionTag += `
                <option value="${carrera.id_carrera}">${carrera.nombre}</option>
                `
        })

        carrerasSelect.append(optionTag)
    })
}

/**
 * Obtiene la información de la vacante dada por el ID de la URL
 * y sincroniza la información con el objeto local `vacante`.
 */
function getVacante() {
    $.get(url, { case: 'obtenerVacante', idVacante, idEmpresa })
        .done(function (res) {
            const respuesta = JSON.parse(res)

            /**
             * Llenar el objeto `vacante` con los datos recibidos en la petición.
             */
            const keys = Object.keys(respuesta.data)
            keys.forEach((key) => (vacante[key] = respuesta.data[key]))

            llenarForm()
        })
        .fail(function (err) {
            const error = JSON.parse(err.responseText)
            alert(error.mensaje)
            // Retornar al index
            location.href = 'index.php'
        })
}

/**
 * Llena el formulario con la información del objeto local `vacante`.
 */
function llenarForm() {
    const formulario = $('#formVacante :input')
    formulario.each(function () {
        const inputName = $(this).attr('name')

        if (!inputName) return

        /**
         * Validar si `vacante.Genero` es Hombre o Mujer, si no es ninguno
         * de los dos, coloca `Indistinto` por defecto.
         */
        if (inputName === 'Genero') {
            vacante[inputName] === 'Hombre'
                ? $(this).val('Hombre')
                : vacante[inputName] === 'Mujer'
                ? $(this).val('Mujer')
                : $(this).val('Indistinto')
        } else {
            $(this).val(vacante[inputName])
        }
    })
}

/**
 * Sincroniza el objeto local con los valores de los input del formulario,
 * actualizando la información cada vez que hay un cambio.
 */
function sincronizarObjLocal() {
    const formulario = $('#formVacante :input')

    formulario.each(function () {
        $(this).on('input', function () {
            const inputName = $(this).attr('name')
            const valorInput = $(this).val()

            vacante[inputName] = valorInput
        })
    })
}

$(document).ready(() => {
    $.get(url, { case: 'validarRegistro', idEmpresa }).done(function (res) {
        const response = JSON.parse(res)
        if (!response.validado) {
            location.href = '../AdmRepresentante'
        }
    })

    llenarSelectCarreras()
    getVacante()
    sincronizarObjLocal()
})

$('#formVacante').submit(function (e) {
    e.preventDefault()

    /**
     * Validar que no existan campos vacíos
     */
    for (let key in vacante) {
        if (vacante[key] === '') {
            const alerta = crearAlerta(
                'Todos los campos son obligatorios',
                'alert-danger'
            )
            alerta.classList.add('mt-4')
            $(this).append(alerta)

            setTimeout(() => $('.alert').remove(), 3000)
            return
        }
    }

    const data = {
        case: 'editarVacante',
        data: { ...vacante },
    }

    $.post(url, data)
        .done(function (res) {
            const respuesta = JSON.parse(res)

            let cuentaRegresiva = 3

            /**
             * Generar alerta de éxito.
             */
            const main = $('main')
            main.empty()

            const template = `
                <div class="d-flex flex-column align-items-center h-100 my-5">
                    <img width="250px" height="250px" src="${getUrl(
                        '/AdmVacantes/assets/green-check.png'
                    )}" alt="Ícono green check">
                    <h2 class="mb-3">${respuesta.mensaje}</h2>
                    <p class="text-center mt-3" id="cuentaReg">Redireccionando en ${cuentaRegresiva}...</p>
                </div>
            `

            main.append(template)

            /**
             * Mostrar ña cuenta regresiva en pantalla
             */
            setInterval(() => {
                cuentaRegresiva--
                $('#cuentaReg').text(`Redireccionando en ${cuentaRegresiva}...`)
            }, 1000)

            /**
             * Redireccionar al index después de 3s.
             */
            setTimeout(() => {
                location.href = 'index.php'
            }, 3000)
        })
        .fail(function (err) {
            const error = JSON.parse(err.responseText)
            alert(error.mensaje)
        })
})
