<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Home</title>
		<?php require("includes/styles.php"); ?>
	</head>
	<body>
		<?php require("includes/navigation.php"); ?>
		
		<div class="container">
			<?php
				$member = substr($_SERVER["REDIRECT_unscoped_affiliation"],7);
				echo "<p>Welcome, " . $_SERVER["REDIRECT_displayName"] . "<br/>You are currently logged in as " . $member . "</p>";
				echo "<p>Use the site as:</p>";
			?>
			
			<p>
				<a href="admin"><button class="btn btn-primary" type="button">Administrator</button></a>
				<a href="student"><button class="btn" type="button">Student</button></a>
			</p>
			
			<p>You can also switch between the two from the navigation bar at the top.</p>
			
			<footer>
				<p>© Study Planner 2013</p>
			</footer>
		</div>
		
		<?php require("includes/scripts.php"); ?>
	</body>
</html>