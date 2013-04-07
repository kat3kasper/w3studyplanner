<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Add Requirement</title>
		<?php require("../includes/styles.php"); ?>
		<?php require("../includes/config.php"); ?>
		<?php require("../includes/functions.php"); ?>
		
		<script type="text/javascript">
		//Add constraint
		function addNumCourses()
		{
			var e1 = document.getElementById('NumberOfCourses');
			var e2=document.getElementById('constraints');
			var o=document.createElement('option');
			o.value=e1.value.concat(' FROM');
			o.text=e1.value.concat(' FROM');
			
			if (e1.value==null || e1.value=="")//check for empty form
			{
			  alert("Please input number of courses!");
			  return false;
			}
			else if(/[^0-9]+/i.test(e1.value))//check for non numeric characters
			{
				alert("Please check for non-numeric characters!\n\ne.g. @#$Abc are not allowed!");
				return false;
			}
			else 
			{
				
				/*var from = document.createElement("option");
				from.text = "FROM";
				from.value = "from";
				var select = document.getElementById("constraints");*/
				e2.options.add(o);
				//select.appendChild(from);
			}

		}
		
		function addCourseGroup()
		{
			var e1 = document.getElementById('cgroup');
			var e2=document.getElementById('constraints');
			var o=document.createElement('option');
			o.value=e1.value;
			o.text=e1.value;
			
			if (e1.value==null || e1.value=="")//check for empty form
			{
			  alert("Please select a course group first");
			  return false;
			}
			else 
			{
				e2.options.add(o);
				//select.appendChild(from);
			}

		}
		
		function addOperator()
		{
			var e1 = document.getElementById('operator');
			var e2=document.getElementById('constraints');
			var o=document.createElement('option');
			o.value=e1.value;
			o.text=e1.value;
			
			if (e1.value==null || e1.value=="")//check for empty form
			{
			  alert("Please select an operator first");
			  return false;
			}
			else 
			{
				e2.options.add(o);
				//select.appendChild(from);
			}

		}
		
		//Remove a constraint from the list
		function removeConstraint()
		{
			var e2=document.getElementById('constraints');
			var o=document.createElement('option');
			//o.value=e1.value;
			//o.text=e1.value;
			
			if (e2.value==null || e2.value=="")//check for empty form
			{
			  alert("Please select a constraint from the list above first");
			  return false;
			}
			
			var ce=confirm("Are you sure you want to remove the constraint from the requirements?");
			if(ce===true)
			{
				e2.remove(e2.selectedIndex);
			}
			else
			{
				return false;
			}
			
		}
		
		//select all courses in the list to be stored in database
		function selectAllConstraints()
		{
			var x=document.getElementById("constraints");
			for (var i=0; i<x.length; i++) 
			{
				x.options[i].selected = true;
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
	if(isset($_POST["submit"]) && (!empty($_POST["reqname"]) && !empty($_POST["constraints"])) )
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
		$reqname = strtoupper(s_string($_POST["reqname"]));
		$constraints = s_string(implode("|", $_POST["constraints"]));
		
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
			
			<form class="form-horizontal" action="requirements-add.php" method="POST">
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
						<input type="text" id="NumberOfCourses" class="input-large"/>
						<button type="button" class="btn btn-info" onclick="addNumCourses()">Add</button>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="From">FROM</label>
				</div>
				<div class="control-group">
					<label class="control-label" for="CourseGroup">Select Course Group</label>
					<div class="controls">
						<select name="cgroup" id="cgroup" >
						<?php
							foreach ($cgroup_arr as $row) 
							{
								$cgname = $row['name'];
								echo "<option value=\"$cgname\">" .$cgname. "</option>\n";
							}
						?>
						</select>
						<button name="cgroup-add" type="button" class="btn btn-info" onclick="addCourseGroup()">Add</button>
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
						<button name="operator-add" type="button" class="btn btn-info" onclick="addOperator()">Add</button>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						
						<select multiple="multiple" name="constraints[]" id="constraints" class="input-xlarge" size="6">
						</select>
					
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<a class="btn btn-small btn-danger" id="remove-btn" rel="tooltip" data-placement="right" data-trigger="hover" title="Select a constraint from the list above" onclick="removeConstraint()" value="Remove constraint">Remove Constraint</a>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<button type="submit" name="submit" class="btn btn-primary" onclick="selectAllConstraints()">Add Requirement</button>
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
		<script>
		$(function ()
		{ $('#remove-btn').tooltip();
		});
		</script>
	</body>
</html>