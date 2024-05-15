import { Page, Text, View, Document, StyleSheet, Image, PDFViewer, Font } from '@react-pdf/renderer';
import { createTw } from 'react-pdf-tailwind'
import { convertirFecha, convertirFechaSinHora, convertirNumeroALetras, convertirFechaHora } from '../helpers/CajaChica';
import useProyect from '../hooks/useProyect';

const tw = createTw({
    theme: {
        fontFamily: {
            sans: ["Comic Sans"],
        },
        extend: {
            colors: {
                custom: "#bada55",
            },
        },
    },
});

export default function CajaChicaInformationPdf() {

    const { gastoElegido } = useProyect();
    const { nroFactura, nro, gasto: costo, id, descripcion, created_at, interested, manager } = gastoElegido;
    const { nombres, apellidoPaterno, apellidoMaterno } = manager
    // console.log(gastoElegido)
    const styles = getDynamicStyles(nroFactura)

    return (


        // MODELO 2 DADO POR CARMENCITA

        <PDFViewer style={styles.container}>
            <Document>
                <Page style={styles.body} wrap>
                    <View style={styles.borders}>
                        {/* COMPROBANTE DE CAJA CHICA */}
                        <View style={styles.header}>
                            <Text style={styles.title}>CARRERA DE {'\n'}TECNOLOGÍA MÉDICA</Text>
                            <Text style={styles.titleBigger}>COMPROBANTE DE CAJA CHICA</Text>
                            <Text style={styles.nroVale}>No. {nro}</Text>
                        </View>

                        <View style={styles.content}>
                            <View style={styles.divSeparador}>
                                {/* <Text style={tw("text-red-500")}>Ciudad: <Text style={styles.span}>La Paz</Text></Text> */}
                                <Text style={tw("text-red-500")}>Fecha: <Text style={styles.span}>{convertirFechaSinHora(created_at)}</Text></Text>
                            </View>
                            <View style={styles.divSeparador}>
                                <Text style={tw("text-red-500")}>Pagado a: <Text style={styles.span}>{interested}</Text></Text>
                                <Text style={tw("text-red-500")}>Cantidad: <Text style={styles.span}>Bs. {costo}</Text></Text>
                            </View>
                            <View style={styles.divSeparador}>
                                <Text style={tw("text-red-500")}>La suma de: <Text style={styles.span}>{convertirNumeroALetras(costo)} en efectivo</Text></Text>

                            </View>
                            <Text style={tw("text-red-500 text-justify leading-6")}>Por concepto de: <Text style={styles.span}>{descripcion}</Text></Text>
                        </View>
                        <View style={styles.firmasContent}>
                            {
                                !nroFactura && <View style={styles.firmas1}>
                                    <Text>{interested}</Text>
                                    <Text>INTERESADO</Text>
                                    <Text>RECIBI CONFORME</Text>
                                </View>
                            }

                            <View style={styles.firmas2}>
                                {/* <Text>María del Carmen Murillo de Espinoza</Text> */}
                                <Text>{`${nombres} ${apellidoPaterno} ${apellidoMaterno}`}</Text>
                                <Text>RESPONSABLE</Text>
                                <Text>ENTREGUE CONFORME</Text>
                            </View>
                            <View style={styles.firmas3}>
                                <Text>Autorizado por</Text>
                            </View>

                        </View>
                    </View>

                    {/* COMPROBANTE DE VALE */}
                    {
                        !nroFactura && (
                            <>
                                <View style={styles.lineaSeparadora}></View>
                                <View style={styles.espaciado}></View>
                                <View style={styles.borders}>
                                    {/* COMPROBANTE DE CAJA CHICA */}
                                    <View style={styles.header}>
                                        <Text style={styles.title}>CARRERA DE {'\n'}TECNOLOGÍA MÉDICA</Text>
                                        <Text style={styles.titleBigger}>VALE DE MOVILIDAD</Text>
                                        <Text style={styles.nroVale}>No. {nro}</Text>
                                    </View>

                                    <View style={styles.content}>
                                        <View style={styles.divSeparador}>
                                            <Text style={tw("text-red-500")}>Fecha: <Text style={styles.span}>{convertirFechaSinHora(created_at)}</Text></Text>
                                            <Text style={tw("text-red-500")}>Hora: <Text style={styles.span}>{convertirFechaHora(created_at)}</Text></Text>
                                        </View>
                                        <View style={styles.divSeparador}>
                                            <Text style={tw("text-red-500")}>Entregado a: <Text style={styles.span}>{interested}</Text></Text>
                                            <Text style={tw("text-red-500")}>Cantidad: <Text style={styles.span}>Bs. {costo}</Text></Text>
                                        </View>
                                        <View style={styles.divSeparador}>
                                            <Text style={tw("text-red-500")}>La cantidad de: <Text style={styles.span}>{convertirNumeroALetras(costo)} en efectivo</Text></Text>

                                        </View>
                                        <Text style={tw("text-red-500 text-justify leading-6")}>Concepto y lugar: <Text style={styles.span}>{descripcion}</Text></Text>
                                    </View>
                                    <View style={styles.firmasContent}>
                                        {
                                            !nroFactura && <View style={styles.firmas1}>
                                                <Text>{interested}</Text>
                                                <Text>INTERESADO</Text>
                                                <Text>RECIBI CONFORME</Text>
                                            </View>
                                        }

                                        <View style={styles.firmas2}>
                                            {/* <Text>María del Carmen Murillo de Espinoza</Text> */}
                                            <Text>{`${nombres} ${apellidoPaterno} ${apellidoMaterno}`}</Text>
                                            <Text>RESPONSABLE</Text>
                                            <Text>ENTREGUE CONFORME</Text>
                                        </View>
                                        <View style={styles.firmas3}>
                                            <Text>Autorizado por</Text>
                                        </View>

                                    </View>
                                </View>
                            </>
                        )
                    }
                </Page>
            </Document>
        </PDFViewer>
    )
}

