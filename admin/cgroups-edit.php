<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Edit Course Group</title>
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
				<li class="active"><a href="cgroups.php">Course Groups</a></li>
			</ul>
			
			<ul class="nav nav-pills">
				<li><a href="cgroups-add.php">Add Course Group</a></li>
				<li class="active"><a href="cgroups-edit.php">Edit Course Group</a></li>
				<li><a href="cgroups-delete.php">Delete Course Group</a></li>
			</ul>

			<hr/>
			
			<h4>Edit Course Group</h4>
			<p>Please select course group and click <em>"Edit Course Group"</em> button.</p>
			
			<form class="form-horizontal">
				<div class="control-group">
					<label class="control-label" for="CourseGroup">Select Course Group</label>
					<div class="controls">
						<select></select>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<button type="submit" class="btn btn-primary">Edit Course Group</button>
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