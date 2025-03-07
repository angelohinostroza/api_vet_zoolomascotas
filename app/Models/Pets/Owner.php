<?php

namespace App\Models\Pets;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class Owner extends Authenticatable
{
    ##TODO necesario para usar createToken() en el controller
    use HasApiTokens;
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'names',
        'surnames',
        'type_document',
        'n_document',
        'email',
        'phone',
        'address',
        'city',
        'emergency_contact',
    ];

    #TODO Aregamos el password para el login desde el aplicativo
    protected $hidden = [
        'password','remember_token'
    ];

    public function setCreatedAtAttribute($value)
    {
    	date_default_timezone_set('America/Lima');
        $this->attributes["created_at"]= Carbon::now();
    }

    public function setUpdatedAtAttribute($value)
    {
    	date_default_timezone_set("America/Lima");
        $this->attributes["updated_at"]= Carbon::now();
    }

    public function pet(){
        return $this->belongsTo(Pet::class,"owner_id");
    }
}
