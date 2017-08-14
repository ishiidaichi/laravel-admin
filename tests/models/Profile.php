<?php

namespace Tests\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Profile extends Model
{
    protected $table = 'test_user_profiles';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
