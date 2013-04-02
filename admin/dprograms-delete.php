<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Delete Degree Program</title>
		<?php require("../includes/styles.php"); ?>
		<?php require("../includes/config.php"); ?>
		<?php require("../includes/functions.php"); ?>
		
	<script>
		function ConfirmDelete(delUrl) 
		{
			if(confirm("Are you sure you want to delete this degree program?"))
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
				<li class="active"><a href="dprograms.php">Degree Programs</a></li>
				<li><a href="courses.php">Courses</a></li>
				<li><a href="cgroups.php">Course Groups</a></li>
				<li><a href="requirements.php">Requirements</a></li>
			</ul>
			
			<ul class="nav nav-pills">
				<li><a href="dprograms-add.php">Add Degree Program</a></li>
				<li><a href="dprograms-edit.php">Edit Degree Program</a></li>
				<li class="active"><a href="dprograms-delete.php">Delete Degree Program</a></li>
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
		if(isset($_POST["submit"]) && !empty($_POST["degree"]))
		{
			
			//Extract values
			$degree = strtolower(addslashes(strip_tags($_POST["degree"])));
		
			//Check with database
			$sql = "SELECT * FROM degree WHERE degree_name = :degree";
			
			$sth = $dbh->prepare($sql);
			if(!empty($degree))
				$sth->bindParam(":degree", $degree);
			
			$sth->execute();
			$rownum = $sth->rowCount();
			//echo "Results: " . $rownum . " degree.<br/>\n";
			
			if(!$rownum)
				echo "There is no degree program available.<br/>\n";
			else
			{
				$sql = "DELETE FROM degree WHERE degree_name = :degree";
				$sth = $dbh->prepare($sql);
				$sth->bindParam(":degree", $degree);
				
				$sth->execute();
				
				echo $degree . " has been deleted.";
			}
				
		}
		else
		{
			$sql = "SELECT degree_name FROM degree";
			
			$sth = $dbh->prepare($sql);
			
			$sth->execute();
			$rownum = $sth->rowCount();
			$rowarray = $sth->fetchAll(PDO::FETCH_ASSOC);
					
			if(!$rownum)
				echo "There is no degree available.<br/>\n";
			else
			{
?>
			<h4>Delete Degree Program</h4>
			<p>Please select a degree program and click <em>"Delete Degree Program"</em> button.</p>
			
			<form class="form-horizontal" method="post" action="dprograms-delete.php">
				<div class="control-group">
					<label class="control-label" for="Degree">Select degree program</label>
					<div class="controls">
						<select name="degree" id="degree" >
							<option value="">-- Degree Program --</option>
						
<?php
				foreach ($rowarray as $row) 
				{
					echo "<option value=\"$row[degree_name]\">" .$row[degree_name]. "</option>\n";
				}
				
?>
						</select>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<a href="dprograms-delete.php" onclick="return confirm('Are you sure you want to delete this degree program?')"><button name="submit" type="submit" class="btn btn-primary">Delete Degree Program</button></a>
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