<?php

namespace App\Http\Controllers\Owners;

use App\Http\Controllers\Controller;
use App\Models\Pets\Owner;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

##########################
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Exception;

class OwnerController extends Controller
{

    // #TODO funcion para realizar el login desde el aplicativo
    public function loginOwnerApp(Request $request)
    {

        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $owner = Owner::where('email', $request->email)->first();

            if (!$owner || !Hash::check($request->password, $owner->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            // 🔹 Especificar el guard correcto
            Auth::shouldUse('owner-api');

            return response()->json([
                'token' => $owner->createToken('auth-token')->plainTextToken, // GENERAMOS API TOKEN
                'owner' => $owner->makeHidden(['password', 'remember_token']), //Ocultamos la contraseña para no enviarla al App
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error en el servidor: ' . $e->getMessage(),
            ], 500);
        }
    }

    #TODO funcion para obtener las mascotas del dueño
    public static function getOwnerPets($ownerId)
    {
        try {
            //Buscamos a las mascotas del dueño
            $owner = Owner::with('pet')->find($ownerId);
            if (!$owner) {
                return response()->json(['message' => 'Owner not found'], 404);
            }
            return response()->json($owner->pet);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Owner not found'], 404);

        } catch (Exception $e) {
            return response()->json(["message" => "Error al buscar la mascota",
                "error" => $e->getMessage()], 500);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $owners = Owner::withTrashed()->OrderBy('created_at','desc')->get();
            return response()->json([
                'message' => 'Listado Completo de Dueños',
                'data' => $owners], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al obtener los dueños', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $owner = Owner::create($request->all());
            return response()->json(['message' => 'Dueño creado correctamente', 'data' => $owner], 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al crear el dueño', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $owner = Owner::findOrFail($id);
            return response()->json([
                'message' => 'Dueño encontrado correctamente',
                'data' => $owner], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Dueño no encontrado'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al obtener el dueño', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $owner = Owner::findOrFail($id);
            $owner->update($request->all());
            return response()->json(['message' => 'Dueño actualizado correctamente', 'data' => $owner], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Dueño no encontrado'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al actualizar el dueño', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $owner = Owner::findOrFail($id);
            $owner->delete();
            return response()->json(['message' => 'Dueño eliminado correctamente', 'data' => $owner], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Dueño no encontrado'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al eliminar el dueño', 'message' => $e->getMessage()], 500);
        }
    }
    public function toggleActive($id)
    {
        $owner = Owner::withTrashed()->find($id);

        if (!$owner) {
            return response()->json(['message' => 'Dueño no encontrado'], 404);
        }

        if ($owner->deleted_at) {
            // Si está eliminado, lo activamos (colocamos deleted_at en null)
            $owner->restore();
            return response()->json(['message' => 'Dueño activado con éxito', 'data' => $owner]);
        } else {
            // Si está activo, lo desactivamos (registramos la fecha actual en deleted_at)
            $owner->delete();
            return response()->json(['message' => 'Dueño desactivado con éxito', 'data' => $owner]);
        }
    }
    public function searchOwners(Request $request)
    {
        try {
            // Obtener los parámetros de la solicitud
            $query = $request->input('query'); // Cadena de búsqueda
            // Validar que el parámetro no esté vacío
            if (!$query) {
                return response()->json([
                    'success' => false,
                    'message' => 'Por favor, ingresa un término de búsqueda',
                ], 400);
            }

            // Realizar la búsqueda en campos de texto
            $owners = Owner::where(function ($q) use ($query) {
                $q->where('names', 'LIKE', "%{$query}%")
                    ->orWhere('surnames', 'LIKE', "%{$query}%")
                    ->orWhere('email', 'LIKE', "%{$query}%")
                    ->orWhere('phone', 'LIKE', "%{$query}%")
                    ->orWhere('address', 'LIKE', "%{$query}%")
                    ->orWhere('city', 'LIKE', "%{$query}%")
                    ->orWhere('emergency_contact', 'LIKE', "%{$query}%");

                // Incluir campos numéricos (convertidos a texto)
                $q->orWhereRaw("CAST(n_document AS TEXT) LIKE ?", ["%{$query}%"]);
            })->get();

            return response()->json([
                'success' => true,
                'data' => $owners,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al buscar dueños:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al realizar la búsqueda',
            ], 500);
        }
    }


}
