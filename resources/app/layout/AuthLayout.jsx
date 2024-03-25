import { Outlet } from "react-router-dom";
import loginImg from '../public/images/fondo.jpeg';
import { ToastContainer } from 'react-toastify';
import "react-toastify/dist/ReactToastify.css";
import { useEffect } from "react";

export default function AuthLayout() {

    useEffect(() => {
        
    },[])

    return (
        <>
            <div className="grid grid-cols-1 sm:grid-cols-2 h-screen w-full">

                <div className="hidden sm:block ">
                    <img className="w-full h-full object-cover" src={loginImg} alt="loginImg" />
                </div>

                <div className="bg-white bg-[radial-gradient(circle_500px_at_55%_500px,#C9EBFF,transparent)] flex flex-col justify-center">
                    <Outlet />
                </div>

            </div>
            <ToastContainer />
        </>
    )
}
