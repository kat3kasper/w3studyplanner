<?php
class TemplateDirs {
  // define a root directory for the templates (MVC Views)
  private static $view_root_dir = '/studyplanner/views/'; 
  // add all template directories
  private static $views_dirs = array('base/',);
  // cache directory
  private static $cache_dir = 'cache/';

  public static function getTemplateRoot()
  {
    return $_SERVER['DOCUMENT_ROOT'] . self::$view_root_dir;
  }

  public static function getTemplateDirs()
  {
    $views_dirs = self::$views_dirs;
    
    // append document root to use full path
    foreach ($views_dirs as $key => $value) {
      $views_dirs[$key] = self::getTemplateRoot() . $value;
    }

    // add template root to the list of view directories
    array_unshift($views_dirs, self::getTemplateRoot());

    return $views_dirs;
  }

  public static function getCacheDir()
  {
    return self::getTemplateRoot() . self::$cache_dir;
  }
}
?>