<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Semester Setup</title>
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
				<li class="active"><a href="ssetup.php">Semester Setup</a></li>
				<li><a href="cpreferences.php">Course Preferences</a></li>
				<li><a href="cschedule.php">Construct Schedule</a></li>
			</ul>
			
			<h4>Semester Setup</h4>
			
			<form class="form-horizontal">
				<div class="control-group">
					<label class="control-label">Please enter the semester you wish to graduate</label>
					<div class="controls">
						<select>
							<option>Spring</option>
							<option>Fall</option>
							<option>Summer</option>
							<option>Fall</option>
							<option>Winter</option>
						</select>
						<select>
							<?php					
								for($year = date("Y"); $year < 2060; $year++)
									echo "<option>$year</option>"; 
							?>
						</select>
					</div>
				</div>
			</form>
			<p>Please choose how many credits you wish to take per semester:</p>
			
			
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