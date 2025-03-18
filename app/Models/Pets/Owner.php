<?php

namespace App\Models\Pets;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Owner extends Authenticatable
{
    ##TODO necesario para usar createToken() en el controller
    use HasApiTokens;
    use HasFactory;
    use SoftDeletes;
    use Notifiable;

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
        'password', 'remember_token'
    ];

    public function setCreatedAtAttribute($value)
    {
        date_default_timezone_set('America/Lima');
        $this->attributes["created_at"] = Carbon::now();
    }

    public function setUpdatedAtAttribute($value)
    {
        date_default_timezone_set("America/Lima");
        $this->attributes["updated_at"] = Carbon::now();
    }

    #TODO se modifica la relacion, con hasMany, como un dueño puede tener muchas mascotas, la relacion sera hasMany
    #tener en cuenta si algo modificar en el web
//    public function pet(){
//        return $this->belongsTo(Pet::class,"owner_id");
//    }

    public function pet()
    {
        return $this->hasMany(Pet::class,"owner_id");
    }
}
