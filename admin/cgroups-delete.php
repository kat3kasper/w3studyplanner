<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Delete Course Group</title>
		<?php require("../includes/styles.php"); ?>
		<?php require("../includes/config.php"); ?>	
		<?php require("../includes/functions.php"); ?>
		
		<script>
			function ConfirmDelete(delUrl) 
			{
				if(confirm("Are you sure you want to delete this course group?"))
				{
					document.location = delUrl;
				}
			}
		</script>
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
				<li><a href="requirements.php">Requirements</a></li>
			</ul>
			
			<ul class="nav nav-pills">
				<li><a href="cgroups-add.php">Add Course Group</a></li>
				<li><a href="cgroups-edit.php">Edit Course Group</a></li>
				<li class="active"><a href="cgroups-delete.php">Delete Course Group</a></li>
			</ul>

			<hr/>
			
<?php		
		//Setup database
			$host = DB_HOST;
			$dbname = DB_NAME;
			$user = DB_USER;
			$pass = DB_PASS;
				
			$dbh = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $user, $pass);
			$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
			$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		
		//If form is submitted & course group is not empty
		if(isset($_POST["submit"]) && !empty($_POST["cgroup"]))
		{
			
			//Extract values
			$cgroup = strtolower(s_string($_POST["cgroup"]));
		
			//Check with database
			$sql = "SELECT * FROM course_group WHERE name = :cgroup";
			
			$sth = $dbh->prepare($sql);
			if(!empty($cgroup))
				$sth->bindParam(":cgroup", $cgroup);
			
			$sth->execute();
			$rownum = $sth->rowCount();
			echo "Results: " . $rownum . " courses.<br/>\n";
			
			if(!$rownum)
				echo "There is no course group available.<br/>\n";
			else
			{
				$sql = "DELETE FROM course_group WHERE name = :cgroup";
				$sth = $dbh->prepare($sql);
				$sth->bindParam(":cgroup", $cgroup);
				
				$sth->execute();
				
				echo  $cgroup . " has been deleted.";
			}
				
		}
		else
		{
			$sql = "SELECT name FROM course_group";
			
			$sth = $dbh->prepare($sql);
			
			$sth->execute();
			$rownum = $sth->rowCount();
			$rowarray = $sth->fetchAll(PDO::FETCH_ASSOC);
					
			if(!$rownum)
				echo "There is no course group available.<br/>\n";
			else
			{
?>
			<h4>Delete Course Group</h4>
			<p>Please select course group and click <em>"Delete Course Group"</em> button.</p>
			
			<form class="form-horizontal" method="post" action="cgroups-delete.php">
				<div class="control-group">
					<label class="control-label" for="CourseGroup">Select Course Group</label>
					<div class="controls">
						<select name="cgroup" id="cgroup" >
							<option value="">--Course Group--</option>
						
<?php
				foreach ($rowarray as $row) 
				{
					echo "<option value=\"$row[name]\">" .$row[name]. "</option>\n";
				}
				
?>
						</select>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<a href="cgroups-delete.php" onclick="return confirm('Are you sure you want to delete this course group?')"><button name="submit" type="submit" class="btn btn-primary">Delete Course Group</button></a>
					</div>
				</div>
			</form>
<?php
			}
		}	
?>
			
			<footer>
				<p>© Study Planner 2013</p>
			</footer>
		</div>
		
		<?php require("../includes/scripts.php"); ?>	
	</body>
</html>