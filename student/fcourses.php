<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Finished Courses</title>
		<?php require("../includes/styles.php"); ?>
	</head>
	<body>
		<?php require("../includes/navigation.php"); ?>
		
		<div class="container">
			<?php
				echo "<p>Welcome, " . $_SERVER["REDIRECT_displayName"] . "</p>";
			?>
			
			<ul class="nav nav-tabs">
				<li><a href="index.php">Student Home</a></li>
				<li><a href="dprograms.php">Degree Programs</a></li>
				<li class="active"><a href="fcourses.php">Finished Courses</a></li>
				<li><a href="ssetup.php">Semester Setup</a></li>
				<li><a href="cpreferences.php">Course Preferences</a></li>
				<li><a href="cschedule.php">Construct Schedule</a></li>
			</ul>
			
			<p><button class="btn btn-small" type="button">Upload Classes from Unofficial Transcript</button>
			
			<button class="btn btn-small" type="button">Upload Classes from Previously Saved Schedule</button></p>
			
			<h4>Finished Courses</h4>
			<p>Please indicate completed courses (Only include ones which you have passed):</p>
			
			<form class="form-horizontal">
				<div class="control-group">
					<label class="control-label" for="CSCourses">Required CS Courses</label>
					<div class="controls">
						<p>Course group description written by administrator</p>
						<button class="btn btn-small" type="button">+</button>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="PE">Physical Education Requirements</label>
					<div class="controls">
						<p>Course group description written by administrator</p>
						<button class="btn btn-small" type="button">+</button>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="TechnicalElectives">Technical Electives</label>
					<div class="controls">
						<p>Course group description written by administrator</p>
						<button class="btn btn-small" type="button">+</button>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<button type="submit" class="btn btn-primary">Save</button>
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