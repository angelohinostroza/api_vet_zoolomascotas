<?php

namespace App\Http\Controllers\Owners;

use App\Http\Controllers\Controller;
use App\Models\Pets\Owner;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

##########################
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        //
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
