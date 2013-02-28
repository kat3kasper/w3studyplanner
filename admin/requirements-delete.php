<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Delete Requirement</title>
		<?php require("../includes/styles.php"); ?>
		<?php require("../includes/config.php"); ?>
		<?php require("../includes/functions.php"); ?>
		

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
				<li><a href="cgroups.php">Course Groups</a></li>
				<li class="active"><a href="requirements.php">Requirements</a></li>
			</ul>
			
			<ul class="nav nav-pills">
				<li><a href="requirements-add.php">Add Requirement</a></li>
				<li><a href="requirements-edit.php">Edit Requirement</a></li>
				<li class="active"><a href="requirements-delete.php">Delete Requirement</a></li>
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
		if(isset($_POST["submit"]) && !empty($_POST["requirement"]))
		{
			
			//Extract values
			$requirement = strtolower(s_string($_POST["requirement"]));
		
			//Check with database
			$sql = "SELECT * FROM requirements WHERE requirement_name = :requirement";
			
			$sth = $dbh->prepare($sql);
			if(!empty($requirement))
				$sth->bindParam(":requirement", $requirement);
			
			$sth->execute();
			$rownum = $sth->rowCount();
			//echo "Results: " . $rownum . " courses.<br/>\n";
			
			if(!$rownum)
				echo "There is no course group available.<br/>\n";
			else
			{
				$sql = "DELETE FROM requirements WHERE requirement_name = :requirement";
				$sth = $dbh->prepare($sql);
				$sth->bindParam(":requirement", $requirement);
				
				$sth->execute();
				
				echo "Requirement has been deleted.";
			}
				
		}
		else
		{
			$sql = "SELECT requirement_name FROM requirements";
			
			$sth = $dbh->prepare($sql);
			
			$sth->execute();
			$rownum = $sth->rowCount();
			$rowarray = $sth->fetchAll(PDO::FETCH_ASSOC);
					
			if(!$rownum)
				echo "There is no requirement available.<br/>\n";
			else
			{
?>
			<h4>Delete Requirement</h4>
			<p>Please select requirement and click <em>"Delete Requirement"</em> button.</p>
			
			<form class="form-horizontal" method="post" action="requirements-delete.php">
				<div class="control-group">
					<label class="control-label" for="RequirementName">Select Requirement</label>
					<div class="controls">
						<select name="requirement" id="requirement" >
							<option value="">-- Requirements --</option>
						
<?php
				foreach ($rowarray as $row) 
				{
					echo "<option value=\"$row[requirement_name]\">" .$row[requirement_name]. "</option>\n";
				}
				
?>
						</select>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<button name="submit" type="submit" class="btn btn-primary">Delete Requirement</button>
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