<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollaboratorCorrespondenceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        
        $documentos = [];

        foreach ($this->correspondence->documents as $documento ) {
            $documentoEditado = [
                "id" => $documento->id,
                "documento" =>  asset('/storage/documentos/'.$documento->nombreDocumento),
                "fechaSubida" => $documento->fechaSubida, 
                'usuario_creador' => $documento->user_id
            ];
            $documentos[]=$documentoEditado;
        };

        return [
            'id' => $this->id,
            'usuario_creador_id' => $this->primary_user_id,
            'correspondencia' => [
                'id' => $this->correspondence->id,
                'nombre' => $this->correspondence->nombre,
                'fechaCreacion' => $this->correspondence->fechaCreacion,
                'descripcion' => $this->correspondence->descripcion,
            ],
            'documentos' => $documentos
        ];
    }
}
