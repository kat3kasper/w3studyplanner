<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Edit Course Group</title>
		<?php require("../includes/styles.php"); ?>
		<?php require("../includes/config.php"); ?>	
		<?php require("../includes/functions.php"); ?>
		
		<script type="text/javascript">
			//Popup window code
			function newPopup(url)
			{
				popupWindow = window.open(url,'popUpWindow', 'height=500, width=600, left=10, top=10, resizable=yes,menubar=no, location=no, directories=no, status=yes');
			}
			
			//Get value from child window
			function GetValueFromChild(course)
			{
				document.getElementById('course').value = course;
			}
			
			//Add courses into list
			function addCourse()
			{
				var e1=document.getElementById('course');
				var e2=document.getElementById('courses');
				var o=document.createElement('option');
				o.value=e1.value;
				o.text=e1.value;
				
				for(var i = 0; i < e2.length; i++)
					{
						if(e2.options[i].value === o.value)
						{
							alert("That course is already added.");
							return false;
						}
					}
					
				if (e1.value==null || e1.value=="")//check for empty form
				{
				  alert("Please input course id!");
				  return false;
				}
				else if(/\s+/.test(e1.value))//check for white spaces 
				{
					alert("Please check for whitespaces!\n\ne.g. Input HUM103 instead of HUM  103");
					return false;
				}
				else if(/[^a-z0-9]+/i.test(e1.value))//check for non alphanumeric characters
				{
					alert("Please check for non-alphanumeric characters!\n\ne.g. @#$ are not allowed!");
					return false;
				}
				else 
				{
					e2.options.add(o);
				}
			
			}
			
			//Remove course from the list
			function removeCourse()
			{
				var x=document.getElementById("courses");
				x.remove(x.selectedIndex);
			}
			
			//select all courses in the list to be stored in database
			function selectAllCourses()
			{
				var x=document.getElementById("courses");
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
				<li class="active"><a href="cgroups-edit.php">Edit Course Group</a></li>
				<li><a href="cgroups-delete.php">Delete Course Group</a></li>
			</ul>
			
			<hr/>
			
<?php		
	//If edited form of course group is submitted
	if(isset($_POST["submit"]) && (!empty($_POST["cgname"]) && !empty($_POST["course_id"])))
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
		$cgname = strtoupper(s_string($_POST["cgname"]));
		$ocgname = strtoupper(s_string($_POST["oldcgname"]));
		$course_id = implode(",", $_POST["course_id"]); //comma separate courses before storing into db
		
		
		//Check for duplicates
		$sql = "SELECT * FROM course_group WHERE name = :cgname AND name != :ocgname";
		
		$sth = $dbh->prepare($sql);
		$sth->bindParam(":cgname", $cgname);
		$sth->bindParam(":ocgname", $ocgname);
		$sth->execute();
		
		$rownum = $sth->rowCount();
		
		if($rownum)
			echo "Course group with that name already exists in database. Please be sure to name your course group uniquely. <br/>\n";
		else
		{
			//Update course
			$sql = "UPDATE course_group SET name= :cgname, course_id = :course_id WHERE name= :ocgname";
			
			$sth = $dbh->prepare($sql);
			
			$sth->bindParam(":cgname", $cgname);
			$sth->bindParam(":course_id", $course_id);	
			$sth->bindParam(":ocgname", $ocgname);
			$sth->execute();
					
			echo "Changes saved successfully.<br/>\n";
		}
		
	}
	else if(isset($_POST["submit"]) && !empty($_POST["cgroup"])) //if edit course group form is submitted
	{
		//Setup database
		$host = DB_HOST;
		$dbname = DB_NAME;
		$user = DB_USER;
		$pass = DB_PASS;
		
		$dbh = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $user, $pass);
		$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		//Extract values
		$cgroup = strtolower(addslashes(strip_tags($_POST["cgroup"])));
	
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
			$row = $sth->fetch(PDO::FETCH_ASSOC);
			
			$cgname = $row["name"];
			$courses = explode(",",$row["course_id"]);
			
			echo "Replace the details below with new values:<br/><br/>\n";
?>
			
			<form class="form-horizontal" action="cgroups-edit.php" method="POST">
				<div class="control-group">
					<label class="control-label" for="CGName">Course Group Name</label>
					<div class="controls">
						<input type="text" name="cgname" id="CGName" placeholder="CS_Group_A_Literature/Philosophy" class="input-xlarge" value="<?php if(isset($cgname)) echo $cgname; ?>"> 
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label" for="course">Add Course to The List</label>
					<div class="controls">
						<input type="text" name="course" id="course" class="input-small" placeholder="e.g. CS101" />
						<a href="Javascript:newPopup('courses-find.php');"><button type="button" class="btn btn-info">Find</button></a>	
					</div>
				</div>
				
				<div class="control-group">
					<div class="controls">
						<button class="btn btn-small" type="button" value="Add to List" onclick="addCourse()">Add Course to Course Group</button>
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label" for="Courses">Courses in the Course Group</label>
					<div class="controls">
						<p>
						<select multiple="multiple" name="course_id[]" id="courses" class="input-xlarge">
						<?php
							foreach ($courses as $course_id) 
							{
								echo "<option value=\"$course_id\">" .$course_id. "</option>\n";
							}
									
						?>
						</select>
						</p>
					</div>
				</div>
				
				<div class="control-group">
					<div class="controls">
						<button class="btn btn-small" type="button" onclick="removeCourse()" value="Remove course">Remove Course from Course Group</button>
					</div>
				</div>
				
				<div class="control-group">
					<div class="controls">
						<input type="hidden" name="oldcgname" value="<?php echo $cgname; ?>">
						<button type="submit" name="submit" class="btn btn-primary" onclick="selectAllCourses()">Save Changes</button>
						<button type="submit" name="submit" class="btn" onclick="/cgroups-edit.php">Cancel</button>
					</div>
				</div>
				
			</form>
<?php
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
		$rowarray = $sth->fetchAll(PDO::FETCH_ASSOC);
				
		if(!$rownum)
			echo "There is no course group available.<br/>\n";
		else
		{
?>
			
			<h4>Edit Course Group</h4>
			<p>Please select course group and click <em>"Edit Course Group"</em> button.</p>
			
			<form class="form-horizontal" method="post" action="cgroups-edit.php">
				<div class="control-group">
					<label class="control-label" for="CourseGroup">Select Course Group</label>
					<div class="controls">
						<select name="cgroup" id="cgroup" >
							<option value="">--Course Groups--</option>
							
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
							<button name="submit" type="submit" class="btn btn-primary">Edit Course Group</button>
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