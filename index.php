<?php
require_once 'lib/Twig/Autoloader.php';
Twig_Autoloader::register();
// define a root for the template directories
$view_root_dir = $_SERVER['DOCUMENT_ROOT'] . '/studyplanner/views/';
// add all template directories
$views_dirs = array($view_root_dir, 
                    $view_root_dir . '/base',
                    );

$loader = new Twig_Loader_Filesystem($views_dirs);
$twig = new Twig_Environment($loader, array('cache' => $view_root_dir . '/cache','auto_reload' => true));

$username = "John Doe";
$usertype = "student";

// define all template variables
$tvars = array(
    "titlesub" => "Home",
    "navitems" => array(
      array(
        "active" => 1,
        "location" => "/studyplanner",
        "name" => "Home"
        ),
      array(
        "name" => "About"
        ),
      array(
        "name" => "Contact"
        )
      ),
    "listitems" => array(
      array(
        "location" => "/studyplanner/admin",
        "name" => "Administrator View",
        "primary" => 1
        ),
      array(
        "location" => "/studyplanner/student",
        "name" => "Student View",
        )
      ),
    "username" => $username,
    "usertype" => $usertype,
    );

// display rendered site
echo $twig->render('index.html', $tvars);
?>
