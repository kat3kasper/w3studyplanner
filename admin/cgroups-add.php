<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Add Course Group</title>
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
				var ce=confirm("Are you sure you want to remove the course from the course group?");
				if(ce===true)
				{
					x.remove(x.selectedIndex);
				}
				else
				{
					return false;
				}
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
				<li class="active"><a href="cgroups-add.php">Add Course Group</a></li>
				<li><a href="cgroups-edit.php">Edit Course Group</a></li>
				<li><a href="cgroups-delete.php">Delete Course Group</a></li>
			</ul>
			
			<hr/>
			
<?php
	//If form is submitted
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
		$course_id = implode(",", $_POST["course_id"]); //comma separate courses before storing into db
		
		//Check for duplicates
		$sql = "SELECT * FROM course_group WHERE name = :cgname";
		
		$sth = $dbh->prepare($sql);
		$sth->bindParam(":cgname", $cgname);
		$sth->execute();
		$rownum = $sth->rowCount();
		
		if($rownum)
			echo "Course group already exists in database. If this is incorrect, please be sure to name your course group correctly. <br/>\n";
		else
		{
			//Insert to course_group table
			$sql = "INSERT INTO course_group(name, course_id) VALUES (:cgname , :course_id)";
			
			$sth = $dbh->prepare($sql);
			
			$sth->bindParam(":cgname", $cgname);
			$sth->bindParam(":course_id", $course_id);	

			$sth->execute();
					
			echo "Course group successfully added.<br/>\n";
		}
	}
	else
	{
?>
			
			<h4>Add Course Group</h4>
			<p>Please input new course group details and click <em>"Add Course Group"</em> button.</p>
			
			<form class="form-horizontal" action="cgroups-add.php" method="POST">
				<div class="control-group">
					<label class="control-label" for="CGName">New Course Group Name</label>
					<div class="controls">
						<input type="text" name="cgname" id="CGName" placeholder="CS_Group_A_Literature/Philosophy" class="input-xlarge"> 
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
						<button type="submit" name="submit" class="btn btn-primary" onclick="selectAllCourses()">Add Course Group</button>
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