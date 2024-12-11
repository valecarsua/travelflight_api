<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;

class AeropuertoController extends Controller
{
    

    /**
     * @OA\Post(
     *     path="/api/aeropuerto",
     *     summary="Obtener información sobre un aeropuerto",
     *     description="Este endpoint permite obtener los detalles de un aeropuerto o ciudad a partir de un código IATA.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"code"},
     *             @OA\Property(property="code", type="string", example="JFK", description="Código IATA del aeropuerto o ciudad.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Operación exitosa",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="airports", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="cities", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No se encontraron datos para el código ingresado",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="No se encontraron datos para el código ingresado.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error al procesar la solicitud o consultar la API externa",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error al procesar la solicitud.")
     *         )
     *     )
     * )
     */
    public function obtenerAeropuerto(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string|max:255',
            ]);

            // Solicitud a la API externa
            $response = Http::post('https://staging.travelflight.aiop.com.co/api/airports/v2', [
                'code' => $request->input('code')
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (empty($data['airports']) && empty($data['cities'])) {
                    return response()->json(['error' => 'No se encontraron datos para el código ingresado.'], 404);
                }

                return response()->json($data);
            }

            return response()->json(['error' => 'Error al consultar la API externa.'], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al procesar la solicitud.'], 500);
        }
    }
}
