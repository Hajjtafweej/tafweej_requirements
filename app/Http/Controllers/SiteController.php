<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Home,App\City,App\Page,App\User;
use DB;
class SiteController extends Controller
{

  public function getPanel()
  {
  //   ini_set('memory_limit', '-1');
  //   $dirs = \File::directories(public_path('uploads/files'));
  //
  // foreach($dirs as $dir){
  //
  //   $files = \File::files($dir);
  //   $getUser = \App\User::where('username',str_replace('-','_',basename($dir)))->first();
  //   if ($getUser) {
  //     $Gallery = new \App\Presentation;
  //     $Gallery->user_id = $getUser->id;
  //     $Gallery->title_ar = 'العرض المرئي لموسم حج 1441 هـ (2020 م)';
  //     $Gallery->title_en = 'Presentation of the Hajj season 1441 H (2020 AD)';
  //     $Gallery->title_fr = 'Présentation de la saison Hajj 1441H (2020 AD)';
  //     $Gallery->created_by_id = user()->id;
  //     $Gallery->save();
  //     foreach($files as $f){
  //       \File::move(public_path('uploads/files/'.basename($dir).'/'.$f->getRelativePathname()), public_path('uploads/files/'.$f->getRelativePathname()));
  //       $Upload = new \App\Upload;
  //       $Upload->module = 'presentations';
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

  public function getDownloadFile($folder,$file)
  {
    return response()->download(public_path('uploads/'.$folder.'/'.$file));
  }

}
