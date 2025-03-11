<?php

namespace App\Http\Controllers\Owners;

use App\Http\Controllers\Controller;
use App\Models\Pets\Owner;
use Illuminate\Http\Request;
##########################
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class OwnerController extends Controller
{

    public function login(Request $request)
    {

       try{
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
               'owner' => $owner->makeHidden(['password','remember_token']), //Ocultamos la contraseña para no enviarla al App
           ]);
       }
       catch (\Exception $e) {
           return response()->json([
               'message' => 'Error en el servidor: ' . $e->getMessage(),
           ], 500);
       }
    }

    #TODO funcion para obtener las mascotas del dueño
    public static function getOwnerPets($ownerId){
        $owner = Owner::with('pet')->find($ownerId);
        if(!$owner){
            return response()->json(['message'=> 'Owner not found'],404);
        }
        return response()->json($owner->pet);
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
