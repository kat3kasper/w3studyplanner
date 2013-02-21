<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Add Course Group</title>
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
				<li class="active"><a href="cgroups-add.php">Add Course Group</a></li>
				<li><a href="cgroups-edit.php">Edit Course Group</a></li>
				<li><a href="cgroups-delete.php">Delete Course Group</a></li>
			</ul>
			
			<hr/>
			
			<h4>Add Course Group</h4>
			<p>Please select course group and click <em>"Add Course Group"</em> button.</p>
			
			<form class="form-horizontal">
				<div class="control-group">
					<label class="control-label" for="CGName">Course Group Name</label>
					<div class="controls">
						<input type="text" id="CGName"> 
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="Courses">Courses present in Course Group</label>
					<div class="controls">
						<p><textarea rows="3"></textarea></p>
						<p><a href="#"><button type="button" class="btn btn-small">Add Course to Course Group</button></a></p>
						<p><a href="#"><button type="button" class="btn btn-small">Delete Course from Course Group</button></a></p>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<button type="submit" class="btn btn-primary">Add Course Group</button>
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