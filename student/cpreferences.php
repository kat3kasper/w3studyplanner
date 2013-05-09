<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Course Preferences</title>
		<?php require("../includes/styles.php"); ?>
		<?php require("../includes/config.php"); ?>
		<?php require("../includes/functions.php"); ?>
		<?php require("../includes/scripts.php"); ?>
		
		<script>
			function updateTerm(course, groupNum, currCourse)
			{
				if(course == "")
				{
					document.getElementById("group" + groupNum + "Course" + currCourse + "Term").innerHTML = "<option value=\"\">---</option>";
					return;
				}
				
				var xmlhttp;
				
				//Modern browsers
				if(window.XMLHttpRequest)
					xmlhttp = new XMLHttpRequest();
				//IE5 & 6
				else
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				
				xmlhttp.onreadystatechange = function()
				{
					if(xmlhttp.readyState == 4 && xmlhttp.status == 200)
					{
						var res = xmlhttp.responseText;
						document.getElementById("group" + groupNum + "Course" + currCourse + "Term").innerHTML = res;
					}
				}
				
				xmlhttp.open("GET", "../includes/cpref_update.php?course=" + course, true);
				xmlhttp.send();
			}
			
			function toggleTerm(checked, groupNum, currCourse)
			{
				var x = document.getElementById("group" + groupNum + "Course" + currCourse + "Term");
				var y = document.getElementById("group" + groupNum + "Course" + currCourse + "Year");
				
				if(checked)
					x.disabled = y.disabled = true;
				else
					x.disabled = y.disabled = false;
			}
		</script>
	</head>
	<body>
		<?php require("../includes/navigation.php"); ?>
		
		<div class="container">
			<?php
				echo "<p>Welcome, " . $_SERVER["REDIRECT_displayName"] . "</p>";
			?>
			
