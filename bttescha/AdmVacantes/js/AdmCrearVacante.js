import { getUrl, crearAlerta } from '../../helpers/js/functions.js'

const url = getUrl('/AdmVacantes/php/AdmVacantes.php')

/**
 * Con el ID de la empresa registraremos nuevas vacantes.
 * Este ID debería guardarse en el navegador cuando se inicia sesión.
 */
const idEmpresa = localStorage.hasOwnProperty('user_info')
    ? JSON.parse(localStorage.getItem('user_info')).IdEmpresa
    : null

$('#formVacante').submit(function (e) {
    e.preventDefault()

    const inputUsuario = $(this).serializeArray()
    let data = {}

    $.each(inputUsuario, function (i, campo) {
        data[campo.name] = campo.value
    })
    data.IdEmpresa = idEmpresa

    /**
     * Validar que no existan datos vacíos
     */
    for (let key in data) {
        if (data.hasOwnProperty(key)) {
            if (data[key] === '') {
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
    }

    /**
     * Registrar vacante
     */
    const info = { case: 'crearVacante', data }

    $.post(url, info)
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
             * Mostrar la cuenta regresiva en pantalla
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

$(document).ready(() => {
    $.get(url, { case: 'validarRegistro', idEmpresa }).done(function (res) {
        const response = JSON.parse(res)
        if (!response.validado) {
            location.href = '../AdmRepresentante'
        }
    })

    /**
     * Llenar el `<select>` con las carreras registradas en la base de datos.
     */
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
})
