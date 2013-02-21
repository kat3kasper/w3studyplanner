<?php require("../includes/config.php"); ?>
<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Edit Course</title>
		<?php require("../includes/styles.php"); ?>
		
		<script type="text/javascript">
			//Popup window code
			function newPopup(url)
			{
				popupWindow = window.open(url,'popUpWindow', 'height=500, width=600, left=10, top=10, resizable=yes,menubar=no, location=no, directories=no, status=yes');
			}
			
			//Get value from child window
			function GetValueFromChild(course)
			{
				document.getElementById('Course').value = course;
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
			</ul>
			
			<ul class="nav nav-pills">
				<li><a href="courses-add.php">Add Course</a></li>
				<li class="active"><a href="courses-edit.php">Edit Course</a></li>
				<li><a href="courses-delete.php">Delete Course</a></li>
			</ul>
			
			<hr/>
			
<?php
	//If edited details form is submitted
	if(isset($_POST["submit"]) && (!empty($_POST["courseprefix"]) && !empty($_POST["coursenumber"])))
	{
		//Setup database
		$host = DB_HOST;
		$dbname = DB_NAME;
		$user = DB_USER;
		$pass = DB_PASS;
		
		$dbh = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $user, $pass);
		
		//$db = new database();
		//$db->setup("w3_studyplanner", "QcRo2mEC", "db0.stevens.edu", "w3_studyplanner");
		
		//Sanitize & extract values
		$course = strtolower(addslashes(strip_tags($_POST["course"])));
		$pre = strtolower(addslashes(strip_tags($_POST["courseprefix"])));
		$num = strtolower(addslashes(strip_tags($_POST["coursenumber"])));
		$cred = strtolower(addslashes(strip_tags($_POST["credits"])));
		$name = addslashes(strip_tags($_POST["coursename"]));
		$dept = strtolower(addslashes(strip_tags($_POST["department"])));
		if(isset($_POST["oncampus"]))
			$ocarr = $_POST["oncampus"];
		if(isset($_POST["webcampus"]))
			$wcarr = $_POST["webcampus"];
		
		$oc = "";
		$wc = "";
		
		if(!empty($ocarr))
			foreach($ocarr as $sem)
			{
				if(!empty($oc))
					$oc .= ",";
				
				$oc .= strtolower(addslashes(strip_tags($sem)));
			}
		
		if(!empty($wcarr))
			foreach($wcarr as $sem)
			{
				if(!empty($wc))
					$wc .= ",";
				
				$wc .= strtolower(addslashes(strip_tags($sem)));
			}
		
		//Insert to database
		$sql = "UPDATE course SET prefix = :pre, number = :num, no_of_credits = :cred, course_name = :name, department = :dept, on_campus_semesters = :oc, web_campus_semesters = :wc WHERE CONCAT(prefix, number) = :course";
		
		$sth = $dbh->prepare($sql);
		
		$sth->bindParam(":pre", $pre);
		$sth->bindParam(":num", $num);
		$sth->bindParam(":cred", $cred);
		$sth->bindParam(":name", $name);
		$sth->bindParam(":dept", $dept);
		$sth->bindParam(":oc", $oc);
		$sth->bindParam(":wc", $wc);
		$sth->bindParam(":course", $course);
		
		$sth->execute();
		
		//$sql = "UPDATE course SET prefix = '" . $pre . "', number = " . $num . ", no_of_credits = " . $cred . ", course_name = '" . $name . "', department = '" . $dept . "', on_campus_semesters = '" . $oc . "', web_campus_semesters = '" . $wc . "' WHERE CONCAT(prefix, number) = '" . $course . "'";
		//$db->send_sql($sql);
		
		echo "Course successfully edited.<br/>\n";
	}
	//If edit form is submitted
	else if(isset($_POST["submit"]) && !empty($_POST["course"]))
	{
		//Setup database
		$host = "db0.stevens.edu";
		$dbname = "w3_studyplanner";
		$user = "w3_studyplanner";
		$pass = "QcRo2mEC";
		
		$dbh = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $user, $pass);
		
		//$db = new database();
		//$db->setup("w3_studyplanner", "QcRo2mEC", "db0.stevens.edu", "w3_studyplanner");
		
		//Sanitize & extract values
		$course = strtolower(addslashes(strip_tags($_POST["course"])));
		
		//Check with database
		$sql = "SELECT * FROM course WHERE CONCAT(prefix, number) = :course";
		
		$sth = $dbh->prepare($sql);
		
		$sth->bindParam(":course", $course);
		
		$sth->execute();
		$rownum = $sth->rowCount();
		
		//$sql = "SELECT * FROM course WHERE CONCAT(prefix, number) = '" . $course . "'";
		//$res = $db->send_sql($sql);
		
		if(!$rownum)
			echo "Course doesn't exist in database.<br/>\n";
		else
		{
			//$row = $db->next_row();
			
			$row = $sth->fetch(PDO::FETCH_ASSOC);
			
			$pre = $row["prefix"]; //$row[0];
			$num = $row["number"]; //$row[1];
			$cred = $row["no_of_credits"]; //$row[2];
			$name = $row["course_name"]; //$row[3];
			$dept = $row["department"]; //$row[4];
			$oc = $row["on_campus_semesters"]; //$row[5];
			$wc = $row["web_campus_semesters"]; //$row[6];
			
			echo "Replace the details below with new values:<br/><br/>\n";
?>
			
			<form class="form-horizontal" action="courses-edit.php" method="POST">
				<div class="control-group">
					<label class="control-label" for="CoursePrefix">Course Prefix</label>
					<div class="controls">
						<input type="text" name="courseprefix" id="CoursePrefix" class="input-small" placeholder="e.g. CS" value="<?php if(isset($pre)) echo $pre; ?>" /> 
					</div>
				</div>				
				<div class="control-group">
					<label class="control-label" for="CourseNumber">Course Number</label>
					<div class="controls">
						<input type="text" name="coursenumber" id="CourseNumber" class="input-small" placeholder="e.g. 101" value="<?php if(isset($num)) echo $num; ?>" /> 
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="Credits">Credits</label>
					<div class="controls">
						<input type="text" name="credits" id="Credits" class="input-small" value="<?php if(isset($cred)) echo $cred; ?>" /> 
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="CourseName">Course Name</label>
					<div class="controls">
						<input type="text" name="coursename" id="CourseName" value="<?php if(isset($name)) echo $name; ?>" /> 
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="Department">Department</label>
					<div class="controls">
						<select name="department">
							<option value="" <?php if(isset($dept) && $dept == "") echo "selected"; ?>>Select a department..</option>
							<option value="chemical" <?php if(isset($dept) && $dept == "chemical") echo "selected"; ?>>Chemical Engineering & Materials Science</option>
							<option value="chemistry" <?php if(isset($dept) && $dept == "chemistry") echo "selected"; ?>>Chemistry, Biology & Biomedical Engineering</option>
							<option value="civil" <?php if(isset($dept) && $dept == "civil") echo "selected"; ?>>Civil, Environmental & Ocean Engineering</option>
							<option value="computer" <?php if(isset($dept) && $dept == "computer") echo "selected"; ?>>Computer Science</option>
							<option value="electrical" <?php if(isset($dept) && $dept == "electrical") echo "selected"; ?>>Electrical & Computer Engineering</option>
							<option value="mathematical" <?php if(isset($dept) && $dept == "mathematical") echo "selected"; ?>>Mathematical Science</option>
							<option value="mechanical" <?php if(isset($dept) && $dept == "mechanical") echo "selected"; ?>>Mechanical Engineering</option>
							<option value="physics" <?php if(isset($dept) && $dept == "physics") echo "selected"; ?>>Physics & Engineering Physics</option>
							<option value="systems" <?php if(isset($dept) && $dept == "systems") echo "selected"; ?>>Systems & Enterprises</option>
							<option value="business" <?php if(isset($dept) && $dept == "business") echo "selected"; ?>>Business and Technology</option>
							<option value="quantitative" <?php if(isset($dept) && $dept == "quantitative") echo "selected"; ?>>Quantitative Finance</option>
							<option value="arts" <?php if(isset($dept) && $dept == "arts") echo "selected"; ?>>Arts and Letters</option>
						</select>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">Prerequisites</label>
					<div class="controls">
						<a href="#"><button type="button" class="btn">Select Prerequisites</button></a>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">Corequisites</label>
					<div class="controls">
						<a href="#"><button type="button" class="btn">Select Corequisites</button></a>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">Term Offered</label>
					<div class="controls">
						<div class="control-group">
							<label class="control-label">On Campus</label>
							<div class="controls">
								<label class="checkbox inline">
									Fall<input type="checkbox" name="oncampus[]" id="Fall" value="fall" <?php if(isset($oc) && strpos($oc, "fall") !== FALSE) echo "checked"; ?> />
								</label>
								<label class="checkbox inline">
									Spring<input type="checkbox" name="oncampus[]" id="Spring" value="spring" <?php if(isset($oc) && strpos($oc, "spring") !== FALSE) echo "checked"; ?> />
								</label>
								<label class="checkbox inline">
									Summer 1<input type="checkbox" name="oncampus[]" id="Summer1" value="summer1" <?php if(isset($oc) && strpos($oc, "summer1") !== FALSE) echo "checked"; ?> />
								</label>
								<label class="checkbox inline">
									Summer 2<input type="checkbox" name="oncampus[]" id="Summer2" value="summer2" <?php if(isset($oc) && strpos($oc, "summer2") !== FALSE) echo "checked"; ?> />
								</label>
							</div>
						</div>
					</div>
					<div class="controls">
						<div class="control-group">
							<label class="control-label">Web Campus</label>
							<div class="controls">
								<label class="checkbox inline">
									Fall<input type="checkbox" name="webcampus[]" id="Fall" value="fall" <?php if(isset($wc) && strpos($wc, "fall") !== FALSE) echo "checked"; ?> />
								</label>
								<label class="checkbox inline">
									Spring<input type="checkbox" name="webcampus[]" id="Spring" value="spring" <?php if(isset($wc) && strpos($wc, "spring") !== FALSE) echo "checked"; ?> />
								</label>
								<label class="checkbox inline">
									Summer 1<input type="checkbox" name="webcampus[]" id="Summer1" value="summer1" <?php if(isset($wc) && strpos($wc, "summer1") !== FALSE) echo "checked"; ?> />
								</label>
								<label class="checkbox inline">
									Summer 2<input type="checkbox" name="webcampus[]" id="Summer2" value="summer2" <?php if(isset($wc) && strpos($wc, "summer2") !== FALSE) echo "checked"; ?> />
								</label>
							</div>
						</div>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<input type="hidden" name="course" value="<?php echo $course; ?>">
						<button type="submit" name="submit" class="btn btn-primary">Edit Course</button>
					</div>
				</div>
			</form>
			
<?php
		}
	}
	else
	{
?>
			
			<h4>Edit Course</h4>
			<p>Please enter the course prefix & number and click <em>"Edit Course"</em> button.</p>
			
			<form class="form-horizontal" action="courses-edit.php" method="POST">
				<div class="control-group">
					<label class="control-label" for="Course">Course</label>
					<div class="controls">
						<input type="text" name="course" id="Course" class="input-small" placeholder="e.g. CS101" />
						<a href="Javascript:newPopup('courses-find.php');"><button type="button" class="btn btn-info">Find</button></a>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<button type="submit" name="submit" class="btn btn-primary">Edit Course</button>
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