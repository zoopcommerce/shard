<?php

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

//copy css
$copy($src . '/css', $dist . '/css');

//copy js
$copy($src . '/js', $dist . '/js');

//copy img
$copy($src . '/img', $dist . '/img');

//build each html file
$files = glob($src . '/*.php'); // get all file names
foreach($files as $file){ // iterate files
    $name = explode('/', $file);
    $name = array_pop($name);
    $name = explode('.', $name);
    array_pop($name);
    $name[] = 'html';
    $name = implode('.', $name);
    ob_start();
    include $file;
    $content = ob_get_clean();
    file_put_contents($dist . '/' . $name, $content);
}

echo "docs build complete\n";
