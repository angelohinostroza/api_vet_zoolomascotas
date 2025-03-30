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
            'password' => Hash::make($request->n_document),
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
        Gate::authorize('delete',Pet::class);
        $pet = Pet::findOrFail($id);
        if($pet->avatar){
            Storage::delete($pet->avatar);
        }
        $pet->delete();

        return response()->json([
            "message" => 200,
            "message_text" => "La mascota se ha eliminado correctamente"
        ]);
    }


    #TODO agregamos la funcion de busqueda por ID para el Aplicativo
    public function getPetById($id)
    {
        try {
            //Buscamos la mascota por el ID
            $pet = Pet::findOrFail($id);
            if (!$pet) {
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

    # METODO PARA EL APP
    public function indexApp()
    {
        try {
            $pets = Pet::with('owner:id,names,surnames,phone')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($pet) {
                    return [
                        'id' => $pet->id,
                        'specie' => $pet->specie,
                        'name' => $pet->name,
                        'breed' => $pet->breed,
                        'birth_date' => $pet->birth_date,
                        'gender' => $pet->gender,
                        'color' => $pet->color,
                        'weight' => $pet->weight,
                        'photo' => $pet->photo,
                        'medical_notes' => $pet->medical_notes,
                        'owner_id' => $pet->owner_id,
                        'deleted_at' => $pet->deleted_at,
                        'owner' => trim(($pet->owner->names ?? '') . ' ' . ($pet->owner->surnames ?? '')),
                        'phone' => $pet->owner->phone ?? null,
                    ];
                });

            return response()->json([
                'message' => 'Lista de mascotas obtenida correctamente',
                'data' => $pets
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las mascotas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateApp(Request $request,string $id){
        try {
            $pet = Pet::findOrFail($id);
            $pet->update($request->all());
            return response()->json(['message' => 'Mascota actualizada correctamente', 'data' => $pet], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Mascota no encontrado'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al actualizar la mascota', 'message' => $e->getMessage()], 500);
        }
    }


    public function destroyApp(string $id)
    {
        try {
            $pet = Owner::findOrFail($id);
            $pet->delete();
            return response()->json(['message' => 'Mascota eliminada correctamente', 'data' => $pet], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Mascota no encontrada'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al eliminar la mascota', 'message' => $e->getMessage()], 500);
        }
    }

    public function toggleActive($id)
    {
        $pet = Pet::withTrashed()->find($id);

        if (!$pet) {
            return response()->json(['message' => 'Mascota no encontrada'], 404);
        }

        if ($pet->deleted_at) {
            // Si está eliminado, lo activamos (colocamos deleted_at en null)
            $pet->restore();
            return response()->json(['message' => 'Dueño activado con éxito', 'data' => $pet]);
        } else {
            // Si está activo, lo desactivamos (registramos la fecha actual en deleted_at)
            $pet->delete();
            return response()->json(['message' => 'Dueño desactivado con éxito', 'data' => $pet]);
        }
    }


    public function search(Request $request)
    {
        try {
            // Obtener el parámetro de búsqueda
            $searchTerm = $request->input('search');

            $pets = Pet::with('owner:id,names,surnames,phone')
                ->where(function ($query) use ($searchTerm) {
                    $query->where('name', 'ILIKE', "%{$searchTerm}%") // Buscar por nombre de la mascota
                    ->orWhereHas('owner', function ($q) use ($searchTerm) { // Si no, buscar en el dueño
                        $q->where('names', 'ILIKE', "%{$searchTerm}%");
                    });
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($pet) {
                    return [
                        'id' => $pet->id,
                        'specie' => $pet->specie,
                        'name' => $pet->name,
                        'breed' => $pet->breed,
                        'birth_date' => $pet->birth_date,
                        'gender' => $pet->gender,
                        'color' => $pet->color,
                        'weight' => $pet->weight,
                        'photo' => $pet->photo,
                        'medical_notes' => $pet->medical_notes,
                        'owner_id' => $pet->owner_id,
                        'deleted_at' => $pet->deleted_at,
                        'owner' => trim(($pet->owner->names ?? '') . ' ' . ($pet->owner->surnames ?? '')),
                        'phone' => $pet->owner->phone ?? null,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Búsqueda realizada correctamente',
                'data' => $pets
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar mascotas',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
