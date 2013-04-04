<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Delete Degree Program</title>
		<?php require("../includes/styles.php"); ?>
		<?php require("../includes/config.php"); ?>
		<?php require("../includes/functions.php"); ?>
		<?php require("../includes/scripts.php"); ?>
		
		<script type="text/javascript">
			//Validates form inputs
			function validateForm()
			{
				var w = document.getElementById("Degree");
				//Check for empty fields
				if (w.value==null || w.value=="")
				{
					alert("Please select a Degree Program from the list!");
					return false;
				}
				else
				{
					confirm("Are you sure you want to delete the selected Degree Program?");
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
		if(isset($_POST["submit"]))
		{
			//Extract values
			$degree = strtoupper(addslashes(strip_tags($_POST["degree"])));
		
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
?>
			<div class="alert alert-success alert-block">
				 <button type="button" class="close" data-dismiss="alert"></button>
				 <h4>There She Goes...</h4>
				<p><?php echo $degree ?> has been deleted</p>
			</div>
<?php				
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
			<div class="well">
			<h4>Delete Degree Program</h4>
			<div class="alert alert-info">
				<button type="button" class="close" data-dismiss="alert"></button>
				<p>Please select a degree program and click <em>"Delete Degree Program"</em> button.</p>
			</div>
			
			<form class="form-horizontal" method="post" action="dprograms-delete.php" onsubmit="return validateForm()">
				<div class="control-group">
					<label class="control-label" for="Degree">Select degree program</label>
					<div class="controls">
						<select name="degree" id="Degree" class="span4">
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
				
				<div class="form-actions">
				 <a href="dprograms-delete.php"><button name="submit" type="submit" class="btn btn-primary">Delete Degree Program</button></a>
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
		
		<?php require("../includes/scripts.php"); ?>
	</body>
</html>