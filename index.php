<?php
require_once 'lib/Twig/Autoloader.php';
Twig_Autoloader::register();
$views_dir = $_SERVER['DOCUMENT_ROOT'] . '/studyplanner/views/';

$loader = new Twig_Loader_Filesystem($views_dir);
$twig = new Twig_Environment($loader, array('cache' => $views_dir . '/cache','auto_reload' => true));

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
        "location" => "#",
        "name" => "About"
        ),
      array(
        "location" => "#",
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
