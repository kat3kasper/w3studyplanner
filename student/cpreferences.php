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
		
		//To use in current page
		$step1InfoArray = json_decode(htmlspecialchars_decode($step1Info), true);
		$step2InfoArray = json_decode(htmlspecialchars_decode($step2Info), true);
		
		$yearEntered = s_int($step1InfoArray["yearEntered"]);
		$degreeName = s_string($step1InfoArray["degreeName"]);
		$yearGraduate = s_int($step2InfoArray["yearGraduate"]);
		
		//Data from previous page
		$step3Info = htmlspecialchars(json_encode(array(
			"maxCredits" => $_POST["maxCredits"],
			"minCredits" => $_POST["minCredits"]
		)));
?>
			
			<h4>Course Preferences</h4>
			
			<form class="form-horizontal" method="post" action="cschedule.php">
				<div class="row-fluid">
					<div id="formLeft" class="span4">
						<div class="control-group">
							<div class="controls">
								Course
							</div>
						</div>
					</div>
					<div id="formLeft" class="span1">
						Completed?
					</div>
					<div id="formLeft" class="span2">
						Preferred term
					</div>
					<div id="formLeft" class="span2">
						Preferred year
					</div>
				</div>
				
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
			
			$sql = "SELECT course_id FROM course_group where name = :cgname";
			$sth = $dbh->prepare($sql);
			
			$groupNum = 0;
			$groupList = array();
			$groupCourses = array();
			
			//Each course group
			foreach($reqArray as $pairs)
			{
				$temp = explode(" FROM ", $pairs);
				$numCourses = $temp[0];
				$cgName = $temp[1];
				$groupList[] = $cgName;
			
				$sth->bindParam(":cgname", $cgName);
				$sth->execute();
				$rownum = $sth->rowCount();
						
				if(!$rownum)
					echo "The course group is not available.<br/>\n";
				else
				{
					$row = $sth->fetch(PDO::FETCH_ASSOC);
					
					$courses = explode(",", $row["course_id"]);
					$groupCourses[] = $courses;
					
					for($currCourse = 0; $currCourse < $numCourses; $currCourse++)
					{
						echo "<div class=\"row-fluid\">
								<div id=\"formLeft\" class=\"span4\">
									<div class=\"control-group\">"
									. ($currCourse == 0 ? "<label class=\"control-label\">" . $cgName . "</label>" : "") . "<div class=\"controls\">";
						
						echo "<select name=\"group[" . $groupNum . "][" . $currCourse . "][]\" id=\"group" . $groupNum . "Course" . $currCourse . "\" class=\"span8\" onChange=\"updateTerm(this.value, " . $groupNum . ", " . $currCourse . ");\">
								<option value=\"\">---</option>";
						
						foreach($courses as $course)
							echo "<option value=\"" . $course . "\">" . $course . "</option>";
						
						echo "</select>
										</div>
									</div>
								</div>
								<div id=\"formLeft\" class=\"span1\">";
						
						echo "<input type=\"checkbox\" name=\"group[" . $groupNum . "][" . $currCourse . "][]\" id=\"completed\" value=\"completed\" onChange=\"toggleTerm(this.checked, " .$groupNum . ", " . $currCourse . ")\" />
									</div>
								<div id=\"formLeft\" class=\"span2\">";
						
						echo "<select name=\"group[" . $groupNum . "][" . $currCourse . "][]\" id=\"group" . $groupNum . "Course" . $currCourse . "Term\" class=\"span8\">
										<option value=\"\">---</option>
									</select>
								</div>
								<div id=\"formLeft\" class=\"span2\">";
						
						echo "<select name=\"group[" . $groupNum . "][" . $currCourse . "][]\" id=\"group" . $groupNum . "Course" . $currCourse . "Year\" class=\"span8\">
										<option value=\"\">---</option>";

						$year = $yearEntered - 1;
						while($year++ < $yearGraduate)
							echo "<option value=\"" . $year . "\">" . $year . "</option>";

						echo "</select>
								</div>
							</div>";
					}
				}
				
				//Increment number of course groups so far for id
				$groupNum++;
			}
			
			$groupList = htmlspecialchars(json_encode($groupList));
			$groupCourses = htmlspecialchars(json_encode($groupCourses));
?>
				
				<div class="control-group">
					<div class="controls">
						<input type="hidden" name="step1Info" value="<?php echo $step1Info; ?>">
						<input type="hidden" name="step2Info" value="<?php echo $step2Info; ?>">
						<input type="hidden" name="step3Info" value="<?php echo $step3Info; ?>">
						<input type="hidden" name="groupList" value="<?php echo $groupList; ?>">
						<input type="hidden" name="groupCourses" value="<?php echo $groupCourses; ?>">
						<button type="submit" name="step5" class="btn btn-primary">Next</button>
					</div>
				</div>
			</form>
			
<?php
		}
	}
	else
		header("Location: index.php");
?>
			
			<footer>
				<p>© Study Planner 2013</p>
			</footer>
		</div>
	</body>
</html>