const getDynamicStyles = (nroFactura) => StyleSheet.create({

    // modelo 2 carmencita 

    container: {
        width: '100%',
        height: '100%',
        fontFamily: 'OpenSans'
    },
    titleContainer: {
        display: 'flex',
        alignItems: 'center',
        fontSize: '10px',
        border: '1px solid black',
        padding: '10px'
    },
    valeMovilidadContainer: {
        marginTop: '30px'
    },
    body: {
        padding: '20px',
        fontSize: '11px',
    },
    header: {
        display: 'flex',
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
    },
    imageLogo: {
        width: '43px',
        height: '80px',
        overflow: 'hidden'
    },
    imageLogoCarrera: {
        width: '50px',
        height: '70px',
    },
    title: {
        textAlign: 'center',
        borderBottom: '1px solid black',
        flex: 3,
        padding: '11px 0 10px 0'
    },
    titleBigger: {
        textAlign: 'center',
        fontSize: '15px',
        fontWeight: 'bold',
        borderBottom: '1px solid black',
        borderLeft: '1px solid black',
        borderRight: '1px solid black',
        flex: 7,
        padding: '13px 0 16px 0',

    },
    nroVale: {
        fontSize: '17px',
        fontWeight: 'bold',
        flex: 2,
        textAlign: 'center'
    },
    content: {
        display: 'flex',
        gap: '10',
        paddingHorizontal: '40px',
        marginTop: '15px',
    },
    divSeparador: {
        display: 'flex',
        flexDirection: 'row',
        justifyContent: 'space-between',
    },
    span: {
        color: 'black'
    },
    firmasContent: {
        display: 'flex',
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'flex-end',
        paddingHorizontal: '20px',
        borderTop: '1px solid black',
        marginTop: '10px',
    },
    firmas1: {
        lineHeight: '1.5',
        fontSize: '10px',
        textAlign: 'center',
        flex: 1
    },
    firmas2: {
        lineHeight: '1.5',
        fontSize: '10px',
        paddingTop: '50px',
        borderRight: '1px solid black',
        borderLeft: !nroFactura ? '1px solid black' : 'none',
        textAlign: 'center',
        flex: 2
    },
    firmas3: {
        flex: 1,
        textAlign: 'center',
        paddingLeft: '10px'
    },
    titleNumber: {
        display: 'flex',
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginRight: '20px'
    },
    titleWhite: {
        color: 'white'
    },
    borders: {
        border: '1px solid black'
    },
    lineaSeparadora: {
        width: '100%',
        marginTop: '20px',
        marginBottom: '20px',
        borderTop: '1px dasshed gray',
    },
    espaciado: {
        paddingTop: '4px'
    }
})