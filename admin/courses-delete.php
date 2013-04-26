<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Delete Course</title>
		<?php require("../includes/styles.php"); ?>
		<?php require("../includes/config.php"); ?>
		<?php require("../includes/functions.php"); ?>
    <?php require("../includes/db2prolog.php"); ?>
		
		<script type="text/javascript">
			//Popup window code
			function newPopup(url)
			{
				popupWindow = window.open(url,'popUpWindow', 'height=500, width=600, left=10, top=10, resizable=yes,menubar=no, location=no, directories=no, status=yes');
			}
			
			//Get value from child window
			function GetValueFromChild(course)
			{
				document.getElementById('courseId').value = course;
			}
			
			//Validates form inputs
			function validateForm()
			{
				var x = document.getElementById("courseId").value;
					
				//Check for empty fields
				if(x == null || x == "")
				{
					alert("Please fill in the required field");
					return false;
				}
				
				//Ask for user confirmation
				return confirm("Are you sure you want to delete this course?");
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
				<li class="active"><a href="courses.php">Courses</a></li>
				<li><a href="cgroups.php">Course Groups</a></li>
				<li><a href="dprograms.php">Degree Programs</a></li>
			</ul>
			
			<ul class="nav nav-pills">
				<li><a href="courses-add.php">Add Course</a></li>
				<li><a href="courses-edit.php">Edit Course</a></li>
				<li class="active"><a href="courses-delete.php">Delete Course</a></li>
			</ul>
			
			<hr/>
			
<?php
	//If delete form is submitted & course is not empty
	if(isset($_POST["submit"]) && !empty($_POST["courseId"]))
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
		$cid = strtolower(s_string($_POST["courseId"]));
		
		//Check if course exists
		$sql = "SELECT * FROM course WHERE CONCAT(prefix, number) = :cid";
		
		$sth = $dbh->prepare($sql);
		$sth->bindParam(":cid", $cid);
		$sth->execute();
		
		$rownum = $sth->rowCount();
		
		if(!$rownum)
		{
?>
			
			<div class="alert alert-block">
				<button type="button" class="close" data-dismiss="alert"></button>
				<h4>Wait!</h4>
				<p><?php echo "Course " . $cid . " does not exist in database.<br/>\n"; ?></p>
			</div>
			
<?php
		}
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

			
			coursegroup_prologize();

			
			echo "Course " . $cid . " has been deleted.";
			?>
			<div class="alert alert-success alert-block">
				 <button type="button" class="close" data-dismiss="alert"></button>
				 <h4>There She Goes...</h4>
				<p><?php echo "Course " . $cid . " has been deleted."; ?></p>
			</div>
<?php			
		}
	}	
	else
	{
?>
			<div class="well">
				<h4>Delete Course</h4>
				<div class="alert alert-info">
					<button type="button" class="close" data-dismiss="alert"></button>
					<p>Please enter the course number and click <em>"Delete Course"</em> button.</p>
				</div>
			
				<form class="form-horizontal" action="courses-delete.php" method="post" onsubmit="return validateForm()">
					<div class="control-group">
						<label class="control-label" for="courseId">Course</label>
						<div class="controls">
							<input type="text" name="courseId" id="courseId" class="input-small" placeholder="e.g. CS101" />
							<button type="button" class="btn btn-info" onclick="Javascript:newPopup('courses-find.php');" title="Find Course"><i class="icon-search"></i></button>
						</div>
					</div>

					<div class="form-actions">
					  <button type="submit" name="submit" class="btn btn-primary">Delete Course</button>
					</div>
				</form>
			</div>
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
