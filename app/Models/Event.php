<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $guarded = ['id'];

    public function Session()
    {
        return $this->hasMany(Session::class, 'event_id', 'id');
    }

    public function Product()
    {
        return $this->hasMany(Product::class, 'group_article', 'id');
    }
}
