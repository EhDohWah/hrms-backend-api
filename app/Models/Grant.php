<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\GrantItem;

class Grant extends Model
{
    //
    use HasFactory;

    protected $fillable = ['name', 'code'];

    public function grantItems()
    {
        return $this->hasMany(GrantItem::class);
    }
}
