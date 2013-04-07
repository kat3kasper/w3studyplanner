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
				<li><a href="index.php">Student Home</a></li>
				<li><a href="dprograms.php">Degree Programs</a></li>
				<li><a href="fcourses.php">Finished Courses</a></li>
				<li><a href="ssetup.php">Semester Setup</a></li>
				<li><a href="cpreferences.php">Course Preferences</a></li>
				<li class="active"><a href="cschedule.php">Construct Schedule</a></li>
			</ul>
			
			<h4>Semester Setup</h4>
			
			<p><button class="btn" type="button">Construct Schedule</button></p>
			
			<footer>
				<p>© Study Planner 2013</p>
			</footer>
		</div>
		
		<?php require("../includes/scripts.php"); ?>
	</body>
</html>