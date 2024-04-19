import { useNavigate } from 'react-router-dom'

export default function PaginaErronea() {
    const navigate = useNavigate()
    
    return (
        <div className="flex flex-col items-center justify-center w-3/4 h-screen mx-auto gap-5">
            <p className="font-black text-5xl text-center">Pagina con url desconocido</p>

            <button onClick={() => navigate(-1)} className='bg-blue-900 hover:bg-blue-950 p-3 rounded-lg font-black text-white mt-2'>Volver a anterior pagina</button>
        </div>
    )
}
