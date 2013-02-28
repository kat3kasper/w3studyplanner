<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Add Requirement</title>
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
				<li class="active"><a href="requirements-add.php">Add Requirement</a></li>
				<li><a href="requirements-edit.php">Edit Requirement</a></li>
				<li><a href="requirements-delete.php">Delete Requirement</a></li>
			</ul>
			
			<hr/>
			
<?php
	//If add degree program form is submitted
	if(isset($_POST["submit"]) && !empty($_POST["requirements"]))
	{
		//Setup database
		$host = DB_HOST;
		$dbname = DB_NAME;
		$user = DB_USER;
		$pass = DB_PASS;
		
		$dbh = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $user, $pass);
		$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		//Sanitiza & extract values
		$reqname = strtolower(s_string($_POST["reqname"]));
		$constraints = s_string(implode(",", $_POST["constraints"]));
		
		//Check for duplicates
		$sql = "SELECT * FROM requirements WHERE requirement_name = :reqname";
		
		$sth = $dbh->prepare($sql);
		$sth->bindParam(":reqname", $reqname);
		$sth->execute();
		
		$rownum = $sth->rowCount();
		
		if($rownum)
			echo "Requirement already exists in database.<br/>\n";
		else
		{
			//Insert to requirement
			$sql = "INSERT INTO requirements(requirement_name, constraints) VALUES (:reqname, :constraints)";
			
			$sth = $dbh->prepare($sql);
			
			$sth->bindParam(":reqname", $reqname);
			$sth->bindParam(":constraints", $constraints);
			
			$sth->execute();
			
			echo "Requirement is successfully added.<br/>\n";
		
		}
	}
	else
	{
	//Setup database
		$host = DB_HOST;
		$dbname = DB_NAME;
		$user = DB_USER;
		$pass = DB_PASS;
		
		$dbh = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $user, $pass);
		$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		$sql = "SELECT name FROM course_group";
		
		$sth = $dbh->prepare($sql);
		$sth->execute();
		$rownum = $sth->rowCount();
		
		if(!$rownum)
			echo "There are no course groups to add to the requirement.<br/>\n";
		else
		{
			$cgroup_arr = $sth->fetchAll(PDO::FETCH_ASSOC);
	
?>

		
		
		
			
			<h4>Add Requirement</h4>
			
			<form class="form-horizontal" action="requirements-add.php" method="post">
				<div class="control-group">
					<label class="control-label" for="RequirementName">Requirement Name</label>
					<div class="controls">
						<input type="text" name="reqname" id="RequirementName" class="input-large"/>
					</div>
				</div>
				<h5>Please add the following to the requirement box below to form a valid combination.</h5>
				<br/>
				<div class="control-group">
					<label class="control-label" for="NumberOfCourses">Number of Courses</label>
					<div class="controls">
						<input type="text" name="numcourse" id="NumberOfCourses" class="input-small"/>
					</div>
				</div>
				<p>from</p>
				<div class="control-group">
					<label class="control-label" for="CourseGroup">Select Course Group</label>
					<div class="controls">
						<select name="cgroup" id="cgroup" >
						<?php
							foreach ($cgroup_arr as $row) 
							{
								echo "<option value=\"$row[name]\">" .$row[name]. "</option>\n";
							}
						?>
						</select>
						<button name="cgroup-add" type="button" class="btn btn-info">Add</button>
					</div>					
				</div>
				<div class="control-group">
				<label class="control-label" for="Operators">Select Operator</label>
					<div class="controls">
						<select name="operator" id="operator">
							<option>AND</option>
							<option>OR</option>
							<option>NOT</option>
							<option>(</option>
							<option>)</option>
							<option>+ (Set Addition)</option>
							<option>- (Set Substraction)</option>
						</select>
						<button name="operator-add" type="button" class="btn btn-info">Add</button>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<textarea name="constraints" rows="4"></textarea>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<button type="submit" name="submit" class="btn btn-primary">Save</button>
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