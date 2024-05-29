import { Link, useNavigate } from "react-router-dom";
import Cargando from "../components/Cargando";
import useProyect from "../hooks/useProyect";
import clienteAxios from "../config/axios";
import useSWR from "swr";
import { useState, useEffect } from "react";
import Swal from 'sweetalert2'
import Alerta from "../components/Alerta";
import { convertirFecha, convertirFechaSinHora } from "../helpers/CajaChica";
import Excel from "../components/Excel";

export default function HistorialCajaChica() {

    const { changeStateModalCajaChicaDocumento, setFechasGasto, crearRecarga, idMoneyBox, crearCajaChica } = useProyect();

    const token = localStorage.getItem('AUTH_TOKEN')

    const [dateOne, setDateOne] = useState('');
    const [dateTwo, setDateTwo] = useState('');
    const [errores, setErrores] = useState('');
    const [mostrarExcel, setMostrarExcel] = useState(false);

    const idCurrentMoneyBox = idMoneyBox || '1';
    // console.log(idCurrentMoneyBox)
    const fetcher = () => clienteAxios(`/api/moneybox/history/${idCurrentMoneyBox}`, {
        headers: {
            Authorization: `Bearer ${token}`
        }
    }).then(data => data.data)

    const { data, error, isLoading, mutate } = useSWR(`/api/moneybox/history/${idCurrentMoneyBox}`, fetcher, {
        // revalidateOnFocus: false,
        // revalidateIfStale: false,
        // revalidateOnReconnect: false
    })


    const handleDocument = (e) => {
        const option = e.target.value

        if (option !== '') {

            let erroresFuncion = []
            setErrores([])

            if ([dateOne, dateTwo].includes('')) {
                setErrores(['Las 2 fechas son obligatorias'])
                erroresFuncion = ['hay errores']
            }

            if (new Date(dateOne) > new Date(dateTwo)) {
                setErrores(['La fecha de inicio debe de ser menor o igual a la fecha fin'])
                erroresFuncion = ['hay errores']
            }

            if (erroresFuncion.length === 0) {
                setFechasGasto({
                    dateOne,
                    dateTwo
                })
                if (option === 'pdf') {
                    changeStateModalCajaChicaDocumento()
                    setMostrarExcel(false)
                } else {
                    setMostrarExcel(!mostrarExcel)
                }
            }
        }
    }

    const handleConfirmar = async () => {

        const { value: monto } = await Swal.fire({
            title: 'Ingresa el monto:',
            icon: "warning",
            input: 'number',
            inputLabel: 'monto',
            inputValidator: (value) => {
                if (!value) {
                    return 'El monto es obligatorio'
                }
            },
            inputPlaceholder: 'Ej. 850.50',
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Si, confirmar"
        });

        const confirmar = async () => {
            if (monto) {
                let resultado = false
                resultado = await crearRecarga(monto)
                if (resultado) {
                    Swal.fire('Monto aumentado')
                } else {
                    Swal.fire('Monto no guardado, error')
                }

            }
        }
        confirmar();
    };

    const handleNewMoneyBox = () => {
        Swal.fire({
            title: 'Nueva caja chica',
            text: "¿Quiere crear una nueva caja chica?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Si, crear"
        }).then((result) => {
            if (result.isConfirmed) {
                const mostrarRespuesta = async () => {
                    const respuesta = await crearCajaChica();
                    if (Boolean(respuesta)) {
                        mutate()
                        Swal.fire({
                            title: "Creada correctamente!",
                            text: respuesta,
                            icon: "success"
                        });
                    } else {
                        Swal.fire({
                            icon: "Error",
                            title: "Oops...ocurrio un error",
                            text: "Fallo en el servidor!",
                        });
                    }

                }
                mostrarRespuesta();
            }
        });
    };

    // useEffect(() => {
    //     mutate()
    // }, [])

    if (isLoading) return <Cargando />
    // console.log(data)
    return (
        <>
            <div className="flex max-lg:flex-col max-lg:gap-3 items-center justify-between p-2 rounded-lg shadow-2xl bg-white">
                <div className="flex max-lg:flex-col max-lg:gap-3 items-center justify-between w-full lg:mr-5">
                    <p className="font-black md:text-4xl text-3xl text-blue-950 capitalize md:mr-3">
                        Historial Caja Chica
                    </p>
                    <div className="flex gap-2">
                        <button
                            className='bg-green-700 hover:bg-green-800 text-center text-white font-black p-3 rounded-lg flex gap-2'
                            onClick={() => {
                                handleNewMoneyBox()
                            }}
                        >Nueva caja
                        </button>
                        <button
                            className='bg-yellow-700 hover:bg-yellow-800 text-center text-white font-black p-3 rounded-lg flex gap-2'
                            onClick={() => {
                                handleConfirmar()
                            }}
                        >Crear desembolso <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-5 h-5">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </button>
                        <Link
                            to={-1}
                            className='bg-blue-950 text-center text-white font-black p-3 rounded-lg flex gap-2 items-center'
                        >Volver <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className="w-5 h-5">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>
            <form className="mt-2 rounded-lg md:flex-row md:gap-0 flex-col gap-2 items-center bg-sky-50 text-sm p-2">
                {errores ? errores.map((error, i) => <Alerta key={i}>{error}</Alerta>) : null}
                <div className="flex justify-around items-center md:flex-row flex-col md:gap-0 gap-2">
                    <div className="border-2 px-2">
                        <label className="font-black" htmlFor="dateOne">Fecha Inicio: </label>
                        <input type="date" name="dateOne" id="dateOne" className="font-bold text-black rounded-lg p-2" value={dateOne} onChange={e => setDateOne(e.target.value)} />
                    </div>
                    <div className="border-2 px-2">
                        <label className="font-black" htmlFor="dateTwo">Fecha Fin: </label>
                        <input type="date" name="dateOne" id="dateTwo" className="font-bold text-black rounded-lg p-2" value={dateTwo} onChange={e => setDateTwo(e.target.value)} />
                    </div>
                    <select onChange={(e) => {
                        handleDocument(e)
                    }}
                        className="bg-blue-500 hover:bg-blue-700 rounded-lg font-bold text-white p-2">
                        <option value="">Generar documento</option>
                        <option value="excel">Excel</option>
                        <option value="pdf">Pdf</option>
                    </select>
                </div>
            </form>
            {mostrarExcel && <Excel />}
            <div className="relative overflow-x-auto mt-3">
                <table className="w-full text-sm text-center mx-auto">
                    <thead className="text-sm uppercase bg-gray-600 text-white">
                        <tr>
                            <th scope="col" className="p-2">
                                Nro de Vale
                            </th>
                            <th scope="col" className="p-2">
                                Fecha
                            </th>

                            <th scope="col" className="p-2">
                                Nro de Factura
                            </th>
                            <th scope="col" className="p-2">
                                Descripción
                            </th>
                            <th scope="col" className="p-2">
                                Ingreso
                            </th>
                            <th scope="col" className="p-2">
                                Gasto
                            </th>
                            <th scope="col" className="p-2">
                                Saldo
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr className="bg-blue-500 border-b text-white text-xs">
                            <th scope="row" className="font-bold p-1" colSpan={5}>
                            </th>
                            <th scope="row" className="font-bold p-1" colSpan={1}>
                                {1000 - data.montoInicial}
                            </th>
                            <th scope="row" className=" font-bold" colSpan={1}>
                                {data.montoInicial}
                            </th>
                        </tr>
                        {
                            data?.gastos?.map(gasto => {
                                if (gasto.descripcion) {
                                    return (
                                        <tr className="bg-white border-b p-1 text-xs" key={gasto.id}>
                                            <td className="">
                                                {gasto.nro}
                                            </td>
                                            <th scope="row" className="p-1 font-medium">
                                                {convertirFechaSinHora(gasto.created_at)}
                                            </th>
                                            <td className="p-1">
                                                {gasto.nroFactura || 'Sin número de factura'}
                                            </td>
                                            <td className="p-1">
                                                {gasto.descripcion}
                                            </td>
                                            <td className="font-bold p-1">
                                                {gasto.ingreso !== 'no' ? `${gasto.gasto}` : ''}
                                            </td>
                                            <td className="font-bold p-1">
                                                {gasto.gasto}
                                            </td>
                                            <td className="font-bold p-1">
                                                {gasto.saldo}
                                            </td>
                                        </tr>
                                    )
                                } else {
                                    return (
                                        <tr className="bg-blue-950 border-b text-yellow-400 p-2" key={gasto.id}>
                                            <th scope="row" className="font-bold p-2" colSpan={2}>
                                                Desembolso Caja Chica
                                            </th>
                                            <th scope="row" className="font-bold p-2" colSpan={2}>
                                                Fecha desembolso: {convertirFecha(gasto.created_at)}
                                            </th>
                                            <th scope="row" className="font-bold" colSpan={1}>
                                                {gasto.montoRecarga}
                                            </th>
                                            <th scope="row" className="font-bold p-2" colSpan={1}>

                                            </th>
                                            <th scope="row" className="font-bold p-2" colSpan={1}>
                                                {gasto.saldo}
                                            </th>
                                        </tr>

                                    )
                                }
                            }
                            )
                        }
                        <tr className="bg-blue-500 border-b text-white text-xs">
                            <th scope="row" className="font-bold p-1" colSpan={5}>
                                GASTO TOTAL</th>
                            <th scope="row" className="font-bold p-1" colSpan={1}>
                                {data.gastoAcumulado}
                            </th>
                            <th scope="row" className="font-bold p-1" colSpan={1}>
                            </th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </>
    )
}
