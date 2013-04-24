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
	//if(isset($_POST["step5"]))
	{
		$step1Info = json_decode(htmlspecialchars_decode($_POST["step1Info"]), true);
		$step2Info = json_decode(htmlspecialchars_decode($_POST["step2Info"]), true);
		$step3Info = json_decode(htmlspecialchars_decode($_POST["step3Info"]), true);
		$groupList = json_decode(htmlspecialchars_decode($_POST["groupList"])); //Name of course groups
		$groupCourses = json_decode(htmlspecialchars_decode($_POST["groupCourses"])); //Grouped courses
		
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
					//If year and term is set
					if(isset($groups[$i][$j][2]) && isset($groups[$i][$j][1]))
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
					else if(isset($groups[$i][$j][1]) && $groups[$i][$j][1] == "completed")
						if(!in_array($groups[$i][$j][0], $transcript))
							$transcript[] = $groups[$i][$j][0];
					
					$j++;
				}
				$i++;
			}
			
			//Build term array
			$term = array(
				"term" => $termNames[$termPoint],
				"year" => $yearPoint,
				"min_credits" => $minCredits[$yearPoint][$termNum[$termPoint]],
				"max_credits" => $maxCredits[$yearPoint][$termNum[$termPoint]],
				"preferences" => $preferences
			);
			
			$semesters[] = $term;
			
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
		foreach($groupCourses as $aGroup)
		{
			foreach($aGroup as $aCourse)
			{
				array_push($requirements, array(
					"coursegroup" => $groupList[$n],
					"coursename" => $aCourse
				));
			}
			$n++;
		}
		
		//Build the json to send to constraint solver
		$jsonString = json_encode(array(
			"semesters" => $semesters,
			"requirements" => $requirements,
			"transcript" => $transcript
		), JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
		
		
		//CONNECT TO CONSTRAINT SOLVER
	
	
		//Receive output from constraint solver
		$jsonString = '{
			"semesters": [
				{
					"term": "fall",
					"year": 2009,
					"min_credits": 12,
					"max_credits": 18,
					"selection": [
						{
							"coursegroup": "COMPUTER SCIENCE REQUIRED COURSES",
							"coursename": "cs146",
							"classlist": [
							]
						},
						{
							"coursegroup": "MATH REQUIRED COURSES",
							"coursename": "ma115",
							"classlist": [
							]
						},
						{
							"coursegroup": "BUSINESS TECHNOLOGY REQUIRED COURSES",
							"coursename": "bt330",
							"classlist": [
							]
						},
						{
							"coursegroup": "HUMANITIES GROUP A",
							"coursename": "none",
							"classlist": [
								"hum103",
								"hum104"
							]
						}
					]
				},
				{
					"term": "spring",
					"year": 2010,
					"min_credits": 12,
					"max_credits": 18,
					"selection": [
						{
							"coursegroup": "COMPUTER SCIENCE REQUIRED COURSES",
							"coursename": "cs284",
							"classlist": [
							]
						},
						{
							"coursegroup": "MATH REQUIRED COURSES",
							"coursename": "ma116",
							"classlist": [
							]
						},
						{
							"coursegroup": "MATH SCIENCE ELECTIVES",
							"coursename": "ma221",
							"classlist": [
							]
						},
						{
							"coursegroup": "HUMANITIES GROUP B",
							"coursename": "none",
							"classlist": [
								"hum103",
								"cs334"
							]
						}
					]
				}
			],
			"requirements": [
				{
					"coursegroup": "COMPUTER SCIENCE REQUIRED COURSES",
					"coursename": "cs115"
				},
				{
					"coursegroup": "COMPUTER SCIENCE REQUIRED COURSES",
					"coursename": "cs146"
				},
				{
					"coursegroup": "COMPUTER SCIENCE REQUIRED COURSES",
					"coursename": "cs135"
				},
				{
					"coursegroup": "COMPUTER SCIENCE REQUIRED COURSES",
					"coursename": "cs284"
				},
				{
					"coursegroup": "COMPUTER SCIENCE REQUIRED COURSES",
					"coursename": "cs334"
				},
				{
					"coursegroup": "COMPUTER SCIENCE REQUIRED COURSES",
					"coursename": "cs383"
				},
				{
					"coursegroup": "COMPUTER SCIENCE REQUIRED COURSES",
					"coursename": "cs385"
				},
				{
					"coursegroup": "COMPUTER SCIENCE REQUIRED COURSES",
					"coursename": "cs347"
				},
				{
					"coursegroup": "COMPUTER SCIENCE REQUIRED COURSES",
					"coursename": "cs392"
				},
				{
					"coursegroup": "COMPUTER SCIENCE REQUIRED COURSES",
					"coursename": "cs442"
				},
				{
					"coursegroup": "COMPUTER SCIENCE REQUIRED COURSES",
					"coursename": "cs506"
				},
				{
					"coursegroup": "COMPUTER SCIENCE REQUIRED COURSES",
					"coursename": "cs496"
				},
				{
					"coursegroup": "COMPUTER SCIENCE REQUIRED COURSES",
					"coursename": "cs511"
				},
				{
					"coursegroup": "COMPUTER SCIENCE REQUIRED COURSES",
					"coursename": "cs488"
				},
				{
					"coursegroup": "COMPUTER SCIENCE REQUIRED COURSES",
					"coursename": "cs492"
				},
				{
					"coursegroup": "COMPUTER SCIENCE REQUIRED COURSES",
					"coursename": "cs423"
				},
				{
					"coursegroup": "COMPUTER SCIENCE REQUIRED COURSES",
					"coursename": "cs424"
				},
				{
					"coursegroup": "SCIENCE REQUIRED COURSES 3",
					"coursename": "ch115"
				},
				{
					"coursegroup": "SCIENCE REQUIRED COURSES 3",
					"coursename": "ch281"
				},
				{
					"coursegroup": "SCIENCE REQUIRED COURSES 3",
					"coursename": "ch117"
				},
				{
					"coursegroup": "MATH REQUIRED COURSES",
					"coursename": "ma115"
				},
				{
					"coursegroup": "MATH REQUIRED COURSES",
					"coursename": "ma116"
				},
				{
					"coursegroup": "MATH REQUIRED COURSES",
					"coursename": "ma222"
				},
				{
					"coursegroup": "MATH REQUIRED COURSES",
					"coursename": "ma331"
				},
				{
					"coursegroup": "BUSINESS TECHNOLOGY REQUIRED COURSES",
					"coursename": "bt330"
				},
				{
					"coursegroup": "TECHNICAL ELECTIVES",
					"coursename": "ssw533"
				},
				{
					"coursegroup": "TECHNICAL ELECTIVES",
					"coursename": "ssw564"
				},
				{
					"coursegroup": "TECHNICAL ELECTIVES",
					"coursename": "ssw565"
				},
				{
					"coursegroup": "TECHNICAL ELECTIVES",
					"coursename": "ssw567"
				},
				{
					"coursegroup": "TECHNICAL ELECTIVES",
					"coursename": "ssw687"
				},
				{
					"coursegroup": "TECHNICAL ELECTIVES",
					"coursename": "ssw689"
				},
				{
					"coursegroup": "SOFTWARE DEVELOPMENT ELECTIVES",
					"coursename": "cs516"
				},
				{
					"coursegroup": "SOFTWARE DEVELOPMENT ELECTIVES",
					"coursename": "cs521"
				},
				{
					"coursegroup": "SOFTWARE DEVELOPMENT ELECTIVES",
					"coursename": "cs522"
				},
				{
					"coursegroup": "SOFTWARE DEVELOPMENT ELECTIVES",
					"coursename": "cs526"
				},
				{
					"coursegroup": "SOFTWARE DEVELOPMENT ELECTIVES",
					"coursename": "cs537"
				},
				{
					"coursegroup": "SOFTWARE DEVELOPMENT ELECTIVES",
					"coursename": "cs541"
				},
				{
					"coursegroup": "SOFTWARE DEVELOPMENT ELECTIVES",
					"coursename": "cs546"
				},
				{
					"coursegroup": "SOFTWARE DEVELOPMENT ELECTIVES",
					"coursename": "cs549"
				},
				{
					"coursegroup": "SOFTWARE DEVELOPMENT ELECTIVES",
					"coursename": "cs558"
				},
				{
					"coursegroup": "MATH SCIENCE ELECTIVES",
					"coursename": "ma221"
				},
				{
					"coursegroup": "MATH SCIENCE ELECTIVES",
					"coursename": "ma227"
				},
				{
					"coursegroup": "MATH SCIENCE ELECTIVES",
					"coursename": "ch243"
				},
				{
					"coursegroup": "MATH SCIENCE ELECTIVES",
					"coursename": "ch244"
				},
				{
					"coursegroup": "FREE ELECTIVES",
					"coursename": "ma221"
				},
				{
					"coursegroup": "FREE ELECTIVES",
					"coursename": "ma227"
				},
				{
					"coursegroup": "FREE ELECTIVES",
					"coursename": "bt100"
				},
				{
					"coursegroup": "FREE ELECTIVES",
					"coursename": "bt353"
				},
				{
					"coursegroup": "FREE ELECTIVES",
					"coursename": "bt360"
				},
				{
					"coursegroup": "FREE ELECTIVES",
					"coursename": " mis201"
				},
				{
					"coursegroup": "FREE ELECTIVES",
					"coursename": "ch243"
				},
				{
					"coursegroup": "FREE ELECTIVES",
					"coursename": "ch244"
				},
				{
					"coursegroup": "HUMANITIES GROUP A",
					"coursename": "hum103"
				},
				{
					"coursegroup": "HUMANITIES GROUP A",
					"coursename": "hum104"
				},
				{
					"coursegroup": "HUMANITIES GROUP A",
					"coursename": "hli113"
				},
				{
					"coursegroup": "HUMANITIES GROUP A",
					"coursename": "hli114"
				},
				{
					"coursegroup": "HUMANITIES GROUP A",
					"coursename": "hli117"
				},
				{
					"coursegroup": "HUMANITIES GROUP A",
					"coursename": "hli118"
				},
				{
					"coursegroup": "HUMANITIES GROUP A",
					"coursename": "hmu192"
				},
				{
					"coursegroup": "HUMANITIES GROUP A",
					"coursename": "hmu193"
				},
				{
					"coursegroup": "HUMANITIES GROUP A",
					"coursename": "hpl111"
				},
				{
					"coursegroup": "HUMANITIES GROUP A",
					"coursename": "hpl112"
				},
				{
					"coursegroup": "HUMANITIES GROUP B",
					"coursename": "hum103"
				},
				{
					"coursegroup": "HUMANITIES GROUP B",
					"coursename": "hum104"
				},
				{
					"coursegroup": "HUMANITIES GROUP B",
					"coursename": "hum107"
				},
				{
					"coursegroup": "HUMANITIES GROUP B",
					"coursename": "hum108"
				},
				{
					"coursegroup": "HUMANITIES GROUP B",
					"coursename": "hum288"
				},
				{
					"coursegroup": "HUMANITIES GROUP B",
					"coursename": "har190"
				},
				{
					"coursegroup": "HUMANITIES GROUP B",
					"coursename": "har191"
				},
				{
					"coursegroup": "HUMANITIES GROUP B",
					"coursename": "hhs123"
				},
				{
					"coursegroup": "HUMANITIES GROUP B",
					"coursename": "hhs124"
				},
				{
					"coursegroup": "HUMANITIES GROUP B",
					"coursename": "hhs125"
				},
				{
					"coursegroup": "HUMANITIES GROUP B",
					"coursename": "hhs126"
				},
				{
					"coursegroup": "HUMANITIES GROUP B",
					"coursename": "hhs129"
				},
				{
					"coursegroup": "HUMANITIES GROUP B",
					"coursename": "hhs130"
				},
				{
					"coursegroup": "HUMANITIES GROUP B",
					"coursename": "hhs135"
				},
				{
					"coursegroup": "HUMANITIES GROUP B",
					"coursename": "hmu101"
				},
				{
					"coursegroup": "HUMANITIES GROUP B",
					"coursename": "hmu102"
				},
				{
					"coursegroup": "HUMANITIES GROUP B",
					"coursename": "hss121"
				},
				{
					"coursegroup": "HUMANITIES GROUP B",
					"coursename": "hss122"
				},
				{
					"coursegroup": "HUMANITIES GROUP B",
					"coursename": "hss127"
				},
				{
					"coursegroup": "HUMANITIES GROUP B",
					"coursename": "hss128"
				},
				{
					"coursegroup": "HUMANITIES GROUP B",
					"coursename": "hss175"
				},
				{
					"coursegroup": "HUMANITIES GROUP B",
					"coursename": "hss176"
				},
				{
					"coursegroup": "HUMANITIES UPPER LEVEL",
					"coursename": "hss371"
				},
				{
					"coursegroup": "HUMANITIES UPPER LEVEL",
					"coursename": "hss377"
				},
				{
					"coursegroup": "HUMANITIES UPPER LEVEL",
					"coursename": "hss458"
				},
				{
					"coursegroup": "HUMANITIES UPPER LEVEL",
					"coursename": "hhs415"
				},
				{
					"coursegroup": "HUMANITIES UPPER LEVEL",
					"coursename": "hhs476"
				},
				{
					"coursegroup": "HUMANITIES UPPER LEVEL",
					"coursename": "bt243"
				},
				{
					"coursegroup": "HUMANITIES UPPER LEVEL",
					"coursename": "bt244"
				}
			],
			"transcript": [
				"hum103",
				"hum104"
			]
		}';
		
		$decodedString = json_decode($jsonString, true);
		
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
					$classlist = $course["classlist"];
					$classCount = count($classlist);
					$k = 1;
					
					foreach($classlist as $class)
					{
						//Get each prereq
						$sth->bindParam(":cid", $class);
						$sth->execute();
						$rownum = $sth->rowCount();
						
						if($rownum)
							$prereq = $sth->fetch(PDO::FETCH_ASSOC)["prereq_course_id"];
						
						//parse prereq first
						//still writing parser for prereq
						
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
?>
			
			<footer>
				<p>© Study Planner 2013</p>
			</footer>
		</div>
	</body>
</html>