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
        // dd($expiresAt);
        $imageReference = app('firebase.storage')->getBucket()->object($this->url);
        // $imageInfo = $imageReference->info();
        // $filePath = str_replace('/','%2F',$imageInfo['name']);
        // $link = 'https://firebasestorage.googleapis.com/v0/b/'.$imageInfo['bucket'].'/o/'.$filePath.'?alt=media&token='.$imageInfo['metadata']['firebaseStorageDownloadTokens'];
        // https://firebasestorage.googleapis.com/v0/b/yudistira-fs-be.appspot.com/o/portofolio-images%2F5%2F62191a143236f.png?alt=media&token=1acb44cf-bb4e-48b0-a75b-f64e8b0f7772

        if ($imageReference->exists()) {
          $image = $imageReference->signedUrl($expiresAt);
        // $image = $imageReference->info();
        // $image = $link;
        } else {
          $image = null;
        }

        return $image;
    }

    public function portofolio(){
        return $this->belongsTo(Portofolio::class);
    }
}
