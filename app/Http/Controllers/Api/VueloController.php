<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;


class VueloController extends Controller
{
    

    /**
     * @OA\Post(
     *     path="/api/vuelo",
     *     summary="Obtener información sobre un vuelo",
     *     description="Este endpoint permite obtener los vuelos disponibles según los parámetros proporcionados.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"direct", "currency", "searchs", "class", "qtyPassengers", "adult", "child", "baby", "seat", "itinerary"},
     *             @OA\Property(property="direct", type="boolean", example=true),
     *             @OA\Property(property="currency", type="string", example="USD"),
     *             @OA\Property(property="searchs", type="integer", example=10),
     *             @OA\Property(property="class", type="boolean", example=true),
     *             @OA\Property(property="qtyPassengers", type="integer", example=2),
     *             @OA\Property(property="adult", type="integer", example=2),
     *             @OA\Property(property="child", type="integer", example=0),
     *             @OA\Property(property="baby", type="integer", example=0),
     *             @OA\Property(property="seat", type="integer", example=1),
     *             @OA\Property(
     *                 property="itinerary", 
     *                 type="array", 
     *                 @OA\Items(
     *                     type="object",
     *                     required={"departureCity", "arrivalCity", "hour"},
     *                     @OA\Property(property="departureCity", type="string", example="BOG"),
     *                     @OA\Property(property="arrivalCity", type="string", example="MDE"),
     *                     @OA\Property(property="hour", type="string", example="2024-12-20T10:30:00.000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Operación exitosa, se retornan los vuelos disponibles.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="data", type="array", 
     *                 @OA\Items(type="object", 
     *                     @OA\Property(property="flight", type="string", example="Vuelo1234"),
     *                     @OA\Property(property="departure", type="string", example="BOG"),
     *                     @OA\Property(property="arrival", type="string", example="MDE")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validación fallida de los parámetros de entrada",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer", example=422),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="direct", type="string", example="El campo direct es obligatorio."),
     *                 @OA\Property(property="currency", type="string", example="El campo currency es obligatorio.")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="error", type="string", example="Error al procesar la solicitud.")
     *         )
     *     )
     * )
     */
    public function obtenerVuelo(Request $request)
    {
        try {
            $request->validate([
                'direct' => 'required|boolean',
                'currency' => 'required|string|max:255',
                'searchs' => 'required|integer',
                'class' => 'required|boolean',
                'qtyPassengers' => 'required|integer|min:1',
                'adult' => 'required|integer',
                'child' => 'required|integer',
                'baby' => 'required|integer',
                'seat' => 'required|integer',
                'itinerary' => 'required|array',
                'itinerary.*.departureCity' => 'required|string|size:3', // Validación de código de ciudad (3 caracteres)
                'itinerary.*.arrivalCity' => 'required|string|size:3', // Validación de código de ciudad (3 caracteres)
                'itinerary.*.hour' => 'required|date_format:Y-m-d\TH:i:s.000\Z', // Validación de hora en el formato ISO 8601
            ]);

            // Solicitud a la API externa
            // Preparar los datos del itinerario para enviar a la API externa
            $itinerary = [];
            foreach ($request->input('itinerary') as $item) {
                $itinerary[] = [
                    'departureCity' => $item['departureCity'],
                    'arrivalCity' => $item['arrivalCity'],
                    'hour' => $item['hour'],
                ];
            }

            // Solicitud a la API externa con los datos del itinerario y otros parámetros
            $response = Http::post('https://staging.travelflight.aiop.com.co/api/flights/v2', [
                'code' => $request->input('code'),
                'direct' => $request->input('direct'),
                'currency' => $request->input('currency'),
                'searchs' => $request->input('searchs'),
                'class' => $request->input('class'),
                'qtyPassengers' => $request->input('qtyPassengers'),
                'adult' => $request->input('adult'),
                'child' => $request->input('child'),
                'baby' => $request->input('baby'),
                'seat' => $request->input('seat'),
                'itinerary' => $itinerary, // Agregamos el itinerario al cuerpo de la solicitud
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (empty($data)) {
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
