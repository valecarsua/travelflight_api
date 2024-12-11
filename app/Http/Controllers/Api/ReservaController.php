<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ReservaController extends Controller
{
    public function index()
    {
        $reservas = Reserva::all();

        if ($reservas->count() > 0) {
            return response()->json([
                'status' => 200,
                'data' => $reservas
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'data' => 'No hay registros disponibles'
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origen' => 'required|string|max:200',
            'destino' => 'required|string|max:200',
            'fecha' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages()
            ]);
        } else {
            $reserva = Reserva::create([
                'origen' => $request->origen,
                'destino' => $request->destino,
                'fecha' => $request->fecha,
            ]);

            if ($reserva) {
                return response()->json([
                    'status' => 200,
                    'message' => 'reserva registrada correctamente'
                ], 200);
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => 'La reserva no pudo ser registrada'
                ], 500);
            }
        }
    }

    public function show(string $id)
    {
        $reserva = Reserva::find($id);

        if (!$reserva) {
            $data = [
                'status' => 404,
                'message' => 'Reserva no encontrado',
            ];
            return response()->json($data, 404);
        }

        $data = [
            'status' => 200,
            'reserva' => $reserva
        ];

        return response()->json($data, 200);
    }

    public function update(Request $request, string $id)
    {
        $reserva = Reserva::find($id);

        if (!$reserva) {
            $data = [
                'status' => 404,
                'message' => 'Reserva no encontrado',
            ];
            return response()->json($data, 404);
        }

        $validator = Validator::make($request->all(), [
            'origen' => 'required|string|max:200',
            'destino' => 'required|string|max:200',
            'fecha' => 'required',
        ]);

        if ($validator->fails()) {
            $data = [
                'status' => 400,
                'message' => 'Error en la validación de los datos',
                'errors' => $validator->errors(),
            ];
            return response()->json($data, 400);
        }

        $reserva->origen = $request->origen;
        $reserva->destino = $request->destino;
        $reserva->fecha = $request->fecha;


        $reserva->save();

        $data = [
            'status' => 200,
            'message' => 'reserva actualizado',
            'reserva' => $reserva,

        ];

        return response()->json($data, 200);
    }

    public function destroy(string $id)
    {
        $reserva = reserva::find($id);

        if (!$reserva) {
            $data = [
                'status' => 404,
                'message' => 'reserva no encontrado',
            ];
            return response()->json($data, 404);
        }

        $reserva->delete();

        $data = [
            'message' => 'reserva eliminado',
            'status' => 200
        ];

        return response()->json($data, 200);
    }
}
