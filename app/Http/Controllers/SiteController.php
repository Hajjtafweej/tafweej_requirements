<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Home,App\City,App\Page,App\User;
use DB;
class SiteController extends Controller
{

  public function getPanel()
  {
    // ini_set('memory_limit', '-1');
    // $dirs = \File::directories(public_path('uploads/images'));
  //
  // foreach($dirs as $dir){
  //
  //   $files = \File::files($dir);
  //   $getUser = \App\User::where('username',str_replace('-','_',basename($dir)))->first();
  //   if ($getUser) {
  //     $Gallery = new \App\Gallery;
  //     $Gallery->user_id = $getUser->id;
  //     $Gallery->title_ar = 'صور رقم 1';
  //     $Gallery->title_en = 'Photos 1';
  //     $Gallery->title_fr = 'Photos 1';
  //     $Gallery->created_by_id = user()->id;
  //     $Gallery->save();
  //     foreach($files as $f){
  //       $Upload = new \App\Upload;
  //       $Upload->module = 'gallery';
  //       $Upload->module_id = $Gallery->id;
  //       $Upload->user_id = $getUser->id;
  //       $Upload->created_by_id = user()->id;
  //       $Upload->path = $f->getRelativePathname();
  //       $Upload->save();
  //
  //     }
  //   }
  //
  //
  // }

    return view('site.panel');
  }

}
