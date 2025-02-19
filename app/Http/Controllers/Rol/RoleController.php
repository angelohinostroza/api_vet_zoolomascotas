<?php

namespace App\Http\Controllers\Rol;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get("search");

        $roles = Role::where("name","ilike","%".$search."%")->orderBy("id","desc")->get();

        return response()->json([
            "roles" => $roles->map(function($rol) {
                return [
                    "id" => $rol->id,
                    "name" => $rol->name,
                    "created_at" => $rol->created_at->format("Y-m-d h:i:s"),
                    "permissions" => $rol->permissions,
                    "permissions_pluck" => $rol->permissions->pluck('name'),
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $exist_role = Role::where("name",$request->name)->first();
        if($exist_role){
            return response()->json([
                "message" => 403,
                "message_text" => "EL NOMBRE DEL ROL YA EXISTE"
            ]);
        }

        $role = Role::create([
            "name" => $request->name,
            "guard_name" => "api"
        ]);

        foreach ($request->permissions as $key => $permission) {
            $role->givePermissionTo($permission);
        }

        return response()->json([
            "message" => 200,
            "message_text" => "El rol se ha creado correctamente"
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $exist_role = Role::where("name",$request->name)->where("id","<>",$id)->first();
        if($exist_role){
            return response()->json([
                "message" => 403,
                "message_text" => "EL NOMBRE DEL ROL YA EXISTE"
            ]);
        }

        $role = Role::findOrFail($id);
        $role->update([
            "name" => $request->name
        ]);
        $role->syncPermissions($request->permissions);
        return response()->json([
            "message" => 200,
            "message_text" => "El Rol se edito correctamente"
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return response()->json([
            "message" => 200,
            "message_text" => "El Rol se elimino correctamente"
        ]);
    }
}
