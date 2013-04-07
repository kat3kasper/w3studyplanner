<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Edit Requirement</title>
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
				<li><a href="requirements-add.php">Add Requirement</a></li>
				<li class="active"><a href="requirements-edit.php">Edit Requirement</a></li>
				<li><a href="requirements-delete.php">Delete Requirement</a></li>
			</ul>
			
			<hr/>
			
			
<?php
	//If edited form of requirement is submitted
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
		
		//TODO: count required input
		
		//Sanitize & extract values
		$reqname = strtoupper(s_string($_POST["reqname"]));
		$constraints = s_string(implode("|", $_POST["constraints"]));
		$oreqname = strtoupper(s_string($_POST["oldreqname"]));

		
		
		//Check for duplicates
		$sql = "SELECT * FROM requirements WHERE requirement_name = :reqname AND requirement_name != :oreqname";
		
		$sth = $dbh->prepare($sql);
		$sth->bindParam(":reqname", $reqname);
		$sth->bindParam(":oreqname", $oreqname);
		$sth->execute();
		
		$rownum = $sth->rowCount();
		
		if($rownum)
			echo "Requirement with that name already exists in database. Please be sure to name your requirements uniquely. <br/>\n";
		else
		{
			//Update course
			$sql = "UPDATE requirements SET requirement_name= :reqname, constraints = :constraints WHERE requirement_name= :oreqname";
			
			$sth = $dbh->prepare($sql);
			
			$sth->bindParam(":reqname", $reqname);
			$sth->bindParam(":constraints", $constraints);	
			$sth->bindParam(":oreqname", $oreqname);
			$sth->execute();
					
			echo "Changes saved successfully.<br/>\n";
		}
	}
	else if(isset($_POST["submit"]) && !empty($_POST["reqname"])) //if edit requirement form is submitted
	{
	//Setup database
		$host = DB_HOST;
		$dbname = DB_NAME;
		$user = DB_USER;
		$pass = DB_PASS;
		
		$dbh = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $user, $pass);
		$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		$sql1 = "SELECT name FROM course_group";
		
		$sth1 = $dbh->prepare($sql1);
		$sth1->execute();
		$rownum1 = $sth1->rowCount();
		
		if(!$rownum1)
			echo "There are no course groups to add to the requirement.<br/>\n";
		else
		{
			$cgroup_arr1 = $sth1->fetchAll(PDO::FETCH_ASSOC);
			
			//Extract values
			$reqname = strtoupper(addslashes(strip_tags($_POST["reqname"])));
			//Check with database
			$sql2 = "SELECT * FROM requirements WHERE requirement_name = :reqname";
		
			$sth2 = $dbh->prepare($sql2);
			if(!empty($reqname))
			$sth2->bindParam(":reqname", $reqname);
		
			$sth2->execute();
			$rownum2 = $sth2->rowCount();
			
			if(!$rownum2)
			echo "There is no requirement available.<br/>\n";
			else
			{
				$row2 = $sth2->fetch(PDO::FETCH_ASSOC);
				
				$reqname = $row2["requirement_name"];
				$constraints = explode("|",$row2["constraints"]);
				
				echo "Replace the details below with new values:<br/><br/>\n";
	
?>
			<h4>Edit Requirement</h4>
			
			<form class="form-horizontal" action="requirements-edit.php" method="POST">
				<div class="control-group">
					<label class="control-label" for="RequirementName">Requirement Name</label>
					<div class="controls">
						<input type="text" name="reqname" id="RequirementName" class="input-large" value="<?php if(isset($reqname)) echo $reqname; ?>"/>
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
							foreach ($cgroup_arr1 as $row1) 
							{
								$cgname1 = $row1['name'];
								echo "<option value=\"$cgname1\">" .$cgname1. "</option>\n";
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
						<p>
						<select multiple="multiple" name="constraints[]" id="constraints" class="input-xlarge" size="6">
						<?php
							foreach ($constraints as $constraint) 
							{
								echo "<option value=\"$constraint\">" .$constraint. "</option>\n";
							}
									
						?>
						</select>
						</p>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<a class="btn btn-small btn-danger" id="remove-btn" rel="tooltip" data-placement="right" data-trigger="hover" title="Select a constraint from the list above" onclick="removeConstraint()" value="Remove constraint">Remove Constraint</a>
					</div>
				</div>
				
				<div class="control-group">
					<div class="controls">
						<input type="hidden" name="oldreqname" value="<?php echo $reqname; ?>">
						<button type="submit" name="submit" class="btn btn-primary" onclick="selectAllConstraints()">Save Changes</button>
						<button type="submit" name="cancel" class="btn" onclick="/requirements-edit.php">Cancel</button>
					</div>
				</div>
				
			</form>
<?php
			}
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
		
		$sql = "SELECT requirement_name FROM requirements";
			
		$sth = $dbh->prepare($sql);
		
		$sth->execute();
		$rownum = $sth->rowCount();
		$rowarray = $sth->fetchAll(PDO::FETCH_ASSOC);
				
		if(!$rownum)
			echo "There is no requirements available.<br/>\n";
		else
		{
?>
			
			<h4>Edit Requirement</h4>
			<p>Please select requirement and click <em>"Edit Requirement"</em> button.</p>
			
			<form class="form-horizontal" method="post" action="requirements-edit.php">
				<div class="control-group">
					<label class="control-label" for="Requirement">Select Requirement</label>
					<div class="controls">
						<select name="reqname" id="reqname" >
							<option value="">--Requirements--</option>
							
		<?php
			foreach ($rowarray as $row) 
			{
				$reqname = $row['requirement_name'];
				echo "<option value=\"$reqname\">" .$reqname. "</option>\n";
			}
					
		?>
						</select>
					</div>
				</div>
					<div class="control-group">
						<div class="controls">
							<button name="submit" type="submit" class="btn btn-primary">Edit Requirement</button>
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