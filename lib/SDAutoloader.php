<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/studyplanner/lib/Twig/Autoloader.php';

class SDAutoloader {
  // register external library autoloader functions here
  public static function register()
  {
    // register Twig functions
    Twig_Autoloader::register();
    // register this class's own autoloader
    spl_autoload_register('SDAutoloader::autoload');
  }

  public static function autoload($className)
  {
    // make sure class file is readable and exists
    if (is_readable($_SERVER['DOCUMENT_ROOT'] . "/studyplanner/lib/{$className
    }.php")) {
      require_once $_SERVER['DOCUMENT_ROOT'] . "/studyplanner/lib/{$className}.php";
    }
    else throw new Exception("File {$className} does not exist or is not readable");
  }
}
?>