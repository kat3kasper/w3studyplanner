<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Courses</title>
		<?php require("../includes/styles.php"); ?>
	</head>
	<body>
		<?php require("../includes/navigation.php"); ?>
		
		<div class="container">
			<?php
				echo "<p>Welcome, " . $_SERVER["REDIRECT_displayName"] . "</p>";
			?>
			
			<ul class="nav nav-tabs">
				<li><a href="index.php">Admin Home</a></li>
				<li><a href="dprograms.php">Degree Programs</a></li>
				<li class="active"><a href="courses.php">Courses</a></li>
				<li><a href="cgroups.php">Course Groups</a></li>
			</ul>
			
			<ul class="nav nav-pills">
				<li><a href="courses-add.php">Add Course</a></li>
				<li><a href="courses-edit.php">Edit Course</a></li>
				<li><a href="courses-delete.php">Delete Course</a></li>
			</ul>

			<footer>
				<p>© Study Planner 2013</p>
			</footer>	
		</div>
		
		<?php require("../includes/scripts.php"); ?>
	</body>
</html>