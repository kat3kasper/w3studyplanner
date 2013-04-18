<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Course Preferences</title>
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
			
<?php
	//Coming from semester setup
	if(isset($_POST["step4"]))
	{
		$step1Info = json_decode(htmlspecialchars_decode($_POST["step1Info"]));
		$step2Info = json_decode(htmlspecialchars_decode($_POST["step2Info"]));
		
		$yearEntered = s_int($step1Info[0]);
		$degreeName = s_string($step1Info[2]);
		$yearGraduate = s_int($step2Info[1]);
		
		
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
		
		$sql = "SELECT degree_requirements FROM degree WHERE name = :name";
				
		$sth = $dbh->prepare($sql);
		$sth->bindParam(":name", $degreeName);
		$sth->execute();
		$rownum = $sth->rowCount();
				
		if(!$rownum)
			echo "Degree program doesn't exist.<br/>\n";
		else
		{
			$row = $sth->fetch(PDO::FETCH_ASSOC);
			$reqArray = explode(",", $row);
			
			$sql = "SELECT course_id FROM course_group where name = :cgname";
			$sth = $dbh->prepare($sql);
			
			//Each course group
			foreach($reqArray as $pairs)
			{
				$temp = explode("|", $pairs);
				$numCourses = $temp[0];
				$cgName = $temp[1];
			
				$sth->bindParam(":cgname", $cgName);
				$sth->execute();
				$rownum = $sth->rowCount();
						
				if(!$rownum)
					echo "The course group is not available.<br/>\n";
				else
				{
					$row = $sth->fetch(PDO::FETCH_ASSOC);
					
					$courses = explode(",", $row["course_id"]);
					
					for(; $numCourses > 0; $numCourses--)
					{
						echo "<div class=\"row-fluid\">
								<div id=\"formLeft\" class=\"span4\">
									<div class=\"control-group\">"
									. ($numCourses === $temp[0] ? "<label class=\"control-label\">" . $cgName . "</label>" : "") . 
										"<div class=\"controls\">
											<select name=\"course\" id=\"course\" class=\"span8\">
												<option value=\"\">---</option>";
						
						foreach($courses as $course)
							echo "<option value=\"" . $course . "\">" . $course . "</option>";
						
						echo "</select>
										</div>
									</div>
								</div>
								<div id=\"formLeft\" class=\"span1\">
									<input type=\"checkbox\" name=\"completed\" id=\"completed\" value=\"completed\" />
								</div>
								<div id=\"formLeft\" class=\"span2\">
									<select name=\"term\" id=\"term\" class=\"span8\">
										<option value=\"\">---</option>
										<option value=\"spring\">Spring</option>
										<option value=\"summer\">Summer</option>
										<option value=\"Fall\">Fall</option>
									</select>
								</div>
								<div id=\"formLeft\" class=\"span2\">
									<select name=\"year\" id=\"year\" class=\"span8\">
										<option value=\"\">---</option>";

						$year = $yearEntered - 1;
						while($year++ < $yearGraduate)
							echo "<option value=\"" . $year . "\">" . $year . "</option>";

						echo "</select>
								</div>
							</div>";
					}
				}
			}
			
			echo "</form>";
		}
	}
?>
			
			<footer>
				<p>© Study Planner 2013</p>
			</footer>
		</div>
	</body>
</html>