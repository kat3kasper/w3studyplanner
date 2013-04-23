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
	if(isset($_POST["step5"]))
	{
		$step1Info = json_decode(htmlspecialchars_decode($_POST["step1Info"]), true);
		$step2Info = json_decode(htmlspecialchars_decode($_POST["step2Info"]), true);
		$step3Info = json_decode(htmlspecialchars_decode($_POST["step3Info"]), true);
		$groupList = json_decode(htmlspecialchars_decode($_POST["groupList"]));
		$groupCourses = json_decode(htmlspecialchars_decode($_POST["groupCourses"]));
		
		$termGraduate = $step2Info["termGraduate"];
		$yearGraduate = $step2Info["yearGraduate"];
		$maxCredits = $step3Info["maxCredits"];
		$minCredits = $step3Info["minCredits"];
		
		$groups = $_POST["group"];
		
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
		
		$yearDiff = $yearGraduate - $currYear;
		$termDiff = array_search($termGraduate, $termNames) - $currTerm;
		$totalTerms = abs(($yearDiff * 4) - $termDiff) + 1;
		
		$semester = array();
		
		for($termPoint = $currTerm, $yearPoint = $currYear, $termCount = 0; $termCount < $totalTerms; $termCount++)
		{
			$pref = array();
			
			$i = 0;
			$k = 0;
			while(isset($groups[$i]))
			{
				$j = 0;
				while(isset($groups[$i][$j]))
				{
					if(isset($groups[$i][$j][2]) && isset($groups[$i][$j][1]))
					{
						if($groups[$i][$j][2] == $yearPoint && $groups[$i][$j][1] == $termNames[$termPoint])
						{
							$pref[$k]["coursegroup"] = $groupList[$i];
							$pref[$k]["coursename"] = $groups[$i][$j][0];
							$k++;
						}
					}
					$j++;
				}
				$i++;
			}
			
			$term = array(
				"term" => $termNames[$termPoint],
				"year" => $yearPoint,
				"min_credits" => $minCredits[$yearPoint][$termNum[$termPoint]],
				"max_credits" => $maxCredits[$yearPoint][$termNum[$termPoint]],
				"preferences" => $pref
			);
			
			$semester[] = $term;
			
			$termPoint--;
			if($termPoint == 0)
			{
				$termPoint = 4;
				$yearPoint++;
			}
		}
		
		$requirements = array();
		foreach(
		
		
		$sem = array(
			$term, $term
		);
		
		$all = array(
			"semesters" => $sem,
			"requirements" => array(
				$cg, $cg
			),
			"transcript" => array()
		);
		
		echo count($semester) . "semesters<br/>";
		echo htmlspecialchars(json_encode($semester));
	}
?>
			
			<ul class="pager">
				<li><a href="#">Back</a></li>
				<li><a href="#">Download Schedule</a></li>
			</ul>
			
			<footer>
				<p>© Study Planner 2013</p>
			</footer>
		</div>
		
		<?php require("../includes/scripts.php"); ?>
	</body>
</html>