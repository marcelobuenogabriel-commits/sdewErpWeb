<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'R999USU';

    protected $primaryKey = 'codusu';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function hasPermissionTo(User $user, string $ability)
    {
        return DB::table('R910MGP as MGP')
            ->join('R910GRP as GRP', 'MGP.CODGRP', '=', 'GRP.CODENT')
            ->where('CODMBR', '=', $user->codusu)
            ->where('DESGRP', '=', $ability)->exists();
    }

    public function adminlte_desc()
    {
        return strtoupper(auth()->user()->nomusu);
    }

    public function adminlte_profile_url()
    {
        return 'profile/username';
    }

    public function adminlte_image()
    {
        return 'vendor/adminlte/dist/img/boxed-bg.png';
    }
}
