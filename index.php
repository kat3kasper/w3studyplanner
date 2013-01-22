<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/studyplanner/lib/SDAutoloader.php';

// register main autoloader for classes
SDAutoloader::register();

$twig = new Twig_Environment(new Twig_Loader_Filesystem(TemplateDirs::getTemplateDirs()), array('cache' => TemplateDirs::getCacheDir(),'auto_reload' => true));

$username = "John Doe";
$usertype = "student";

// define all template variables
$tvars = array(
    "titlesub" => "Home",
    "navitems" => NavBar::renderNavBar(),
    "listitems" => NavBar::renderNavBarList(),
    "username" => $username,
    "usertype" => $usertype,
    );

// display rendered site
echo $twig->render('index.html', $tvars);
?>
