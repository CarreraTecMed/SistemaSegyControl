<?php

namespace App\Http\Controllers;

use App\Events\NewResponseEvent;
use App\Http\Requests\SpentRequest;
use App\Http\Resources\SpentCollection;
use App\Models\MoneyBox;
use App\Models\Spent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpentController extends Controller
{
    public function getSpents()
    {
        return new SpentCollection(Spent::with('money_box')->get());
    }

    public function getSpentId($id)
    {
        return Spent::with('money_box')->find($id);
    }

    public function getUltimateSpent($id)
    {
        $spents = Spent::with('money_box')->where('money_boxes_id',$id)->orderBy('nro')->get();
       
        $nro = 1;
        if (count($spents) > 0) {
            $lastSpent = $spents->last();
            $nro = $lastSpent->nro + 1;
        }

        $nroBuscador = 1;

        foreach($spents as $spent) {
            if ($spent->nro !== (string)$nroBuscador) {
                $nro = $nroBuscador;
                break;
            }
            $nroBuscador += 1;
        }

        return [
            'nro' => strval($nro)
        ];
    }

    public function createSpent(SpentRequest $request)
    {
        DB::beginTransaction();

        try {
            $moneyBox = MoneyBox::find(1);
            $data = $request->validated();

            // if ($moneyBox->monto < $request->gasto) {
            //     return response([
            //         'errors' => ['El gasto excede a el dinero sobrante en la caja chica']
            //     ], 422);
            // }

            $spent = Spent::create([
                "money_boxes_id" => $request['idMoneyBox'],
                "nro" => $data['nro'],
                "fechaCreacion" => Carbon::now(),
                "nroFactura" => $data['nroFactura'] === 'Sin factura' ? '' : $data['nroFactura'],
                "descripcion" => $data['descripcion'],
                "cantidad" => $data['cantidad'],
                "gasto" => $data['gasto'],
                "interested" => $data['custodio'],
                "ingreso" => $request['ingreso']
            ]);

            // $moneyBox->monto = $moneyBox->monto - $spent->gasto;
            $moneyBox->save();
            DB::commit();
            return [
                "message" => "Gasto creado correctamente",
                "gasto" => $spent
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            return response([
                'errors' => ['Ocurrio algo inesperado con el servidor: ' . $th->getMessage()]
            ], 422);
        }
    }

    public function editSpent(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                "gasto" => ["required", "numeric", "regex:/^[\d]{0,8}(\.[\d]{1,2})?$/",'min:1'],
                "nro" => ["required", "string"],
                "nroFactura" => ["required", "string"],
                "custodio" => ["required"],
                "descripcion" => ["required", "string"],
                "cantidad" => ['required']
            ], [
                "nro.required" => "El nro de vale es obligatorio",
                "nro.unique" => "Ya existe un numero de vale con ese registro",
                "nro.string" => "El nro de vale debe de ser una cadena de caracteres",
                "cantidad" => "La cantidad es obligatoria",
                "nroFactura.required" => "El nro de factura es obligatorio",
                "nroFactura.unique" => "Ya existe un numero de factura con ese registro",
                "nroFactura.string" => "El nro de factura debe de ser una cadena de caracteres",
                "descripcion.required" => "La descripcion es obligatoria",
                "descripcion.string" => "La descripcion debe de ser una cadena de caracteres",
                "gasto.required" => "El gasto es obligatorio",
                "gasto.numeric" => "El gasto debe de ser un numero",
                "gast.min" => "El gasto no puede ser de 0 Bs.",
                "gasto.regex" => "El gasto no tiene el formato correcto",
                "custodio" => "El custodio es requerido",
            ]);

            $moneyBox = MoneyBox::find(1);
            // if ($moneyBox->monto < $request->gasto) {
            //     return response([
            //         'errors' => ['El gasto excede a el dinero sobrante en la caja chica']
            //     ], 422);
            // }

            $spent = Spent::find($id);

            $spent->descripcion = $request->descripcion;
            $spent->interested = $request->custodio;
            // if ($spent->gasto > $request->gasto) {
            //     $moneyBox->monto += abs($request->gasto-$spent->gasto);
            // }else {
            //     $moneyBox->monto -= abs($request->gasto-$spent->gasto);
            // }

            $spent->gasto = $request->gasto;
            $spent->nroFactura = null;

            if ($request->nroFactura !== 'Sin factura') {
                $spent->nroFactura = $request->nroFactura;
            }

            if ($request->ingreso === 'si' && $spent->ingreso === 'no') {
                $moneyBox->monto += $request->gasto;
            } else if ($request->ingreso === 'no' && $spent->ingreso === 'si') {
                $moneyBox->monto -= $request->gasto;
            }

            $spent->nro = $request->nro;
            $spent->cantidad = $request->cantidad;
            $spent->ingreso = $request->ingreso;

            $spent->save();
            $moneyBox->save();
            DB::commit();
            return [
                "message" => "Gasto editado correctamente"
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            return response([
                'errors' => ['Ocurrio algo inesperado con el servidor: ' . $th->getMessage()]
            ], 422);
        }
    }

    public function deleteSpent($id)
    {
        DB::beginTransaction();
        try {
            $moneyBox = MoneyBox::find(1);
            $spent = Spent::find($id);
            // $moneyBox->monto = $spent->gasto + $moneyBox->monto;
            $moneyBox->save();
            $spent->delete();

            DB::commit();
            return [
                "message" => "Gasto eliminado correctamente"
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            return response([
                'errors' => ['Ocurrio algo inesperado con el servidor: ' . $th->getMessage()]
            ], 422);
        }
    }
}
