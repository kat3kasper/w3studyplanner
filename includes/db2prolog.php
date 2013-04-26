<?php

include_once('config.php');
include_once('functions.php');

function getall_course_info()
{
  $host = DB_HOST;
  $dbname = DB_NAME;
  $user = DB_USER;
  $pass = DB_PASS;

  $dbh = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $user, $pass);
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $stmt = $dbh->prepare("SELECT * FROM course");
  $stmt->execute();

  $courseinfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $final_cinfo = array();

  foreach ((array)$courseinfo as $c) {
    $c['coursecode'] = $c['prefix'] . $c['number'];

    $reqstmt = $dbh->prepare("SELECT * FROM course_prerequisites WHERE parent_course_id = :pci");
    $reqstmt->bindParam(":pci", $c['coursecode']);
    $reqstmt->execute();
    $creqstmt = $dbh->prepare("SELECT * FROM course_corequisites WHERE parent_course_id = :pci");
    $creqstmt->bindParam(":pci", $c['coursecode']);
    $creqstmt->execute();

    $c['coursecode'] = "'". $c['prefix'] . $c['number'] ."'";

    $sems = explode(",", $c['on_campus_semesters']);

    foreach ($sems as $i => $s) {
      $sems[$i] = "'". $s ."'";
    }

    $c['on_campus_semesters'] = implode(",", $sems);

    $c['prerequisites'] = "none";
    $c['corequisites'] = "none";

    if($reqstmt->rowCount() > 0)
    {
      $prereq = $reqstmt->fetch(PDO::FETCH_ASSOC);
      $lp = unwrap($prereq['prereq_course_id']);

      foreach ((array)$lp as $i => $p) {
        if(strstr($p, " OR ")) {
          $op = explode(" OR ", $p);
          foreach ($op as $j => $o) {
            $op[$j] = "'". $o ."'";
          }
          $lp[$i] = wrap($op, 2);
        }
        else {
          $lp[$i] = "'" . $p . "'";
        }
      }
      $prereq['prereq_course_id'] = wrap($lp, 1);
      $c['prerequisites'] = $prereq['prereq_course_id'];
    }

    if($creqstmt->rowCount() > 0)
    {
      $coreq = $creqstmt->fetch(PDO::FETCH_ASSOC);
      $c['corequisites'] = $coreq['coreq_course_id'];
    }

    array_push($final_cinfo, $c);
  }

  return $final_cinfo;
}

function getall_coursegroup_info()
{
  $host = DB_HOST;
  $dbname = DB_NAME;
  $user = DB_USER;
  $pass = DB_PASS;

  $dbh = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $user, $pass);
  $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $stmt = $dbh->prepare("SELECT * FROM course_group");
  $stmt->execute();

  $courseginfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $final_cginfo = $courseginfo;

  foreach ($courseginfo as $index => $cg) {
    $final_cginfo[$index]['course_id'] = explode(',', $cg['course_id']);
    foreach ($final_cginfo[$index]['course_id'] as $i => $c) {
      $final_cginfo[$index]['course_id'][$i] = "'". $c ."'";
    }

    $final_cginfo[$index]['course_id'] = implode(",", $final_cginfo[$index]['course_id']);
    $final_cginfo[$index]['name'] = "'". $final_cginfo[$index]['name'] ."'";
  }

  return $final_cginfo;
}

function coursegroup_prologize()
{
  $cginfo = getall_coursegroup_info();

  $filebuf = "% load course information\n".
             ":- ensure_loaded(courses).\n".
             "\n\n%% COURSE GROUPS %%\n\n";

  foreach ((array)$cginfo as $coursegroup) {
    $filebuf .= "courseGroup({$coursegroup['name']}, [{$coursegroup['course_id']}]).\n";
  }

  // add trailing newline
  $filebuf .= "\n\n";

  file_put_contents('../lib/courseGroup.pl', $filebuf);
}

function course_prologize()
{
  $cinfo = getall_course_info();

  $filebuf = "%% COURSE LIST %%\n\n";

  foreach ((array)$cinfo as $c) {
    $filebuf .= "course({$c['coursecode']}, {$c['prerequisites']}, {$c['corequisites']}, [{$c['on_campus_semesters']}], {$c['no_of_credits']}).\n";
  }

  $filebuf .= "\n\n";

  file_put_contents('../lib/courses.pl', $filebuf);
}

?>