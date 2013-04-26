<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);


require_once "lib/eclipse_exdr_parser.php";
require_once "lib/eclipse_socket.php";

class Degree {
  private $semesters;
  private $requirements;
  private $transcript;

  public function Degree(array $sems = array(), array $reqs = array(), array $transcript = array())
  {
    $this->semesters = $sems;
    $this->requirements = $reqs;
    $this->transcript = $transcript;
  }

  public function getSemesters() {
    return $this->semesters;
  }

  public function getRequirements() {
    return $this->requirements;
  }

  public function getTranscript() {
    return $this->transcript;
  }

/*  // add a requirement
  public function requires(Predicate $r) {
    array_push($requirements, $r);
  }
*/
  public function requires($requirement_name, $course_name) {
    array_push($this->requirements, Degree::degreeReq($requirement_name, $course_name));
  }

/*  // add a semester
  public function addSemester(Predicate $s) {
    array_push($semesters, $s);
  }*/

  public function addSemester($term, $year, $minCredits, $maxCredits, array $prefs) {
    array_push($this->semesters, Degree::semesterNew($term, $year, $minCredits, $maxCredits, $prefs));
  }

  // add course to transcript
  public function courseTaken($course_name) {
    array_push($this->transcript, $course_name);
  }

  public function buildGoal() {
    return Degree::okDegree($this->semesters, $this->requirements, $this->transcript);
  }

  // the following are named to match the prolog predicate they represent
  public static function degreeReq($name, $class) {
  return new Predicate('degreeReq', array($name, $class, ($class == "none") ? null : array()));
  }

  public static function semesterNew($term, $year, $minCredits, $maxCredits, $prefs) {
    return new Predicate('semesterNew', array($term, $year, $minCredits, $maxCredits, null, $prefs));
  }

  public static function okDegree($semesters, $degreeReqs, $classesTaken) {
    return new Predicate('okDegree', array($semesters, $degreeReqs, $classesTaken));
  }
}

define("PROLOG_HOST", "localhost");
define("PROLOG_PORT", 9000);
define("PROLOG_BIN", "eclipseclp/bin/i386_linux/eclipse");
define("PROLOG_SERVER", "lib/server.pl");

class ECLiPSeQuery {
  private $logicSocket;

  public function ECLiPSeQuery($hostname = PROLOG_HOST, $port = PROLOG_PORT)
  {
    $this->ensure_running_server();
    $this->logicSocket = new EclipseSocket($hostname, $port);
  }

  // starts server if it's not running (in case it may crash sometime)
  private function ensure_running_server()
  {
    if(PROLOG_HOST == "localhost") {
    // check if ECLiPSe server is running
    exec("ps aux | grep -i 'eclipse' | grep -v grep", $instances);
    if(empty($instances))
    {
      // start eclipse server
      //echo "starting server...\n";
      exec("nice -19 " . PROLOG_BIN ." -b ". PROLOG_SERVER ." -e listen > /dev/null 2>/dev/null &");
      sleep(1);
    }
    }
  }

  public function submitGoal(Degree $d) {
    $goal = $d->buildGoal();
    $goal_result = $this->logicSocket->rpcGoal($goal->toEXDRTerm());
    $result_pred = $goal_result->getTerm()->getObject();
    $solutions = $result_pred->getArgs();
    return $solutions;
  }

  public function getSolutionJSON(Degree $d) {
    $jsonlutions = array();
    $solutions = $this->submitGoal($d);

    $jsonlutions['semesters'] = array();
    foreach ($solutions[0] as $semester) {
      $s = array();
      $s['term'] = $semester->getArg(0);
      $s['year'] = $semester->getArg(1);
      $s['min_credits'] = $semester->getArg(2);
      $s['max_credits'] = $semester->getArg(3);
      $s['selection'] = array();
      foreach ((array)$semester->getArg(4) as $course) {
        $c = array();
        $c['coursegroup'] = $course->getArg(0);
        $c['coursename'] = $course->getArg(1);
        $c['courselist'] = array();
        foreach ($course->getArg(2) as $value) {
          array_push($c['courselist'], $value);
        }
        array_push($s['selection'], $c);
      }
      foreach ($semester->getArg(5) as $preference) {
        $p = array();
        $p['coursegroup'] = $preference->getArg(0);
        $p['coursename'] = $preference->getArg(1);
        $p['courselist'] = array();
        foreach ($preference->getArg(2) as $value) {
          array_push($p['courselist'], $value);
        }
        array_push($s['selection'], $p);
      }
      array_push($jsonlutions['semesters'], $s);
    }

    $jsonlutions['requirements'] = array();
    foreach ($solutions[1] as $requirement) {
      $r = array();
      $r['coursegroup'] = $requirement->getArg(0);
      $r['coursename'] = $requirement->getArg(1);
      
      array_push($jsonlutions['requirements'], $r);
    }

    $jsonlutions['transcript'] = array();
    foreach ($solutions[2] as $course) {
      array_push($jsonlutions['transcript'], $course);
    }

    return $jsonlutions;
  }
}

