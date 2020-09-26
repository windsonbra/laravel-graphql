<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    protected $table = 'email';
    protected $fillable = [
        'title', 'body'
    ];
    public $timestamps = false; 
}
