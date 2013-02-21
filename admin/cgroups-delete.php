<?php require("../includes/config.php"); ?>
<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Delete Course Group</title>
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
				<li><a href="cgroups-edit.php">Edit Course Group</a></li>
				<li class="active"><a href="cgroups-delete.php">Delete Course Group</a></li>
			</ul>

			<hr/>
			
<?php		
		//If form is submitted & course group is not empty
		if(isset($_POST["submit"]) && !empty($_POST["CourseGroup"]))
		{
			//Setup database
			$host = DB_HOST;
			$dbname = DB_NAME;
			$user = DB_USER;
			$pass = DB_PASS;
				
			$dbh = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $user, $pass);
			
			//Extract values
			$cgroup = strtolower(addslashes(strip_tags($_POST["CourseGroup"])));
		
			//Check with database
			$sql = "SELECT * FROM course_group WHERE name = :cgroup";
			
			$sth = $dbh->prepare($sql);
			if(!empty($cgroup))
				$sth->bindParam(":cgroup", $cgroup);
			
			$sth->execute();
			echo "Course group has been deleted";
		}
		else
		{
?>
			<h4>Delete Course Group</h4>
			<p>Please select course group and click <em>"Delete Course Group"</em> button.</p>
			
			<form class="form-horizontal">
				<div class="control-group">
					<label class="control-label" name="CourseGroup" for="CourseGroup">Select Course Group</label>
					<div class="controls">
						<select>
							<option>humanities</option>
						</select>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<button type="submit" class="btn btn-primary">Delete Course Group</button>
					</div>
				</div>
			</form>
<?php
		}
?>
			
			<footer>
				<p>© Study Planner 2013</p>
			</footer>
		</div>
		
		<?php require("../includes/scripts.php"); ?>	
	</body>
</html>