/*$jsonString = '{
  "semesters" : [
    { "term" : "fall",
      "year" : 2009,
      "min_credits" : 12,
      "max_credits" : 18,
      "preferences" : [
        {
          "coursegroup" : "techElect",
          "coursename" : "cs105"
        },
        {
          "coursegroup" : "humGroupA",
          "coursename"  : "hpl111"
        }
      ]
    },
    { "term" : "spring",
      "year" : 2010,
      "min_credits" : 12,
      "max_credits" : 18,
      "preferences" : [
        {
          "coursegroup" : "csReq",
          "coursename" : "cs115"
        }
      ]
    },
    { "term" : "fall",
      "year" : 2010,
      "min_credits" : 12,
      "max_credits" : 18,
      "preferences" : [
      ]
    },
    { "term" : "spring",
      "year" : 2011,
      "min_credits" : 12,
      "max_credits" : 18,
      "preferences" : [
      ]
    },
    { "term" : "fall",
      "year" : 2011,
      "min_credits" : 12,
      "max_credits" : 18,
      "preferences" : [
      ]
    },
    { "term" : "spring",
      "year" : 2012,
      "min_credits" : 12,
      "max_credits" : 18,
      "preferences" : [
      ]
    },
    { "term" : "fall",
      "year" : 2012,
      "min_credits" : 12,
      "max_credits" : 18,
      "preferences" : [
      ]
    },
    { "term" : "spring",
      "year" : 2013,
      "min_credits" : 12,
      "max_credits" : 18,
      "preferences" : [
      ]
    }
  ],
  "requirements" : [
  {
        "coursegroup": "sci",
        "coursename": "ch115"
    },
    {
        "coursegroup": "sci",
        "coursename": "ch281"
    },
    {
        "coursegroup": "math",
        "coursename": "ma115"
    },
    {
        "coursegroup": "math",
        "coursename": "ma116"
    },
    {
        "coursegroup": "math",
        "coursename": "ma222"
    },
    {
        "coursegroup": "math",
        "coursename": "ma331"
    },
    {
        "coursegroup": "mngt",
        "coursename": "mgt111"
    },
    {
        "coursegroup": "csReq",
        "coursename": "cs146"
    },
    {
        "coursegroup": "csReq",
        "coursename": "cs135"
    },
    {
        "coursegroup": "csReq",
        "coursename": "cs284"
    },
    {
        "coursegroup": "csReq",
        "coursename": "cs334"
    },
    {
        "coursegroup": "csReq",
        "coursename": "cs383"
    },
    {
        "coursegroup": "csReq",
        "coursename": "cs385"
    },
    {
        "coursegroup": "csReq",
        "coursename": "cs347"
    },
    {
        "coursegroup": "csReq",
        "coursename": "cs392"
    },
    {
        "coursegroup": "csReq",
        "coursename": "cs496"
    },
    {
        "coursegroup": "csReq",
        "coursename": "cs442"
    },
    {
        "coursegroup": "csReq",
        "coursename": "cs511"
    },
    {
        "coursegroup": "csReq",
        "coursename": "cs488"
    },
    {
        "coursegroup": "csReq",
        "coursename": "cs492"
    },
    {
        "coursegroup": "csReq",
        "coursename": "cs506"
    },
    {
        "coursegroup": "csReq",
        "coursename": "cs423"
    },
    {
        "coursegroup": "csReq",
        "coursename": "cs424"
    },
    {
        "coursegroup": "techElect",
        "coursename": "none"
    },
    {
        "coursegroup": "softwareDevElective",
        "coursename": "none"
    },
    {
        "coursegroup": "mathScienceElective",
        "coursename": "none"
    },
    {
        "coursegroup": "mathScienceElective",
        "coursename": "none"
    },
    {
        "coursegroup": "freeElective",
        "coursename": "none"
    },
    {
        "coursegroup": "freeElective",
        "coursename": "none"
    },
    {
        "coursegroup": "humGroupA",
        "coursename": "none"
    },
    {
        "coursegroup": "humGroupB",
        "coursename": "none"
    },
    {
        "coursegroup": "humGroupB",
        "coursename": "none"
    },
    {
        "coursegroup": "humRequiredClass",
        "coursename": "hss371"
    },
    {
        "coursegroup": "hum300400",
        "coursename": "none"
    },
    {
        "coursegroup": "hum300400",
        "coursename": "none"
    },
    {
        "coursegroup": "hum300400",
        "coursename": "none"
    }

  ],
  "transcript" : [] 
}';
*/
if (!defined(DEGCLIENT_INCLUDED)) {

  $postdata = file_get_contents('php://input');

    if($postdata) {
    $input = json_decode((empty($postdata)) ? $jsonString : $postdata, true);

    $trydeg = new Degree();


    // iterate through the list of semesters and add semester information to the degree
    foreach ($input['semesters'] as $semester) {
      $sem_prefs = array();
      foreach ($semester['preferences'] as $pref) {
        array_push($sem_prefs, Degree::degreeReq($pref['coursegroup'], $pref['coursename']));
      }

      $trydeg->addSemester($semester['term'], $semester['year'], $semester['min_credits'], $semester['max_credits'], $sem_prefs);
    }

    // add statements of degree requirements
    foreach ($input['requirements'] as $req) {
      $trydeg->requires($req['coursegroup'], $req['coursename']);
    }

    // add courses
    foreach ($input['transcript'] as $course) {
      $trydeg->courseTaken($course);
    }

    $ecl = new ECLiPSeQuery();

    $sol = $ecl->getSolutionJSON($trydeg);

    echo json_encode($sol);
    }
}
?>
