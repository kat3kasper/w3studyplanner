<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Delete Course</title>
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
				document.getElementById('CourseId').value = course;
			}
			
			function ConfirmDelete(delUrl) 
			{
				if(confirm("Are you sure you want to delete this course?"))
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
				<li class="active"><a href="courses.php">Courses</a></li>
				<li><a href="cgroups.php">Course Groups</a></li>
				<li><a href="requirements.php">Requirements</a></li>
			</ul>
			
			<ul class="nav nav-pills">
				<li><a href="courses-add.php">Add Course</a></li>
				<li><a href="courses-edit.php">Edit Course</a></li>
				<li class="active"><a href="courses-delete.php">Delete Course</a></li>
			</ul>
			
			<hr/>
			
<?php
	//If delete form is submitted & course is not empty
	if(isset($_POST["submit"]) && !empty($_POST["courseid"]))
	{
		
		//Setup database
		$host = DB_HOST;
		$dbname = DB_NAME;
		$user = DB_USER;
		$pass = DB_PASS;
		
		$dbh = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $user, $pass);
		$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		//Sanitize & extract values
		$cid = strtoupper(s_string($_POST["courseid"]));
		
		//Check if course exists
		$sql = "SELECT * FROM course WHERE CONCAT(prefix, number) = :cid";
		
		$sth = $dbh->prepare($sql);
		$sth->bindParam(":cid", $cid);
		$sth->execute();
		
		$rownum = $sth->rowCount();
		
		if(!$rownum)
			echo "Course " . $cid . " does not exist in database.<br/>\n";
		else
		{
			$sql = "DELETE FROM course WHERE CONCAT(prefix, number) = :cid";
			
			$sth = $dbh->prepare($sql);
			$sth->bindParam(":cid", $cid);
			$sth->execute();
			
			//Check for existing prerequisites
			$sql = "SELECT * FROM course_prerequisites WHERE parent_course_id = :cid";
			
			$sth = $dbh->prepare($sql);
			$sth->bindParam(":cid", $cid);
			$sth->execute();
			
			$rownum = $sth->rowCount();
			
			//Delete existing prerequisites
			if($rownum)
			{
				$sql = "DELETE FROM course_prerequisites WHERE parent_course_id = :cid";
				
				$sth = $dbh->prepare($sql);
				$sth->bindParam(":cid", $cid);
				$sth->execute();
			}
			
			//Check for existing corequisites
			$sql = "SELECT * FROM course_corequisites WHERE parent_course_id = :cid";
			
			$sth = $dbh->prepare($sql);
			$sth->bindParam(":cid", $cid);
			$sth->execute();
			
			$rownum = $sth->rowCount();
			
			//Delete existing corequisites
			if($rownum)
			{
				$sql = "DELETE FROM course_corequisites WHERE parent_course_id = :cid";
				
				$sth = $dbh->prepare($sql);
				$sth->bindParam(":cid", $cid);
				$sth->execute();
			}
			
			echo "Course " . $cid . " has been deleted.";
		}
	}	
	else
	{
?>
			<h4>Delete Course</h4>
			<p>Please enter the course number and click <em>"Delete Course"</em> button.</p>
		
			<form class="form-horizontal" action="courses-delete.php" method="POST">
				<div class="control-group">
					<label class="control-label" for="CourseId">Course</label>
					<div class="controls">
						<input type="text" name="courseid" id="CourseId" class="input-small" placeholder="e.g. CS101" />
						<a href="Javascript:newPopup('courses-find.php');"><button type="button" class="btn btn-info">Find</button></a>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<a href="courses-delete.php" onclick="return confirm('Are you sure you want to delete this course?')"><button type="submit" name="submit" class="btn btn-primary">Delete Course</button></a>
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