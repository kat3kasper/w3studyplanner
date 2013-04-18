<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Delete Course Group</title>
		<?php require("../includes/styles.php"); ?>
		<?php require("../includes/config.php"); ?>	
		<?php require("../includes/functions.php"); ?>
		<?php require("../includes/scripts.php"); ?>
		
		<script>
			//Validates form inputs
			function validateForm()
			{
				var w = document.getElementById("cgroup");
				//Check for empty fields
				if (w.value==null || w.value=="")
				{
					alert("Please select a Course Group from the list!");
					return false;
				}
				else
				{
					confirm("Are you sure you want to delete the selected Course Group?");
				}
			}
		</script>
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
		//Setup database
			$host = DB_HOST;
			$dbname = DB_NAME;
			$user = DB_USER;
			$pass = DB_PASS;
				
			$dbh = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $user, $pass);
			$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
			$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		
		//If form is submitted & course group is not empty
		if(isset($_POST["submit"]))
		{
			
			//Extract values
			$cgroup = strtoupper(s_string($_POST["cgroup"]));
		
			//Check with database
			$sql = "SELECT * FROM course_group WHERE name = :cgroup";
			
			$sth = $dbh->prepare($sql);
			if(!empty($cgroup))
				$sth->bindParam(":cgroup", $cgroup);
			
			$sth->execute();
			$rownum = $sth->rowCount();
			
			if(!$rownum)
				echo "There is no course group available.<br/>\n";
			else
			{
				$sql = "DELETE FROM course_group WHERE name = :cgroup";
				$sth = $dbh->prepare($sql);
				$sth->bindParam(":cgroup", $cgroup);
				
				$sth->execute();
				
?>
			<div class="alert alert-success alert-block">
				 <button type="button" class="close" data-dismiss="alert"></button>
				 <h4>There She Goes...</h4>
				<p><?php echo $cgroup ?> has been deleted</p>
			</div>
<?php				
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
			<div class="well">
			<h4>Delete Course Group</h4>
			<div class="alert alert-info">
				<button type="button" class="close" data-dismiss="alert"></button>
				<p>Please select course group and click <em>"Delete Course Group"</em> button.</p>
			</div>
			
			<form class="form-horizontal" method="post" action="cgroups-delete.php" onsubmit="return validateForm()">
				<div class="control-group">
					<label class="control-label" for="CourseGroup">Select Course Group</label>
					<div class="controls">
						<select name="cgroup" id="cgroup" class="span4">
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
		
				<div class="form-actions">
				 <a href="dprograms-delete.php"><button name="submit" type="submit" class="btn btn-primary">Delete Course Group</button></a>
				</div>
			</form>
			</div>
<?php
			}
		}	
?>
			<footer>
				<p>© Study Planner 2013</p>
			</footer>
		</div>	
	</body>
</html>