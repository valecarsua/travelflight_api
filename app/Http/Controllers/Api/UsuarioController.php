<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
/**
 * @OA\Info(
 *     title="API del proyecto",
 *     version="1.0.0",
 *     description="Este documento muestra los diferentes endpoints y sus configuraciones con el fin de ser consumidos por un cliente",
 *     @OA\Contact(
 *         email="soporte@miempresa.com"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 */

class UsuarioController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/usuarios",
     *     summary="Obtener un listado de los usuarios registrados en la aplicación",
     *     description="Este endpoint devuelve todos los usuarios registrados en el sistema.",
     *     @OA\Response(
     *         response=200,
     *         description="Operación exitosa",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nombre", type="string", example="Juan"),
     *                 @OA\Property(property="apellido", type="string", example="Pérez"),
     *                 @OA\Property(property="cedula", type="string", example="1234567890"),
     *                 @OA\Property(property="correo", type="string", example="juan@correo.com"),
     *                 @OA\Property(property="estado", type="string", example="activo")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No hay usuarios disponibles",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="string", example="No hay registros disponibles")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $usuarios = Usuario::all();

        if($usuarios->count() > 0){
            return response()->json([
                'status' => 200,
                'data' => $usuarios
            ], 200);
        }
        else{
            return response()->json([
                'status' => 404,
                'data' => 'No hay registros disponibles'
            ],404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Autenticar usuario",
     *     description="Este endpoint valida las credenciales de correo y contraseña para iniciar sesión.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"correo", "password"},
     *                 @OA\Property(property="correo", type="string", example="juan@correo.com"),
     *                 @OA\Property(property="password", type="string", example="password123")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login exitoso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciales incorrectas"
     *     )
     * )
     */
    public function login(Request $request)
    {
        // Validación de datos
        $validator = Validator::make($request->all(), [
            'correo' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Intentar autenticar al usuario
        $usuario = Usuario::where('correo', $request->correo)->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            return response()->json(['error' => 'Credenciales incorrectas'], 401);
        }

        // Crear un token (se asume que ya estás utilizando Laravel Passport o Sanctum)
        $token = $usuario->createToken('Token de acceso')->plainTextToken;

        return response()->json(['token' => $token], 200);
    }


    /**
     * @OA\Post(
     *     path="/api/usuarios",
     *     summary="Crear un nuevo usuario",
     *     description="Este endpoint permite crear un nuevo usuario en el sistema.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"nombre", "apellido", "cedula", "correo", "password", "estado"},
     *             @OA\Property(property="nombre", type="string", example="Juan"),
     *             @OA\Property(property="apellido", type="string", example="Pérez"),
     *             @OA\Property(property="cedula", type="string", example="1234567890"),
     *             @OA\Property(property="correo", type="string", example="juan@correo.com"),
     *             @OA\Property(property="password", type="string", example="contraseña123"),
     *             @OA\Property(property="estado", type="string", example="activo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuario creado exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer", example=201),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nombre", type="string", example="Juan"),
     *                 @OA\Property(property="apellido", type="string", example="Pérez"),
     *                 @OA\Property(property="cedula", type="string", example="1234567890"),
     *                 @OA\Property(property="correo", type="string", example="juan@correo.com"),
     *                 @OA\Property(property="estado", type="string", example="activo")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la validación de los datos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="error", type="string", example="El campo 'nombre' es obligatorio.")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'nombre' => 'required|string|max:200',
            'apellido' => 'required|string|max:200',
            'cedula' => 'required|string|max:200',
            'correo' => 'required|email|string|max:200',
            'password' => 'required|string|max:200',
            'estado' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->messages()
            ]);
        }
        else{
            $usuario = Usuario::create([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'cedula' => $request->cedula,
                'correo' => $request->correo,
                'password' => Hash::make($request->password),
                'estado' => $request->estado,
            ]);

            if($usuario){
                return response()->json([
                    'status' => 200,
                    'message' => 'Usuario registrado correctamente'
                ], 200);
            }else{
                return response()->json([
                    'status' => 500,
                    'message' => 'El Usuario no pudo ser registrado'
                ], 500);
            }
        }
    }
    public function show(string $id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            $data = [
                'status' => 404,
                'message' => 'Usuario no encontrado',
            ];
            return response()->json($data, 404);
        }

        $data = [
            'status' => 200,
            'usuario' => $usuario
        ];

        return response()->json($data, 200);
    }

    /**
     * @OA\Put(
     *     path="/api/usuarios/editar/{id}",
     *     summary="Actualizar un usuario",
     *     description="Este endpoint permite actualizar los datos de un usuario existente.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del usuario",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre", "apellido", "cedula", "correo", "password", "estado"},
     *             @OA\Property(property="nombre", type="string", example="Carlos"),
     *             @OA\Property(property="apellido", type="string", example="Sánchez"),
     *             @OA\Property(property="cedula", type="string", example="987654321"),
     *             @OA\Property(property="correo", type="string", example="carlos@correo.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="estado", type="string", example="activo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario actualizado exitosamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario no encontrado"
     *     )
     * )
     */

    public function update(Request $request, string $id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            $data = [
                'status' => 404,
                'message' => 'Usuario no encontrado',
            ];
            return response()->json($data, 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:200',
            'apellido' => 'required|string|max:200',
            'cedula' => 'required|string|max:200',
            'correo' => 'required|email|string|max:200',
            'password' => 'required|string|max:200',
            'estado' => 'required'
        ]);

        if ($validator->fails()) {
            $data = [
                'status' => 400,
                'message' => 'Error en la validación de los datos',
                'errors' => $validator->errors(),
            ];
            return response()->json($data, 400);
        }

        $usuario->nombre = $request->nombre;
        $usuario->apellido = $request->apellido;
        $usuario->cedula = $request->cedula;
        $usuario->correo = $request->correo;
        $usuario->password = $request->password;
        $usuario->estado = $request->estado;

        $usuario->save();

        $data = [
            'status' => 200,
            'message' => 'Usuario actualizado',
            'usuario' => $usuario,
            
        ];

        return response()->json($data, 200);
    }

    /**
     * @OA\Patch(
     *     path="/api/usuarios/editar-parcial/{id}",
     *     summary="Actualizar parcialmente un usuario",
     *     description="Este endpoint permite actualizar parcialmente los datos de un usuario existente.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del usuario",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nombre", type="string", example="Carlos"),
     *             @OA\Property(property="estado", type="string", example="inactivo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario actualizado parcialmente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario no encontrado"
     *     )
     * )
     */
    public function updatePartial(Request $request, $id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            $data = [
                'status' => 404,
                'message' => 'Usuario no encontrado'
            ];
            return response()->json($data, 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'max:200',
            'apellido' => 'max:200',
            'cedula' => 'max:200',
            'correo' => 'email|max:200',
            'password' => 'max:200',
            'estado' => ''
        ]);

        if ($validator->fails()) {
            $data = [
                'status' => 400,
                'message' => 'Error en la validación de los datos',
                'errors' => $validator->errors(),    
            ];
            return response()->json($data, 400);
        }

        if ($request->has('nombre')) {
            $usuario->nombre = $request->nombre;
        }

        if ($request->has('apellido')) {
            $usuario->apellido = $request->apellido;
        }

        if ($request->has('cedula')) {
            $usuario->cedula = $request->cedula;
        }
        if ($request->has('correo')) {
            $usuario->correo = $request->correo;
        }
        if ($request->has('password')) {
            $usuario->password = $request->password;
        }
        if ($request->has('estado')) {
            $usuario->estado = $request->estado;
        }

        $usuario->save();

        $data = [
            'status' => 200,
            'message' => 'Usuario actualizado',
            'student' => $usuario,
            
        ];

        return response()->json($data, 200);
    }

     /**
     * @OA\Delete(
     *     path="/api/usuarios/{id}",
     *     summary="Eliminar un usuario",
     *     description="Este endpoint elimina un usuario del sistema.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del usuario",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario eliminado exitosamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario no encontrado"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            $data = [
                'status' => 404,
                'message' => 'Usuario no encontrado',
            ];
            return response()->json($data, 404);
        }
        
        $usuario->delete();

        $data = [
            'message' => 'Usuario eliminado',
            'status' => 200
        ];

        return response()->json($data, 200);
    }
}
