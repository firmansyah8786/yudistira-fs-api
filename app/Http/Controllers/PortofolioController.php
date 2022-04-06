<?php

namespace App\Http\Controllers;

use App\Models\Portofolio;
use App\Models\PortofolioImage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PortofolioController extends Controller
{
    public function index(Request $request)
    {
        $company_portofolios = Portofolio::with(['portofolioImages'=>function($q){
            $q->orderBy('index');
        }])->where('type','company')->get();
        $personal_portofolios = Portofolio::with(['portofolioImages'=>function($q){
            $q->orderBy('index');
        }])->where('type','personal')->get();

        return response()->json([
            'company_portofolios'=>$company_portofolios,
            'personal_portofolios'=>$personal_portofolios
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'=>'required',
            'description'=>'required',
            'type'=>'required|in:company,personal',
            'detail'=>'required',
        ]);

        try {
            $portofolio = Portofolio::create([
                'title'=>$request->title,
                'description'=>$request->description,
                'type'=>$request->type,
                'nda'=>($request->nda) ? $request->nda : false,
                'detail'=>$request->detail,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error'=>$th->getMessage()
            ],500);
        }

        return response()->json($portofolio);
    }

    public function storeImage(Request $request, $id)
    {
        $request->validate([
            'image'=>'required|image'
        ]);

        $portofolio = Portofolio::where('id',$id)->first();

        if(!$portofolio){
            return response()->json([
                'error'=>'Portofolio not found'
            ],404);
        }

        try {
            $file = $request->file('image');
            $localfolder = public_path('firebase-temp-uploads') .'/';
            $ext = $file->getClientOriginalExtension();
            $name = ($request->alt) ? $request->alt : uniqid();
            $fileName = $name.'.'.$ext;

            if ($file->move($localfolder, $fileName)) {
                $storedFile = fopen($localfolder.$fileName, 'r');
                $bukcet = app('firebase.storage')->getBucket();

                $uploadPath = 'portofolio-images/'.$portofolio->id.'/'.$fileName;

                $uplodedFile = $bukcet->upload($storedFile,[
                    'name'=>$uploadPath,
                    'metadata' => [
                        'metadata'=>[
                            'firebaseStorageDownloadTokens' => Str::uuid()
                        ]
                    ]
                ]);
                unlink($localfolder . $fileName);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'error'=>$th->getMessage()
            ],500);
        }

        try {
            $lastImage = PortofolioImage::where('portofolio_id',$portofolio->id)->orderBy('index','desc')->first();

            if($lastImage){
                $index = $lastImage->index + 1;
            } else {
                $index = 1;
            }

            $portofolioImage = PortofolioImage::create([
                'alt'=>($request->alt) ? $request->alt : '',
                'url'=>$uploadPath,
                'index'=>$index,
                'portofolio_id'=>$portofolio->id,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error'=>$th->getMessage()
            ],500);
        }

        return response()->json(['msg'=>'File uploaded']);
    }

    public function getImage(Request $request, $id)
    {
        $portofolioImages = PortofolioImage::orderBy('index')
            ->where('portofolio_id',$id)
            ->get();

        return response()->json($portofolioImages);
    }

    public function deleteImage(Request $request, $id, $imageId)
    {
        $portofolioImages = PortofolioImage::orderBy('index')
            ->where('portofolio_id',$id)
            ->where('id',$imageId)
            ->first();

        if(!$portofolioImages){
            return response()->json([
                'error'=>'Portofolio image not found'
            ],404);
        }

        try {
            $imageReference = app('firebase.storage')->getBucket()->object($portofolioImages->url);

            if ($imageReference->exists()) {
                $imageReference->delete();
            }

            $portofolioImages->delete();
        } catch (\Throwable $th) {
            return response()->json([
                'error'=>'Delete image failed'
            ],500);
        }

        return response()->json(['msg'=>'Image deleted']);
    }

    public function getImageUrl(Request $request, $id, $imageId)
    {
        $portofolioImage = PortofolioImage::orderBy('index')
            ->where('portofolio_id',$id)
            ->where('id',$imageId)
            ->first();

        $expiresAt = Carbon::now();
        $expiresAt->addSeconds(30);

        $imageReference = app('firebase.storage')->getBucket()->object($portofolioImage->url);

        if ($imageReference->exists()) {
            $image = $imageReference->signedUrl($expiresAt);
        } else {
            $image = "";
        }

        return response()->json(['image_url'=>$image]);
    }
}
