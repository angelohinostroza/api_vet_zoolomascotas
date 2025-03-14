<?php

namespace App\Http\Controllers\Pets;

use App\Models\Pets\Pet;
use App\Models\Pets\Owner;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Pets\PetResource;
use App\Http\Resources\Pets\PetCollection;
use PhpOffice\PhpSpreadsheet\Exception;

class PetsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Pet::class);
        $search = $request->get("search");
        $species = $request->get("species");

        $pets = Pet::where(function ($q) use ($search, $species) {
            if ($species) {
                $q->where("specie", $species);
            }
            if ($search) {
                $q->whereHas("owner", function ($q) use ($search) {
                    $q->Where(DB::raw("pets.name || ' ' || owners.names || ' ' || COALESCE(owners.surnames,'') || ' ' || owners.phone
                                                || ' ' || owners.n_document"), "ilike", "%" . $search . "%");
                });
            }
        })
            ->orderBy("id", "desc")->paginate(4);

        return response()->json([
            "total_page" => $pets->lastPage(),
            "pets" => PetCollection::make($pets),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Pet::class);
        if ($request->hasFile("imagen")) {
            $path = Storage::putFile("pets", $request->file("imagen"));
            $request->request->add(["avatar" => $path]);
        }

        $owner = Owner::create([
            'names' => $request->names,
            'surnames' => $request->surnames,
            'type_document' => $request->type_document,
            'n_document' => $request->n_document,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'emergency_contact' => $request->emergency_contact,
//            #TODO Agregamos el nuevo cammpo password, sera el numero de documento encriptado
            'password' => Hash::make( $request->n_document),
        ]);

        $request->request->add([
            "owner_id" => $owner->id
        ]);

        $pet = Pet::create($request->all());

        return response()->json([
            "message" => 200,
            "message_text" => "La mascota se ha creado correctamente"
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        Gate::authorize('view', Pet::class);
        $pet = Pet::findOrfail($id);
        return response()->json([
            "pet" => PetResource::make($pet)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        Gate::authorize('update', Pet::class);
        $pet = Pet::findOrFail($id);
        if ($request->hasFile("imagen")) {
            if ($pet->avatar) {
                Storage::delete($pet->avatar);
            }
            $path = Storage::putFile("pets", $request->file("imagen"));
            $request->request->add(["avatar" => $path]);
        }
        if ($request->birth_date) {
            $request->request->add(["birth_date" => $request->birth_date . " 00:00:00"]);
        }
        $pet->update($request->all());

        $owner = $pet->owner;
        $owner->update([
            'names' => $request->names,
            'surnames' => $request->surnames,
            'type_document' => $request->type_document,
            'n_document' => $request->n_document,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'emergency_contact' => $request->emergency_contact,
        ]);

        return response()->json([
            "message" => 200,
            "message_text" => "La mascota se ha editado correctamente"
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Gate::authorize('delete', Pet::class);
        $pet = Pet::findOrFail($id);
        if ($pet->avatar) {
            Storage::delete($pet->avatar);
        }
        $pet->delete();

        return response()->json([
            "message" => 200,
            "message_text" => "El usuario se ha eliminado correctamente"
        ]);
    }


    #TODO agregamos la funcion de busqueda por ID para el Aplicativo
    public function getPetById($id)
    {
        try {
            //Buscamos la mascota por el ID
            $pet = Pet::findOrFail($id);
            if(!$pet){
                return response()->json(['message' => 'Pet not found'], 404);
            }
            return response()->json($pet, 200);
        } catch (ModelNotFoundException) { #Si la mascota no existe manda eror 404
            return response()->json(["message" => "No se encontro la mascota"], 404);
        } catch (Exception $e) { # Si ocurre un error inestapado como la falla de la base de datos, response con un error 500
            return response()->json(["message" => "Error al buscar la mascota",
                "error" => $e->getMessage()], 500);
        }
    }
}
