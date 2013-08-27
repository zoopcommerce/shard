<?php

//Builds the src php files of the docs into flat html files
//Run this script from the mystique/docs/build directory

$build = 'dist'; //used to switch the code used to bootstrap havok in layer.php

$base = __DIR__ . '/..';
$src = $base . '/src';
$dist = $base . '/dist';

$copy = function ($src,$dst) use (&$copy) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                $copy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
};

//empty dist folder
$files = glob($dist . '/*'); // get all file names
foreach($files as $file){ // iterate files
  if(is_file($file))
    unlink($file); // delete file
}

//copy js
$copy($src . '/js', $dist . '/js');

//build each html file
$files = glob($src . '/*.php'); // get all file names
foreach($files as $file){ // iterate files
    $name = explode('/', $file);
    $name = array_pop($name);
    $name = explode('.', $name);
    array_pop($name);
    if ($name[count($name) - 1] == 'layout'){
        continue;
    }
    $name[] = 'html';
    $name = implode('.', $name);
    ob_start();
    include $file;
    $content = ob_get_clean();
    file_put_contents($dist . '/' . $name, $content);
}

echo "build php files complete\n";
