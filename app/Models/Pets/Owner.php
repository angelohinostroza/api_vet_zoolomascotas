<?php

namespace App\Models\Pets;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Owner extends Model
{
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
