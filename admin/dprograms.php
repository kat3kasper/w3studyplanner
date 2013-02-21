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
				echo "<p>Welcome, " . $_ENV["REDIRECT_displayName"] . "</p>";
			?>
			
			<ul class="nav nav-tabs">
				<li><a href="/studyplanner/admin">Admin Home</a></li>
				<li class="active"><a href="dprograms.php">Degree Programs</a></li>
				<li><a href="courses.php">Courses</a></li>
				<li><a href="cgroups.php">Course Groups</a></li>
			</ul>
			
			<h4>Degree Programs</h4>
			<p>Please enter the degree program name and click <em>"Submit"</em> button.</p>
			
			<form class="form-horizontal">
				<div class="control-group">
					<label class="control-label" for="DegreePrograms">Name of Degree Program</label>
					<div class="controls">
						<input type="text" id="DegreePrograms" class="input-xlarge" placeholder="e.g. CS_2011.START_WITH_CS105" />
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<button type="submit" class="btn btn-primary">Add Degree Program</button>
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