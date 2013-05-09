<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Construct Schedule</title>
		<?php require("../includes/styles.php"); ?>
		<?php require("../includes/config.php"); ?>
		<?php require("../includes/functions.php"); ?>
		<?php require("../includes/scripts.php"); ?>
	</head>
	<body>
		<?php require("../includes/navigation.php"); ?>
		
		<div class="container">
			<?php
				echo "<p>Welcome, " . $_SERVER["REDIRECT_displayName"] . "</p>";
			?>
			
			<h4>Construct Schedule</h4>
			
			<p>Here is suggested schedule. If you wish to save your schedule, please click the download button on the bottom of the page</p>
			
<?php
	if(isset($_POST["step6"]))
	{
		$step1Info = json_decode(htmlspecialchars_decode($_POST["step1Info"]), true);
		$step2Info = json_decode(htmlspecialchars_decode($_POST["step2Info"]), true);
		$step3Info = json_decode(htmlspecialchars_decode($_POST["step3Info"]), true);
		$groupList = json_decode(htmlspecialchars_decode($_POST["groupList"])); //Name of course groups
		$groupCourses = json_decode(htmlspecialchars_decode($_POST["groupCourses"])); //Grouped courses

    //echo "<pre>". $_POST["groupList"] . "\n\n". $_POST["groupCourses"] ."</pre>";
		
		$termGraduate = $step2Info["termGraduate"];
		$yearGraduate = $step2Info["yearGraduate"];
		$maxCredits = $step3Info["maxCredits"];
		$minCredits = $step3Info["minCredits"];
		
		$groups = $_POST["group"]; //Everything from course preferences
		
		$currMonth = date("n");
		$currYear = date("Y");
		
		if($currMonth < 5) $currTerm = 4;
		else if($currMonth < 6) $currTerm = 3;
		else if($currMonth < 8) $currTerm = 2;
		else $currTerm = 1;
		
		$termNames = array(
			4 => "spring",
			3 => "summer1",
			2 => "summer2",
			1 => "fall"
		);
		
		$termNum = array(
			4 => 0,
			3 => 1,
			2 => 2,
			1 => 3
		);
		
		//Find total term to do
		$yearDiff = $yearGraduate - $currYear;
		$termDiff = array_search($termGraduate, $termNames) - $currTerm;
		$totalTerms = abs(($yearDiff * 4) - $termDiff) + 1;
		
		$semesters = array();
		$transcript = array();
		
		//Format data on each semester to take
		for($termPoint = $currTerm, $yearPoint = $currYear, $termCount = 0; $termCount < $totalTerms; $termCount++)
		{
			$preferences = array();
			
			$i = 0;
			$k = 0;
			//Course group
			while(isset($groups[$i]))
			{
				$j = 0;
				//Course in the group
				while(isset($groups[$i][$j]))
				{
          /* change course to lowercase since for some reason course prefix case
             doesnt match the database. */
          $groups[$i][$j][0] = strtolower($groups[$i][$j][0]);

					//If year and term is set
					if(isset($groups[$i][$j][2]) && !empty($groups[$i][$j][2]) && isset($groups[$i][$j][1]))
					{
						//If year & term matches current iteration's
						if($groups[$i][$j][2] == $yearPoint && $groups[$i][$j][1] == $termNames[$termPoint])
						{
							$preferences[$k]["coursegroup"] = $groupList[$i];
							$preferences[$k]["coursename"] = $groups[$i][$j][0];
							$k++;
						}
					}
					//If course is completed
					else if(isset($groups[$i][$j][1]) && $groups[$i][$j][1] == "completed") {
						if(!in_array($groups[$i][$j][0], $transcript))
            {
							$transcript[] = $groups[$i][$j][0];
            }
          }
					
					$j++;
				}
				$i++;
			}
			
			//Build term array
      if($maxCredits[$yearPoint][$termNum[$termPoint]] > 0) {
  			$term = array(
  				"term" => $termNames[$termPoint],
  				"year" => $yearPoint,
  				"min_credits" => $minCredits[$yearPoint][$termNum[$termPoint]],
  				"max_credits" => $maxCredits[$yearPoint][$termNum[$termPoint]],
  				"preferences" => $preferences
  			);
  			
  			$semesters[] = $term;
      }
			
			$termPoint--;
			if($termPoint == 0)
			{
				$termPoint = 4;
				$yearPoint++;
			}
		}
		
		$requirements = array();
		$n = 0;
		
		//Build list of requirement
		foreach($groups as $aGroup)
		{
			foreach($aGroup as $aCourse)
			{
				array_push($requirements, array(
					"coursegroup" => $groupList[$n],
					"coursename" => empty($aCourse[0]) ? "none" : $aCourse[0]
				));
			}
			$n++;
		}
		
		//Build the json to send to constraint solver
		$jsonString = json_encode(array(
			"semesters" => $semesters,
			"requirements" => $requirements,
			"transcript" => $transcript
		), JSON_NUMERIC_CHECK);
		
		
		//CONNECT TO CONSTRAINT SOLVER
    define('DEGCLIENT_INCLUDED', 1);
		include_once('../degclient.php');


    //echo '<pre> input:\n\n' . $jsonString .'</pre>';

    $input = json_decode($jsonString, true);

    $trydeg = new Degree();

    //echo "<pre>PROLOG input:\n\nokDegree([";

    // iterate through the list of semesters and add semester information to the degree
    $i = 0;
    foreach ($input['semesters'] as $semester) {
      $sem_prefs = array();
      foreach ($semester['preferences'] as $pref) {
        array_push($sem_prefs, Degree::degreeReq($pref['coursegroup'], $pref['coursename']));
      }

      $trydeg->addSemester($semester['term'], $semester['year'], $semester['min_credits'], $semester['max_credits'], $sem_prefs);
      //echo "semesterNew('{$semester['term']}', {$semester['year']}, {$semester['min_credits']}, {$semester['max_credits']}, Sem{$i}, ". json_encode($sem_prefs) ."),\n";
      $i++;
    }
    //echo "],\n[";
    $i = 0;
    // add statements of degree requirements
    foreach ($input['requirements'] as $req) {
      $trydeg->requires($req['coursegroup'], $req['coursename']);
      //echo "degreeReq('{$req['coursegroup']}', ". (($req['coursename'] == "none") ? "none, C{$i}" : "{$req['coursename']}, []") ."),\n";
      $i++;
    }

    //echo "],\n[";
    // add courses
    foreach ($input['transcript'] as $course) {
      $trydeg->courseTaken($course);
      echo "'{$course}',\n";
    }

    //echo "])\n</pre>";

		$sol = array("semesters" => array(), "transcript" => array());
		try {
    $ecl = new ECLiPSeQuery();

    $sol = $ecl->getSolutionJSON($trydeg);
  	}
  	catch(Exception $e)
  	{
  		//echo "<pre>something bad happened</pre>";
  	}
		
		$decodedString = $sol;//json_decode($result, true);
		//var_dump($sol);

    //echo "<pre>output:\n\n". json_encode($sol, JSON_NUMERIC_CHECK) ."</pre>";
		
		$semesters = $decodedString["semesters"];
		$transcript = $decodedString["transcript"];
		
		//change cpref - if required courses from cgroup is same as cgroup size, list down
		
		//Setup database
		$host = DB_HOST;
		$dbname = DB_NAME;
		$user = DB_USER;
		$pass = DB_PASS;
		
		$dbh = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $user, $pass);
		$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		//Get prerequisites details
		$sql = "SELECT * FROM course_prerequisites WHERE parent_course_id = :cid";
		$sth = $dbh->prepare($sql);

		//var_dump($semesters);
		
		if(!empty($semesters))
		{
			foreach($semesters as $sem)
			{
				echo "
				<table class=\"table table-striped table-hover table-bordered\">
					<thead>
						<tr>
							<th colspan=\"2\">" . ucfirst($sem["term"]) . " " . $sem["year"] . " [" . $sem["min_credits"] . "-" . $sem["max_credits"] . " Credit hours]</th>
						</tr>
					</thead>
					<tbody>";
				
				foreach($sem["selection"] as $course)
				{
					$classes = "";
					if($course["coursename"] == "none")
					{
						$courselist = $course["courselist"];
						$classCount = count($courselist);
						$k = 1;
						
						foreach($courselist as $class)
						{
							//Get each prereq
							$sth->bindParam(":cid", $class);
							$sth->execute();
							$rownum = $sth->rowCount();
							
							if($rownum > 0)
							{
								$courserow = $sth->fetch(PDO::FETCH_ASSOC);
								$prereq = $courserow["prereq_course_id"];
	             //Parse prereq
	              $prereq = implode(",", unwrap($prereq)); 
							}
							
							
							
							$classes .= $class . (isset($prereq) ? "(" .  $prereq . ")" : "");
							if($k++ != $classCount)
								$classes .= ", ";
						}
					}
					else
						$classes = $course["coursename"];
					
					echo "
						<tr>
							<td class=\"span8\">" . $course["coursegroup"] . "</td>
							<td>" . strtoupper($classes) . "</td>
						</tr>";
				}
				
				echo "
					</tbody>
				</table>";
				
			}
		}
		else {
			echo '<div class="alert alert-error"><h3>Sorry, a schedule cannot be created.</h3><br>
						Try again with different:<br>
						<ul>
							<li> Course preferences</li>
							<li> Number of credits per semesters </li>
							<li> Graduation semester </li>
						</ul></div>';
		}
	}
	else {
					echo '<div class="alert alert-error">post not set</div>';
	}
?>
			
			<footer>
				<p>© Study Planner 2013</p>
			</footer>
		</div>
	</body>
</html>
