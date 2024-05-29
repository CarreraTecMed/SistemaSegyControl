<?php

namespace App\Http\Controllers;

use App\Exports\MoneyBoxExport;
use App\Http\Resources\MoneyBoxCollection;
use App\Models\MoneyBox;
use App\Models\Recharge;
use App\Models\Spent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelWriter;
use PDF;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MoneyBoxController extends Controller
{
    public function getMoneyBox($id)
    {
        $cajaChica = MoneyBox::with('manager')->with('director')->find($id);
        $spents = Spent::orderBy('created_at', 'desc')->where('money_boxes_id', $cajaChica->id)->get();
        $recargas = Recharge::where('money_box_id', $cajaChica->id)->get();
        $montoInicial = $cajaChica->monto;
        $gastos = 1000 - $cajaChica->monto;

        foreach ($spents as $spent) {
            $gastos += $spent->gasto;
            $montoInicial -= $spent->gasto;
            if ($spent->ingreso === 'si') {
                $montoInicial += number_format($spent->gasto, 2);
                $gastos -= number_format($spent->gasto, 2);
            }
        }

        foreach ($recargas as $recarga) {
            $montoInicial += $recarga->montoRecarga;
            $gastos -= $recarga->montoRecarga;
        }
        $cajaChica->saldo = number_format($montoInicial, 2);
        $cajaChica->gasto = number_format($gastos, 2);
        $cajaChica->spents = $spents;
        return $cajaChica;
    }

    public function moneyBoxes()
    {
        $moneyBoxes = MoneyBox::all();
        return $moneyBoxes;
    }

    public function getMoneyBoxHistory($id)
    {
        $cajaChica = MoneyBox::find($id);
        $spents = Spent::orderBy('created_at', 'desc')->where('money_boxes_id', $cajaChica->id)->get();
        $recharges = Recharge::where('money_box_id', $id)->get();
        // $spents = Spent::all();
        $spentsRecharges = $spents->concat($recharges)->sortBy('created_at')->values();
        $montoInicial = $cajaChica->monto;
        $gastos = 1000 - $cajaChica->monto;

        foreach ($spentsRecharges as $spentRecharge) {
            if (isset($spentRecharge->estado)) { //recarga
                $montoInicial += floatval($spentRecharge->montoRecarga);
                $spentRecharge->saldo = $montoInicial;
                $gastos -= $spentRecharge->montoRecarga;
            } else { //gasto
                if ($spentRecharge->ingreso === 'si') {
                    $montoInicial += number_format(($spentRecharge->gasto), 2);
                    $gastos -= number_format(($spentRecharge->gasto), 2);
                }
                $montoInicial = $montoInicial - $spentRecharge->gasto;
                $spentRecharge->saldo = $montoInicial;
                $gastos += $spentRecharge->gasto;
            }
        }
        return [
            'gastos' => $spentsRecharges,
            'gastoAcumulado' => number_format($gastos, 2),
            'montoInicial' => $cajaChica->monto
        ];
    }

    public function createMoneyBox()
    {
        DB::beginTransaction();

        try {
            $currentYear = Carbon::now()->year;
            MoneyBox::create([
                'nombre' => 'CC. ' . $currentYear,
                'monto' => 1000.00,
                'user_id' => '2',
                'director_teacher_id' => '1'
            ]);

            // $moneyBox->monto = $moneyBox->monto - $spent->gasto;

            DB::commit();
            return [
                "message" => "Caja chica creada correctamente",
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            return response([
                'errors' => ['Ocurrio algo inesperado con el servidor: ' . $th->getMessage()]
            ], 422);
        }
    }

    public function getMoneyBoxRecopilation($dateOne, $dateTwo, $id)
    {
        //Fechas
        $fechaInicial = Carbon::parse($dateOne);
        $fechaFinal = Carbon::parse($dateTwo);

        $cajaChica = MoneyBox::with('director')->with('manager')->find($id);
        $recharges = Recharge::where('money_box_id', $id)->get();

        $spentsModel = Spent::where('money_boxes_id', $id)->get();
        $spentsRecharges = $spentsModel->concat($recharges)->sortBy('created_at')->values();

        $ultimaRecarga = [];
        if (count($recharges) > 0) {
            $ultimaRecarga = $recharges[count($recharges) - 1];
        } else {
            $ultimaRecarga = ['id' => '0'];
        }

        $montoInicial = $cajaChica->monto;
        $gastoInicial = 1000 - $cajaChica->monto;

        foreach ($spentsRecharges as $spentRecharge) {
            $fechaComparar = Carbon::parse(isset($spentRecharge->fechaCreacion) ? $spentRecharge->fechaCreacion : $spentRecharge->fechaRecarga);

            if ($fechaComparar->between($fechaInicial, $fechaFinal)) {
                break;
            }

            if (isset($spentRecharge->fechaCreacion)) { //gasto
                if ($spentRecharge->ingreso === 'si') {
                    $montoInicial += number_format(($spentRecharge->gasto), 2);
                    $gastoInicial -= number_format($spentRecharge->gasto, 2);
                }
                $montoInicial -= number_format($spentRecharge->gasto, 2);
                $gastoInicial += number_format($spentRecharge->gasto, 2);
            } else { //Reembolso
                $montoInicial += number_format($spentRecharge->montoRecarga);
                $gastoInicial -= number_format($spentRecharge->montoRecarga, 2);
            }
        }

        //HEADER

        $fechaInicial->setLocale('Es');
        $fechaFinal->setLocale('Es');
        $fechaMostrarOne = strtoupper($fechaInicial->isoFormat('D MMMM Y'));
        $fechaMostrarTwo = strtoupper($fechaFinal->isoFormat('D MMMM Y'));

        //Creadno el pdf 
        $pdf = new PDF_MC_Table();
        $pdf->AddPage('L', 'Legal');
        $pdf->SetFont('Arial', '', 14);
        $pdf->Image(public_path('logos/logoTecMed.png'), 319, 6, 20);
        $pdf->Image(public_path('logos/LogoUMSA.png'), 10, 6, 18, 30);
        $pdf->SetFont('Arial', 'B', 16,);
        $pdf->Cell(335, 8, iconv('UTF-8', 'windows-1252', 'UNIVERSIDAD MAYOR DE SAN ANDRÉS'), 0, 1, 'C');
        $pdf->Cell(335, 8, iconv('UTF-8', 'windows-1252', 'FACULTAD DE MEDICINA, ENFERMERÍA, NUTRICIÓN Y TECNOLOGÍA MÉDICA'), 0, 1, 'C');
        $pdf->Cell(335, 10, iconv('UTF-8', 'windows-1252', 'CARRERA DE TECNOLOGÍA MÉDICA'), 0, 1, 'C');
        $pdf->Cell(335, 5, '', 0, 1, 'C');
        $pdf->Cell(330, 10, iconv('UTF-8', 'windows-1252', 'DETALLE DE GASTOS DE CAJA CHICA CARRERA DE TECNOLOGÍA MÉDICA'), 0, 1, 'C');
        $pdf->Cell(330, 8, iconv('UTF-8', 'windows-1252', 'PERIODO: DEL ' . $fechaMostrarOne . ' AL ' . $fechaMostrarTwo), 0, 1, 'C');
        $pdf->Ln();
        // Definir encabezados de la tabla
        $header = array('Nro', 'Nro de Cbtes.', 'Fecha', 'Nombre beneficiario', 'Factura', 'Concepto del gasto', 'Cantidad', 'Ingreso', 'Gasto', 'Saldo');


        $widths = array(8, 12, 15, 40, 25, 150, 20, 20, 20, 20);
        $aligns = array('C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C');
        $pdf->SetWidths($widths);
        $pdf->SetAligns($aligns);
        // Agregar encabezados de tabla
        $pdf->SetFont('Arial', 'B', 8);


        // $pdf->Cell($widths[$i], 10, $header[$i], 1, 0, 'C');
        $pdf->Row(array($header[0], $header[1], $header[2], $header[3], $header[4], $header[5], $header[6], $header[7], $header[8], $header[9]));


        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(270, 10, 'SALDO Y GASTO (ANTES DE FECHAS)', 1, 0, 'C');
        $pdf->Cell(20, 10, '', 1, 0, 'C');
        $pdf->Cell(20, 10, number_format($gastoInicial, 2), 1, 0, 'C');
        $pdf->Cell(20, 10, number_format($montoInicial, 2), 1, 0, 'C');
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 8);
        //Gastos que se realizaron entre fechas
        $spents = [];

        foreach ($spentsRecharges as $spentRecharge) {
            $fechaComparar = Carbon::parse(isset($spentRecharge->fechaCreacion) ? $spentRecharge->fechaCreacion : $spentRecharge->fechaRecarga);
            if ($fechaComparar->between($fechaInicial, $fechaFinal)) {
                $spents[] = $spentRecharge;
            }
        }
        //Datos

        $data = [];

        $pdf->SetWidths($widths);
        $pdf->SetAligns($aligns);

        $nroVales = 1;
        $gastoEncontrado = false;
        $gastoPorcentaje = 0;

        foreach ($spents as $spent) {
            $fechaCambiada = Carbon::createFromFormat('Y-m-d', isset($spent->fechaCreacion) ? $spent->fechaCreacion : $spent->fechaRecarga);

            if (isset($spent->fechaCreacion)) { //gasto 

                $montoInicial -= number_format($spent->gasto, 2);
                $gastoInicial += number_format($spent->gasto, 2);

                if ($spent->ingreso === 'si') {
                    $montoInicial += number_format($spent->gasto, 2);
                    $gastoInicial -= number_format($spent->gasto, 2);
                }
                $data[] = [
                    $nroVales,
                    $spent->nro,
                    $fechaCambiada->format('d-m-Y'),
                    iconv('UTF-8', 'windows-1252', $spent->interested),
                    $spent->nroFactura !== '' && $spent->nroFactura !== null ? $spent->nroFactura : 'Sin factura',
                    iconv('UTF-8', 'windows-1252', $spent->descripcion),
                    iconv('UTF-8', 'windows-1252', $spent->cantidad),
                    $spent->ingreso === 'no' ? '' : number_format($spent->gasto, 2),
                    number_format($spent->gasto, 2),
                    number_format($montoInicial, 2)
                ];
            } else { //desembolso
                if ($spent->id === $ultimaRecarga->id) {
                    $gastoEncontrado = true;
                }
                $data[] = [
                    $nroVales,
                    '',
                    $fechaCambiada->format('d-m-Y'),
                    '',
                    '',
                    'DESEMBOLSO CAJA CHICA:',
                    '',
                    $spent->montoRecarga,
                    '',
                    number_format(($montoInicial + $spent->montoRecarga), 2)
                ];
                // $gastoInicial -= number_format($spent->montoRecarga, 2);
                $montoInicial += number_format($spent->montoRecarga, 2);
            }
            $nroVales += 1;
        };
        // for ($i = 0; $i < 22; $i++){ 
        //         $data[] = ['1', 'Dato ', 'Dato ', Str::random(rand(1, 250)), 'Dato 1', 'Dato 2','Dato','Dato'];
        //     };

        if (!$gastoEncontrado) { // No hay recargas
            $gastoPorcentaje = $gastoInicial;
        } else {
            $gastoEncontrado = false;
            foreach ($spents as $spent) {
                $fechaCambiada = Carbon::createFromFormat('Y-m-d', isset($spent->fechaCreacion) ? $spent->fechaCreacion : $spent->fechaRecarga);

                if (isset($spent->fechaCreacion)) { //gasto 
                    if ($gastoEncontrado) {
                        $gastoPorcentaje += number_format($spent->gasto, 2);
                    }
                } else { //desembolso
                    if ($spent->id === $ultimaRecarga->id) {
                        $gastoEncontrado = true;
                    }
                }
            }
        }
        $pdf->SetFont('Arial', '', 7);
        for ($i = 0; $i < count($data); $i++)
            $pdf->Row(array($data[$i][0], $data[$i][1], $data[$i][2], $data[$i][3], $data[$i][4], $data[$i][5], $data[$i][6], $data[$i][7], $data[$i][8], $data[$i][9]));

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(270, 10, 'SALDO Y GASTO (DESPUES DE FECHAS)', 1, 0, 'C');
        $pdf->Cell(20, 10, '', 1, 0, 'C');
        $pdf->Cell(20, 10, number_format($gastoInicial, 2), 1, 0, 'C');
        $pdf->Cell(20, 10, number_format($montoInicial, 2), 1, 0, 'C');

        $porcentajeGastos = ($gastoPorcentaje / 1000) * 100;
        $saldoSobrante = 1000 - $gastoPorcentaje;
        $porcentajeSobrante = ($saldoSobrante / 1000) * 100;
        $ultimaInformacion =
            [
                array('DETALLE', 'IMPORTE', '%'),
                array('TOTAL CAJA CHICA ASIGNADO Bs.', '1000', '100%'),
                array('TOTAL GASTO Bs.', $gastoPorcentaje, (string)$porcentajeGastos . '%'),
                array('SALDO DISPONIBLE', $saldoSobrante, (string)$porcentajeSobrante . '%')
            ];
        $pdf->SetWidths(array('70', '30', '30'));
        $pdf->SetAligns(array('C', 'C', 'C'));
        // $pdf->Row(array($informacionUltima[0],$informacionUltima[1],$informacionUltima[2]));
        $pdf->Ln();
        $pdf->Ln();
        $pdf->SetX(200);

        if ($pdf->GetY() >= 200) {
            $this->AddPage('L', 'Legal');
            $pdf->SetXY(10, 30);
        }

        for ($i = 0; $i < count($ultimaInformacion); $i++) {
            if ($i === 0 || $i === 3) {
                $pdf->SetFont('Arial', 'B', 8);
            } else {
                $pdf->SetFont('Arial', '', 8);
            }
            $pdf->Cell(100, 5, iconv('UTF-8', 'windows-1252', $ultimaInformacion[$i][0]), 1, 0, 'C');
            $pdf->Cell(30, 5, iconv('UTF-8', 'windows-1252', $ultimaInformacion[$i][1]), 1, 0, 'C');
            $pdf->Cell(10, 5, iconv('UTF-8', 'windows-1252', $ultimaInformacion[$i][2]), 1, 0, 'C');
            $pdf->Ln();
            $pdf->SetX(200);
        }
        $pdf->Ln();
        if ($pdf->GetY() >= 200) {
            $this->AddPage('L', 'Legal');
            $pdf->SetXY(10, 30);
            $pdf->Ln();
            $pdf->Ln();
            $pdf->Ln();
        }
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Ln();
        //ENCARGADO
        $pdf->Cell(190, 5, '', 0, 0, 'C');
        $pdf->Ln();
        $pdf->Cell(190, 5, '', 0, 0, 'C');
        $pdf->Ln();
        $pdf->Cell(190, 5, '', 0, 0, 'C');
        $pdf->Ln();
        // $pdf->SetXY(55, 175);
        $pdf->Cell(190, 5, '........................................................................', 0, 0, 'C');
        $pdf->Cell(100, 5, '........................................................................', 0, 0, 'C');
        // $pdf->SetXY(60, 180);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 10);

        $encargado = $cajaChica->manager;
        $director = $cajaChica->director;

        $pdf->Cell(190, 5, iconv('UTF-8', 'windows-1252', $encargado->nombres . ' ' . $encargado->apellidoPaterno . ' ' . $encargado->apellidoMaterno), 0, 0, 'C');
        // $pdf->Cell(100, 5, 'Maria del Carmen Murillo de Espinoza');
        $pdf->Cell(100, 5, iconv('UTF-8', 'windows-1252', $director->gradoAcademico . ' ' . $director->nombreCompleto), 0, 0, 'C');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(190, 5, 'Responsable de Caja Chica', 0, 0, 'C');

        $genero = substr($director->gradoAcademico, -1);
        $director = '';
        if ($genero === 'o') {
            $director = 'Director';
        } else {
            $director = 'Directora';
        }
        $pdf->Cell(100, 5, iconv('UTF-8', 'windows-1252', $director . ' Carrera Tecnología Médica'), 0, 0, 'C');

        // Salida del PDF
        $pdfContent = $pdf->Output('', 'S');
        return response()->json(['pdfContent' => base64_encode($pdfContent)]);
    }

    public function getMoneyBoxRecopilationExcel($dateOne, $dateTwo, $id)
    {
        //Fechas
        $fechaInicial = Carbon::parse($dateOne);
        $fechaFinal = Carbon::parse($dateTwo);

        $cajaChica = MoneyBox::with('director')->with('manager')->find($id);
        $recharges = Recharge::where('money_box_id', $id)->get();


        $spentsModel = Spent::where('money_boxes_id', $id)->get();
        $spentsRecharges = $spentsModel->concat($recharges)->sortBy('created_at')->values();

        $ultimaRecarga = [];
        if (count($recharges) > 0) {
            $ultimaRecarga = $recharges[count($recharges) - 1];
        } else {
            $ultimaRecarga = ['id' => '0'];
        }

        $montoInicial = $cajaChica->monto;
        $gastoInicial = 1000 - $cajaChica->monto;

        foreach ($spentsRecharges as $spentRecharge) {
            $fechaComparar = Carbon::parse(isset($spentRecharge->fechaCreacion) ? $spentRecharge->fechaCreacion : $spentRecharge->fechaRecarga);

            if ($fechaComparar->between($fechaInicial, $fechaFinal)) {
                break;
            }

            if (isset($spentRecharge->fechaCreacion)) { //gasto
                if ($spentRecharge->ingreso === 'si') {
                    $montoInicial += number_format(($spentRecharge->gasto), 2);
                    $gastoInicial -= number_format($spentRecharge->gasto, 2);
                }
                $montoInicial -= number_format($spentRecharge->gasto, 2);
                $gastoInicial += number_format($spentRecharge->gasto, 2);
            } else { //Reembolso
                $montoInicial += number_format($spentRecharge->montoRecarga);
                $gastoInicial -= number_format($spentRecharge->montoRecarga, 2);
            }
        }

        $montoInicialPrimero = $montoInicial;
        $gastoInicialPrimero = $gastoInicial;

        $fechaInicial->setLocale('Es');
        $fechaFinal->setLocale('Es');
        $fechaMostrarOne = strtoupper($fechaInicial->isoFormat('D MMMM Y'));
        $fechaMostrarTwo = strtoupper($fechaFinal->isoFormat('D MMMM Y'));

        //Gastos que se realizaron entre fechas
        $spents = [];

        foreach ($spentsRecharges as $spentRecharge) {
            $fechaComparar = Carbon::parse(isset($spentRecharge->fechaCreacion) ? $spentRecharge->fechaCreacion : $spentRecharge->fechaRecarga);
            if ($fechaComparar->between($fechaInicial, $fechaFinal)) {
                $spents[] = $spentRecharge;
            }
        }
        //Datos

        $data = [];
        $nroVales = 1;
        $gastoEncontrado = false;
        $gastoPorcentaje = 0;

        foreach ($spents as $spent) {
            $fechaCambiada = Carbon::createFromFormat('Y-m-d', isset($spent->fechaCreacion) ? $spent->fechaCreacion : $spent->fechaRecarga);

            if (isset($spent->fechaCreacion)) { //gasto 

                $montoInicial -= number_format($spent->gasto, 2);
                $gastoInicial += number_format($spent->gasto, 2);

                if ($spent->ingreso === 'si') {
                    $montoInicial += number_format($spent->gasto, 2);
                    $gastoInicial -= number_format($spent->gasto, 2);
                }

                $data[] = [
                    $nroVales,
                    $spent->nro,
                    $fechaCambiada->format('d-m-Y'),
                    iconv('UTF-8', 'windows-1252', $spent->interested),
                    $spent->nroFactura !== '' && $spent->nroFactura !== null ? $spent->nroFactura : 'Sin factura',
                    //iconv('UTF-8', 'windows-1252', $spent->descripcion),
                    // mb_convert_encoding($spent->descripcion, 'Windows-1252', 'UTF-8'),
                    $spent->descripcion,
                    iconv('UTF-8', 'windows-1252', $spent->cantidad),
                    $spent->ingreso === 'no' ? '' : number_format($spent->gasto, 2),
                    number_format($spent->gasto, 2),
                    number_format($montoInicial, 2)
                ];
            } else { //desembolso
                if ($spent->id === $ultimaRecarga->id) {
                    $gastoEncontrado = true;
                }
                $data[] = [
                    $nroVales,
                    '',
                    $fechaCambiada->format('d-m-Y'),
                    '',
                    '',
                    'DESEMBOLSO CAJA CHICA:',
                    '',
                    $spent->montoRecarga,
                    '',
                    number_format(($montoInicial + $spent->montoRecarga), 2)
                ];
                // $gastoInicial -= number_format($spent->montoRecarga, 2);
                $montoInicial += number_format($spent->montoRecarga, 2);
            }
            $nroVales += 1;
        };

        if (!$gastoEncontrado) { // No hay recargas
            $gastoPorcentaje = $gastoInicial;
        } else {
            $gastoEncontrado = false;
            foreach ($spents as $spent) {
                $fechaCambiada = Carbon::createFromFormat('Y-m-d', isset($spent->fechaCreacion) ? $spent->fechaCreacion : $spent->fechaRecarga);

                if (isset($spent->fechaCreacion)) { //gasto 
                    if ($gastoEncontrado) {
                        $gastoPorcentaje += number_format($spent->gasto, 2);
                    }
                } else { //desembolso
                    if ($spent->id === $ultimaRecarga->id) {
                        $gastoEncontrado = true;
                    }
                }
            }
        }

        $porcentajeGastos = ($gastoPorcentaje / 1000) * 100;
        $saldoSobrante = 1000 - $gastoPorcentaje;
        $porcentajeSobrante = ($saldoSobrante / 1000) * 100;
        $ultimaInformacion =
            [
                array('DETALLE', 'IMPORTE', '%'),
                array('TOTAL CAJA CHICA ASIGNADO Bs.', '1000', '100%'),
                array('TOTAL GASTO Bs.', $gastoPorcentaje, (string)$porcentajeGastos . '%'),
                array('SALDO DISPONIBLE', $saldoSobrante, (string)$porcentajeSobrante . '%')
            ];
        // for ($i = 0; $i < 22; $i++){ 
        //         $data[] = ['1', 'Dato ', 'Dato ', Str::random(rand(1, 250)), 'Dato 1', 'Dato 2','Dato','Dato'];
        //     };

        $encargado = $cajaChica->manager;
        $director = $cajaChica->director;
        $nombreDirector = $director->gradoAcademico . ' ' . $director->nombreCompleto;
        $genero = substr($director->gradoAcademico, -1);
        $director = '';
        if ($genero === 'o') {
            $director = 'Director';
        } else {
            $director = 'Directora';
        }

        // return (new FastExcel($data))->download('users.xlsx');


        array_unshift($data, ['', '', '', '', '', 'SALDO Y GASTO (ANTES DE FECHAS)', '', '', (string)number_format($gastoInicialPrimero, 2), (string)number_format($montoInicialPrimero, 2)]);

        array_unshift($data, ['Nro', 'Nro de Cbtes.', 'Fecha', 'Nombre beneficiario', 'Factura', 'Concepto del gasto', 'Cantidad', 'Ingreso', 'Gasto', 'Saldo']);

        array_push($data, ['', '', '', '', '', 'SALDO Y GASTO (DESPUES DE FECHAS)', '', '', (string)number_format($gastoInicial, 2), (string)number_format($montoInicial, 2)]);

        $excelFile = Excel::raw(new MoneyBoxExport(
            $data,
            $ultimaInformacion,
            $encargado->nombres . ' ' . $encargado->apellidoPaterno . ' ' . $encargado->apellidoMaterno,
            $nombreDirector,
            $director . ' Carrera Tecnología Médica',
            'UNIVERSIDAD MAYOR DE SAN ANDRÉS FACULTAD DE MEDICINA, ENFERMERÍA, NUTRICIÓN Y TECNOLOGÍA MÉDICA' . "\n" . 'CARRERA DE TECNOLOGÍA MÉDICA' . "\n" . 'DETALLE DE GASTOS DE CAJA CHICA CARRERA DE TECNOLOGÍA MÉDICA' . "\n" . 'PERIODO: DEL ' . $fechaMostrarOne . ' AL ' . $fechaMostrarTwo
        ), ExcelWriter::XLSX);



        return response()->json(['fileContent' => base64_encode($excelFile)])->header('Content-Type', 'application/json; charset=UTF-8');
    }

    public function editMoneyBox($id, Request $request)
    {
        $money_box = MoneyBox::find($id);

        $money_box->nombre = $request->nombre;
        $money_box->monto = $request->monto;
        $money_box->user_id = $request->user_id;

        $money_box->save();

        return [
            'message' => 'Caja chica editada correctamente'
        ];
    }

    public function selectManager($id, Request $request)
    {
        $money_box = MoneyBox::find($id);
        $money_box->user_id = $request->user_id;
        $money_box->save();
        return [
            'message' => 'Encargado seleccionado'
        ];
    }

    public function selectDirector($id, Request $request)
    {
        $money_box = MoneyBox::find($id);
        $money_box->director_teacher_id = $request->user_id;
        $money_box->save();
        return [
            'message' => 'Director seleccionado'
        ];
    }
}

class PDF_MC_Table extends FPDF
{
    protected $widths;
    protected $aligns;

    function SetWidths($w)
    {
        // Set the array of column widths
        $this->widths = $w;
    }

    function SetAligns($a)
    {
        // Set the array of column alignments
        $this->aligns = $a;
    }

    function Row($data)
    {
        // Calculate the height of the row
        $nb = 0;
        for ($i = 0; $i < count($data); $i++)
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        $h = 5 * $nb;
        // Issue a page break first if needed
        $this->CheckPageBreak($h);
        // Draw the cells of the row
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            // Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            // Draw the border
            $this->Rect($x, $y, $w, $h);
            // Print the text
            $this->MultiCell($w, 5, $data[$i], 0, $a);
            // Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        // Go to the next line
        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        // If the height h would cause an overflow, add a new page immediately
        if ($this->GetY() + $h > $this->PageBreakTrigger)
            // $this->AddPage($this->CurOrientation);
            $this->AddPage('L', 'Legal');
    }

    function NbLines($w, $txt)
    {
        // Compute the number of lines a MultiCell of width w will take
        if (!isset($this->CurrentFont))
            $this->Error('No font has been set');
        $cw = $this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', (string)$txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }
}
