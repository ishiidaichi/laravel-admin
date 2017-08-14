<?php

namespace Tests\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'test_tags';

    public function users()
    {
        return $this->belongsToMany(User::class, 'test_user_tags', 'tag_id', 'user_id');
    }
}
