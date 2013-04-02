<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Degree Programs</title>
		<?php require("../includes/styles.php"); ?>
	</head>
	<body>
		<?php require("../includes/navigation.php"); ?>
		
		<div class="container">
			<?php
				echo "<p>Welcome, " . $_SERVER["REDIRECT_displayName"] . "</p>";
			?>
			
			<ul class="nav nav-tabs">
				<li><a href="/studyplanner/student">Student Home</a></li>
				<li class="active"><a href="dprograms.php">Degree Programs</a></li>
				<li><a href="fcourses.php">Finished Courses</a></li>
				<li><a href="ssetup.php">Semester Setup</a></li>
				<li><a href="cpreferences.php">Course Preferences</a></li>
				<li><a href="cschedule.php">Construct Schedule</a></li>
			</ul>
			
			<h4>Degree Programs</h4>
			
			<form class="form-horizontal">
				<div class="control-group">
					<label class="control-label" for="YearEntered">Please select the year you entered school</label>
					<div class="controls">
						<select>
							<?php					
								for($year = date("Y"); $year > 2005; $year--)
									echo "<option>$year</option>"; 
							?>
						</select>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="Department">Please select the department your major is under</label>
					<div class="controls">
						<select>
							<option>CS</option>
							<option>IS</option>
							<option>CyS</option>
						</select>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="DegreeProgram">Please select your degree program</label>
					<div class="controls">
						<select>
							<option> </option>
						</select>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<button type="submit" class="btn btn-primary">Next</button>
					</div>
				</div>
			</form>
			
			<footer>
				<p>© Study Planner 2013</p>
			</footer>
		</div>
		
		<?php require("../includes/scripts.php"); ?>
	</body>
</html>