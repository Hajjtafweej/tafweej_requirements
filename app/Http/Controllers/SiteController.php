<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class SiteController extends Controller
{

  public function getPanel()
  {
    if (user()->is_admin) {
      return view('site.admin');
    }else {
      return view('site.panel');
    }
  }

  public function getDownloadFile($folder,$file)
  {
    return response()->download(public_path('uploads/'.$folder.'/'.$file));
  }

}
