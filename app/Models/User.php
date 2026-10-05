<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Mass-assignable attributes.
     * NOTE: `role` is only ever set by our own code (register = student,
     * instructor = seeder), never copied from request input.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /** Hidden when the model is serialized. */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // auto-hashes plain passwords on save
            'role' => Role::class,  // string column <-> Role enum
        ];
    }

    /** Extra profile info; only students have one (the instructor is seeded). */
    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }
}