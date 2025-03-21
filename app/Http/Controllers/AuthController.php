<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Validator;
use Illuminate\Support\Facades\Gate;

class AuthController extends Controller
{

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register()
    {

        Gate::authorize('create', User::class);

        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = new User;
        $user->name = request()->name;
        $user->email = request()->email;
        $user->password = bcrypt(request()->password);
        $user->save();

        return response()->json($user, 201);
    }


    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Email o contraseña incorrecta, intenta nuevamente'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth('api')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $permissions = auth('api')->user()->getAllPermissions()->map(function ($permission) {
            return $permission->name;
        });
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            "user" => [
                "name" => auth('api')->user()->name,
                "surname" => auth('api')->user()->surname,
                "email" => auth('api')->user()->email,
                "avatar" => auth('api')->user()->avatar ? env("APP_URL") . "storage/" . auth('api')->user()->avatar : null,
                "role" => auth('api')->user()->role,
                "permissions" => $permissions,
            ]
        ]);
    }

    // #TODO funcion para realizar el login desde el aplicativo
    public function loginUserApp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
            // bcrypt
            $user = User::where('email', $request->email)->first();

//            if (!$user) {
//                Log::warning('Intento de login con email no registrado:', ['email' => $request->email]);
//            } else {
//                Log::info('Usuario encontrado:', ['user' => $user->toArray()]);
//            }


            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            // 🔹 Especificar el guard correcto
            Auth::shouldUse('user-api');

            $response = [
                'token' => $user->createToken('auth-token')->plainTextToken, // GENERAMOS API TOKEN
                'user' => $user->makeHidden(['password', 'remember_token']), //Ocultamos la contraseña para no enviarla al App
                'role' => $user->role ? $user->role->name : 'SinRol', //enviamos el rol del usuario
            ];
            // 🔹 Imprimir la respuesta antes de devolverla
            //Log::info('Respuesta de login:', $response); // 🔹 Registra la respuesta
            return response()->json($response, 200);

        } catch (\Exception $e) {
            Log::error('Error en login:', ['error' => $e->getMessage()]); // 🔹 Registra el error

            return response()->json([
                'message' => 'Error en el servidor: ' . $e->getMessage(),
            ], 500);
        }
    }

    #Funcion para el dashboard del superAdmin
    public function getUsersForSuperAdmin(Request $request)
    {
        try {
            $users = User::with('role') // Cargar la relación con roles
            ->withTrashed()
                ->select('id', 'name', 'surname', 'email', 'phone', 'role_id', 'avatar', 'created_at', 'deleted_at')
                ->get();

            return response()->json([
                'users' => $users
            ]);
        } catch (\Exception $e) {
            Log::error('Error en login:', ['error' => $e->getMessage()]); // 🔹 Registra el error

            return response()->json([
                'message' => 'Error en el servidor: ' . $e->getMessage(),
            ], 500);
        }
    }

    #Funcion para actualizar usuarios
    //
    public static function updateUser(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            //  Validación de datos para evitar entradas no deseadas
            $validatedData = $request->validate([
                'name' => 'sometimes|string|max:255',
                'surname' => 'sometimes|string|max:255',
                'phone' => 'sometimes|nullable|string|max:20',
                'role_id' => 'sometimes|exists:roles,id',
            ]);

            $user->update($validatedData);
            return response()->json([
                'message' => 'Usuario actualizado correctamente',
                'user' => $user, // Devuelve los datos actualizados
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar usuario updateUser:', ['error' => $e->getMessage()]);


            return response()->json([
                'message' => 'Error en el servidor: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroyUser($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete(); // Marca el usuario como eliminado

            return response()->json([
                'message' => 'Usuario eliminado correctamente',
                'user' => $user, // Devuelve el usuario actualizado
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar usuario destroyUser:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error en el servidor: ' . $e->getMessage(),
            ], 500);
        }
    }

}
