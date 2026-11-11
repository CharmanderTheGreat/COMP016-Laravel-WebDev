<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A student's profile picture, stored IN THE DATABASE (base64 text).
 *
 * Why: Railway's disk is wiped on every redeploy and the project has no Volume,
 * so files saved in storage/app/public would disappear. The database survives.
 * Kept in its own table so listing students never loads the image data.
 */
class StudentPhoto extends Model
{
    protected $fillable = ['student_id', 'mime', 'data'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
