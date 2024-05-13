import React, { useEffect } from 'react'
import useProyect from '../hooks/useProyect'
import useSWR from 'swr';
import clienteAxios from '../config/axios';
import Cargando from './Cargando';

export default function Excel({ setMostrarExcel }) {
    const { fechasGasto, idMoneyBox } = useProyect()
    const { dateOne, dateTwo } = fechasGasto;
    const token = localStorage.getItem('AUTH_TOKEN')

    const fetcher = () => clienteAxios(`/api/moneyboxExcel/${dateOne}/${dateTwo}/${idMoneyBox || '1'}`, {
        headers: {
            Authorization: `Bearer ${token}`
        }
    }).then(data => data.data)

    const { data, error, isLoading } = useSWR(`/api/moneyboxExcel/${dateOne}/${dateTwo}/${idMoneyBox || '1'}`, fetcher, {
        revalidateOnFocus: false,
        revalidateIfStale: false,
        revalidateOnReconnect: false
    })
    if (isLoading) return <Cargando />
    
    return (
        <iframe
            className="w-0 h-0"
            src={`data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,${data?.fileContent}`}

        />
    )
}
