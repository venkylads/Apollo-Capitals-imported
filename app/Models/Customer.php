<?php
 
namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Appointment;
 
class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'name', 
        'email', 
        'phone_number',  'address'
    ] ;
 
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function appointments(){
        return $this->hasOne(Appointment::class);
    }
}
 