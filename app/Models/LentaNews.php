<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LentaNews extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'content', 'image', 'source', 'link'];
}
