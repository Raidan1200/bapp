<?php

namespace App\Models;

use App\Models\Room;
use App\Models\User;
use App\Models\Order;
use App\Models\Package;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Venue extends Model
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'reminder_delay',
        'check_delay',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    public function getOverdueOrdersAttribute()
    {
        return Order::whereBetween('starts_at', [
            now()->addDays($this->reminder_delay),
            now()->addDays($this->reminder_delay + 1)
        ])->get();
    }
}
