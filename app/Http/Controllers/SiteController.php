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

  /**
  * Get external page
  *
  * @param string $slug
  * @return view
  */
  public function getExternalPage($slug)
  {
    if ($slug == 'home') {
      return redirect('/');
    }

    switch ($slug) {
      case 'apply-to-portal':
        $ListOfCountries = \App\ListOfCountry::all();
        return view('site.external-pages.apply-to-portal',['ListOfCountries' => $ListOfCountries]);
      break;
      default:
        abort(404);
      break;
    }
  }

}
