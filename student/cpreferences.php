<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Course Preferences</title>
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
				<li><a href="fcourses.php">Finished Courses</a></li>
				<li><a href="ssetup.php">Semester Setup</a></li>
				<li class="active"><a href="cpreferences.php">Course Preferences</a></li>
				<li><a href="cschedule.php">Construct Schedule</a></li>
			</ul>
			
			<h4>Course Preferences</h4>
			
			<form class="form-horizontal">
				<div class="control-group">
					<label class="control-label">Basic Requirement</label>
					<div class="controls">
						<button class="btn btn-medium" type="button">Add Desired Course</button>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">Basic Requirement</label>
					<div class="controls">
						<button class="btn btn-medium" type="button">Add Desired Course</button>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">Basic Requirement</label>
					<div class="controls">
						<button class="btn btn-medium" type="button">Add Desired Course</button>
					</div>
				</div>
			</form>			
			
			<ul class="pager">
				<li><a href="#">Back</a></li>
				<li><a href="#">Save</a></li>
				<li><a href="#">Save and Continue</a></li>
			</ul>
			
			<footer>
				<p>© Study Planner 2013</p>
			</footer>
		</div>
		
		<?php require("../includes/scripts.php"); ?>
	</body>
</html>