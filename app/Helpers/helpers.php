<?php

function price($v){
  return number_format($v,2).' '.__('master.rs');
}
function domain($with_http = false,$addSubdomain = null){
  $ex = explode('.',request()->getHost());
  $prefix = ($with_http) ? ((request()->secure()) ? 'https://' : 'http://') : '';
  $addSubdomain = ($addSubdomain) ? $addSubdomain.'.' : null;
  if (count($ex) > 1) {
    return $prefix.$addSubdomain.$ex[1].(isset($ex[2]) ? '.'.$ex[2] : '');
  }
  return $prefix.$addSubdomain.$ex[0];
}

function make_slug($string, $separator = '-')
{
  $string = trim($string);
  $string = mb_strtolower($string, 'UTF-8');

  // Make alphanumeric (removes all other characters)
  // this makes the string safe especially when used as a part of a URL
  // this keeps latin characters and Persian characters as well
  $string = preg_replace("/[^a-z0-9_\s\-۰۱۲۳۴۵۶۷۸۹ةيءاآؤئبپتثجچحخدذرزژسشصضطظعغفقکكگگلمنوهی]/u", '', $string);

  // Remove multiple dashes or whitespaces or underscores
  $string = preg_replace("/[\s\-_]+/", ' ', $string);

  // Convert whitespaces and underscore to the given separator
  $string = preg_replace("/[\s_]/", $separator, $string);

  return str_replace('/','',$string);
}

function user(){
  return (request()->is_web || isset(auth()->user()->id)) ? auth()->user() : auth('api')->user();
}

function cleanPhone($v){
    return '966'.substr(preg_replace('/^0+/','', $v),0,9);
}


function home_path(){
  return (domain() == 'localhost') ? public_path() : base_path();
}

function zipData($source, $destination) {
    if (extension_loaded('zip')) {
        if (file_exists($source)) {
            $zip = new ZipArchive();
            if ($zip->open($destination, ZIPARCHIVE::CREATE)) {
                $source = realpath($source);
                if (is_dir($source) === true) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

    foreach ($files as $file) {
        $file = realpath($file);

        if (is_dir($file) === true) {
            $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));

        } else if (is_file($file) === true) {
            $zip->addFile($file, str_replace($source . '/', '', $file));
        }
    }
} else if (is_file($source) === true) {
        $zip->addFile($source, basename($source));
}
            }
            return $zip->close();
        }
    }
    return false;
}

function make_substr($val,$max_length = 50){
  if (mb_strlen($val) > $max_length)
  {
    return mb_substr($val, 0, $max_length)."ـ..";
  }
  else
  {
    return $val;
  }
}
