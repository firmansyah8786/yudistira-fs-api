<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Portofolio extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'detail' => 'json',
        'nda'=>'boolean'
    ];

    public function portofolioImages(){
        return $this->HasMany(PortofolioImage::class);
    }
}
