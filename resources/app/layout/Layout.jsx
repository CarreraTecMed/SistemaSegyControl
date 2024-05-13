import { Outlet, useNavigate } from 'react-router-dom'
import { ToastContainer } from 'react-toastify';
import "react-toastify/dist/ReactToastify.css";


import Modal from 'react-modal'
import useProyect from '../hooks/useProyect'
import Sidebar from '../components/Sidebar'
import ModalDetallesMateriales from '../components/ModalDetallesMateriales';
import ModalMaterial from '../components/ModalMaterial';
import SinPermisos from '../components/SinPermisos';
import ModalCajaChica from '../components/ModalCajaChica';
import ModalCajaChicaDocumento from '../components/ModalCajaChicaDocumento';
import ModalDocumento from '../components/ModalDocumento';
import ModalDocumentoGeneradoCorrespondencia from '../components/ModalDocumentoGeneradoCorrespondencia';
import ModalRecuperarContraseña from '../components/ModalRecuperarContraseña';
import { useEffect, useMemo } from 'react';
import Pusher from 'pusher-js';
import { useSWRConfig } from 'swr';


const customStyles = {
  content: {
    top: "50%",
    left: "50%",
    right: "auto",
    bottom: "auto",
    marginRight: "-50%",
    transform: "translate(-50%, -50%)",

  },
};

Modal.setAppElement('#root')

export default function Layout() {
  const { modalMoreDetails, modalMaterial, usuarioLogin, modalCorrespondencia, modalCajaChica, modalCajaChicaDocumento, modalDocumentoGeneradoCorrespondencia, modalContraseña, changeStateModalContraseña } = useProyect();
  const { mutate } = useSWRConfig()
  const navigate = useNavigate()
  useEffect(() => {
    if (usuarioLogin.resetear === '1') {
      changeStateModalContraseña(true)
    }
  }, [usuarioLogin])

  useEffect(() => {

    if ('Notification' in window) {
      Notification.requestPermission()
    }

    const pusher = new Pusher('cb26bf8979e3aa7ae571', {
      cluster: 'us2',
      debug: true
    });


    const channel = pusher.subscribe('my-channel')

    channel.bind('event-notification', (data) => {
      if (data.user === 'estudiante') {
        const notification = new Notification('Nuevo prestamo de material')
        notification.onclick = () => {
          navigate('/administrativo/materiales')
          // console.log('clickeando')
        }
        mutate('/api/orders')
      }
    })

    return () => {
      pusher.disconnect()
    }
  }, [])

  if (!Boolean(usuarioLogin)) return <SinPermisos />
  if (usuarioLogin.tipo == 'administrativo') {
    return (
      <>
        <div className='md:flex'>
          <Sidebar />

          <main className='flex-1 h-screen overflow-y-scroll bg-slate-200 p-3'>
            <Outlet />
          </main>
        </div>

        <Modal isOpen={modalContraseña} style={customStyles}>
          <ModalRecuperarContraseña />
        </Modal>

        <Modal isOpen={modalMoreDetails} style={customStyles}>
          <ModalDetallesMateriales />
        </Modal>

        <Modal isOpen={modalMaterial} style={customStyles}>
          <ModalMaterial />
        </Modal>

        <Modal isOpen={modalCajaChica} style={customStyles}>
          <ModalCajaChica />
        </Modal>

        <Modal isOpen={modalCajaChicaDocumento} style={customStyles}>
          <ModalCajaChicaDocumento />
        </Modal>

        <Modal isOpen={modalCorrespondencia} style={customStyles}>
          <ModalDocumento />
        </Modal>

        <Modal isOpen={modalDocumentoGeneradoCorrespondencia} style={customStyles}>
          <ModalDocumentoGeneradoCorrespondencia />
        </Modal>

        <ToastContainer />
      </>
    )
  } else {
    return <SinPermisos />
  }

}