<?php
	//Coming from semester setup
	if(isset($_POST["step4"]))
	{
		//To pass info to next page
		$step1Info = htmlspecialchars($_POST["step1Info"]);
		$step2Info = htmlspecialchars($_POST["step2Info"]);
		
		//Data from previous page
		$step3Info = htmlspecialchars(json_encode(array(
			"maxCredits" => $_POST["maxCredits"],
			"minCredits" => $_POST["minCredits"]
		)));
		
		$step1InfoArray = json_decode(htmlspecialchars_decode($step1Info), true);
		$department = s_string($step1InfoArray["department"]);
?>
			
			<h4>Course Preferences: Science Requirements</h4>
			
			<form class="form-horizontal" method="post" action="cpreferences.php">
				
<?php
		if($department == "computer")
		{
?>
				<div class="control-group">
					<label class="control-label">Choose the group that you've taken/wish to take</label>
					<div class="controls">
						<select class="span4" name="scReq" id="scReq">
							<option value="1">Group 1 (PEP111, PEP112, PEP221)</option>
							<option value="2">Group 2 (CH115, CH116, CH117)</option>
							<option value="3">Group 3 (CH115, CH281, CH117)</option>
							<option value="4">Group 4 (CH115, CH281, CH282)</option>
							<option value="5">Group 5 (PEP111, CH281, CH282)</option>
						</select>
					</div>
				</div>
				
<?php
		}
		else
		{
?>
				<p>Science Requirements are applicable to Computer Science undergraduates only.</p>
				<p>Please click next to continue:</p>
				<div class="control-group">
					<div class="controls">
						<input type="hidden" name="step1Info" value="<?php echo $step1Info; ?>">
						<input type="hidden" name="step2Info" value="<?php echo $step2Info; ?>">
						<input type="hidden" name="step3Info" value="<?php echo $step3Info; ?>">
						<button type="submit" name="step5" class="btn btn-primary">Next</button>
					</div>
				</div>
			</form>
			
<?php
		}
	}
	else if(isset($_POST["step5"]))
	{
		//To pass info to next page
		$step1Info = htmlspecialchars($_POST["step1Info"]);
		$step2Info = htmlspecialchars($_POST["step2Info"]);
		$step3Info = htmlspecialchars($_POST["step3Info"]);
		
		//To use in current page
		$step1InfoArray = json_decode(htmlspecialchars_decode($step1Info), true);
		$step2InfoArray = json_decode(htmlspecialchars_decode($step2Info), true);
		
		$yearEntered = s_int($step1InfoArray["yearEntered"]);
		$degreeName = s_string($step1InfoArray["degreeName"]);
		$yearGraduate = s_int($step2InfoArray["yearGraduate"]);
		if(isset($_POST["scReq"]))
			$scReqNum = s_int($_POST["scReq"]);
		else
			$scReqNum = 0;
?>
			
			<h4>Course Preferences</h4>
			
			<form class="form-horizontal" method="post" action="cschedule.php">
				<table class="table table-hover">
					<thead>
						<tr>
							<th></th>
							<th>Course</th>
							<th>Completed</th>
							<th>Preferred term</th>
							<th>Preferred year</th>
						</tr>
					</thead>
					<tbody>
				
<?php
		//Setup database
		$host = DB_HOST;
		$dbname = DB_NAME;
		$user = DB_USER;
		$pass = DB_PASS;
		
		$dbh = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $user, $pass);
		$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		$sql = "SELECT degree_requirements FROM degree WHERE degree_name = :name";
				
		$sth = $dbh->prepare($sql);
		$sth->bindParam(":name", $degreeName);
		$sth->execute();
		$rownum = $sth->rowCount();
				
		if(!$rownum)
			echo "Degree program doesn't exist.<br/>\n";
		else
		{
			$row = $sth->fetch(PDO::FETCH_ASSOC);
			$reqArray = explode(",", $row["degree_requirements"]);
			
			$groupNum = 0;
			$groupList = array();
			$groupCourses = array();
			
			$scReq = array();
			
			//Each course group
			foreach($reqArray as $pairs)
			{
				$temp = explode(" FROM ", $pairs);
				$numCourses = $temp[0];
				$cgName = $temp[1];
				
				if(strpos($cgName, "SCIENCE REQUIRED COURSES ") === 0)
					if($cgName != "SCIENCE REQUIRED COURSES " . $scReqNum)
						continue;
				
				$sql = "SELECT course_id FROM course_group where name = :cgname";
				$sth = $dbh->prepare($sql);
			
				$sth->bindParam(":cgname", $cgName);
				$sth->execute();
				$rownum = $sth->rowCount();
						
				if(!$rownum)
					echo "The course group is not available.<br/>\n";
				else
				{
					$row = $sth->fetch(PDO::FETCH_ASSOC);
					
					$courses = explode(",", $row["course_id"]);
					
					//To be sent to next step
					$groupList[] = $cgName;
					$groupCourses[] = $courses;
					
					for($currCourse = 0; $currCourse < $numCourses; $currCourse++)
					{
						echo "<tr>";
						echo "<td>" . ($currCourse == 0 ? $cgName : "") . "</td>";
						
						//If required courses is equal to course group size,
						//list all courses as read only
						if($numCourses == count($courses))
						{
							echo "<td><input type=\"text\" name=\"group[" . $groupNum . "][" . $currCourse . "][]\" id=\"group" . $groupNum . "Course" . $currCourse . "\" value=\"" . strtoupper($courses[$currCourse]) . "\" readonly /></td>";
						}
						else
						{
							echo "<td><select name=\"group[" . $groupNum . "][" . $currCourse . "][]\" id=\"group" . $groupNum . "Course" . $currCourse . "\" onChange=\"updateTerm(this.value, " . $groupNum . ", " . $currCourse . ");\"><option value=\"\">---</option>";
						
							foreach($courses as $course)
								echo "<option value=\"" . $course . "\">" . strtoupper($course) . "</option>";
							
							echo "</select></td>";
						}
						
						echo "<td><input type=\"checkbox\" name=\"group[" . $groupNum . "][" . $currCourse . "][]\" id=\"completed\" value=\"completed\" onChange=\"toggleTerm(this.checked, " .$groupNum . ", " . $currCourse . ")\" /></td>";
						
						//List term that the course is only available in
						if($numCourses == count($courses))
						{
							echo "<td><select name=\"group[" . $groupNum . "][" . $currCourse . "][]\" id=\"group" . $groupNum . "Course" . $currCourse . "Term\"><option value=\"\">---</option>";
							
							$sql = "SELECT on_campus_semesters, web_campus_semesters FROM course WHERE CONCAT(prefix, number) = :course";
			
							$sth = $dbh->prepare($sql);
							$sth->bindParam(":course", $courses[$currCourse]);
							$sth->execute();
							$rownum = $sth->rowCount();
							
							if($rownum)
							{
								$row = $sth->fetch(PDO::FETCH_ASSOC);
								
								$ocs = $row["on_campus_semesters"];
								if(!empty($ocs))
								{
									$ocarr = explode(",", $ocs);
									foreach($ocarr as $val)
										echo "<option value=\"" . $val . "\">" . ucfirst($val) . "</option>";
								}
								
								$wcs = $row["web_campus_semesters"];
								if(!empty($wcs))
								{
									$wcarr = explode(",", $wcs);
									foreach($wcarr as $val)
										echo "<option value=\"" . $val . "\">WC " . ucfirst($val) . "</option>";
								}
							}
							
							echo "</select></td>";
						}
						else
							echo "<td><select name=\"group[" . $groupNum . "][" . $currCourse . "][]\" id=\"group" . $groupNum . "Course" . $currCourse . "Term\"><option value=\"\">---</option></select></td>";
						
						echo "<td><select name=\"group[" . $groupNum . "][" . $currCourse . "][]\" id=\"group" . $groupNum . "Course" . $currCourse . "Year\"><option value=\"\">---</option>";

						$year = $yearEntered - 1;
						while($year++ < $yearGraduate)
							echo "<option value=\"" . $year . "\">" . $year . "</option>";

						echo "</select></td>";
						echo "</tr>";
					}
				}
				
				//Increment number of course groups so far for id
				$groupNum++;
			}
			
			$groupList = htmlspecialchars(json_encode($groupList));
			$groupCourses = htmlspecialchars(json_encode($groupCourses));
?>
					
					</tbody>
				</table>
				<div class="control-group">
					<div class="controls">
						<input type="hidden" name="step1Info" value="<?php echo $step1Info; ?>">
						<input type="hidden" name="step2Info" value="<?php echo $step2Info; ?>">
						<input type="hidden" name="step3Info" value="<?php echo $step3Info; ?>">
						<input type="hidden" name="groupList" value="<?php echo $groupList; ?>">
						<input type="hidden" name="groupCourses" value="<?php echo $groupCourses; ?>">
						<div class="alert alert-info"><b>Note:</b> Making the right schedule will take about a minute. </div>
						<button type="submit" name="step6" class="btn btn-primary">Next</button>
					</div>
				</div>
			</form>
			
<?php
		}
	}
	else
		header("Location: http://stevens.edu/studyplanner/index.php");
?>
			
			<footer>
				<p>© Study Planner 2013</p>
			</footer>
		</div>
	</body>
</html>