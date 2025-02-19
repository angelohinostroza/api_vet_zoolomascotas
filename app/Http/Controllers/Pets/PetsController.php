<?php

namespace App\Http\Controllers\Pets;

use App\Models\Pets\Pet;
use App\Models\Pets\Owner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Pets\PetResource;
use App\Http\Resources\Pets\PetCollection;

class PetsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get("search");
        $species = $request->get("species");

        $pets = Pet::where(function($q) use($search,$species){
                    if ($species) {
                        $q->where("specie",$species);
                    }
                    if($search){
                        $q->whereHas("owner",function($q) use($search) {
                            $q->Where(DB::raw("pets.name || ' ' || owners.names || ' ' || COALESCE(owners.surnames,'') || ' ' || owners.phone
                                                || ' ' || owners.n_document" ),"ilike","%".$search."%");
                        });
                    }
                })
                ->orderBy("id","desc")->paginate(5);

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

        if($request->hasFile("imagen")){
            $path = Storage::putFile("pets",$request->file("imagen"));
            $request->request->add(["photo" => $path]);
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
        $pet = Pet::findOrFail($id);
        if($request->hasFile("imagen")){
            if($pet->avatar){
                Storage::delete($pet->avatar);
            }
            $path = Storage::putFile("pets",$request->file("imagen"));
            $request->request->add(["photo" => $path]);
        }
        if($request->birth_date){
            $request->request->add(["birth_date" => $request->birth_date." 00:00:00"]);
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
        $pet = Pet::findOrFail($id);
        if($pet->photo){
            Storage::delete($pet->photo);
        }
        $pet->delete();

        return response()->json([
            "message" => 200,
            "message_text" => "El usuario se ha eliminado correctamente"
        ]);
    }
}
