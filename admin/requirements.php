<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Requirements</title>
		<?php require("../includes/styles.php"); ?>
	</head>
	<body>
		<?php require("../includes/navigation.php"); ?>
		
		<div class="container">
			<?php
				echo "<p>Welcome, " . $_ENV["REDIRECT_displayName"] . "</p>";
			?>
			
			<ul class="nav nav-tabs">
				<li><a href="/studyplanner/admin">Admin Home</a></li>
				<li><a href="dprograms.php">Degree Programs</a></li>
				<li><a href="courses.php">Courses</a></li>
				<li ><a href="cgroups.php">Course Groups</a></li>
				<li class="active"><a href="requirements.php">Requirements</a></li>
			</ul>
			
			<ul class="nav nav-pills">
				<li><a href="requirements-add.php">Add Requirement</a></li>
				<li><a href="requirements-edit.php">Edit Requirement</a></li>
				<li><a href="requirements-delete.php">Delete Requirement</a></li>
			</ul>
			
			<footer>
				<p>© Study Planner 2013</p>
			</footer>
		</div>
		
		<?php require("../includes/scripts.php"); ?>
	</body>
</html>