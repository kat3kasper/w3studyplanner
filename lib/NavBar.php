<?php

class NavBar {
  // navigation components
  private static $navarr = array(
      array(
        "location" => "/studyplanner",
        "name" => "Home"
        ),
      array(
        "name" => "About"
        ),
      array(
        "name" => "Contact"
        )
      );
  // dropdown list components
  private static $listarr = array(
      array(
        "location" => "/studyplanner/admin",
        "name" => "Administrator View",
        "primary" => 1
        ),
      array(
        "location" => "/studyplanner/student",
        "name" => "Student View",
        )
      );

  public static function renderNavBar()
  {
    return self::$navarr;
  }

  public static function renderNavBarList()
  {
    return self::$listarr;
  }
}

?>