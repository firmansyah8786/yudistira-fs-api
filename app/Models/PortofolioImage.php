<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortofolioImage extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        $expiresAt = Carbon::now();
        $expiresAt->addSeconds(30);

        $imageReference = app('firebase.storage')->getBucket()->object($this->url);

        if ($imageReference->exists()) {
          $image = $imageReference->signedUrl($expiresAt);
        } else {
          $image = null;
        }

        return $image;
    }

    public function portofolio(){
        return $this->belongsTo(Portofolio::class);
    }
}
