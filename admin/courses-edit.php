<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Edit Course</title>
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
				<li><a href="dprograms.php">Degree Programs</a></li>
				<li class="active"><a href="courses.php">Courses</a></li>
				<li><a href="cgroups.php">Course Groups</a></li>
				<li><a href="requirements.php">Requirements</a></li>
			</ul>
			
			<ul class="nav nav-pills">
				<li><a href="courses-add.php">Add Course</a></li>
				<li class="active"><a href="courses-edit.php">Edit Course</a></li>
				<li><a href="courses-delete.php">Delete Course</a></li>
			</ul>
			
			<hr/>
			
<?php
	//If newly edited details form is submitted
	if(isset($_POST["submit"]) && (!empty($_POST["courseprefix"]) && !empty($_POST["coursenumber"])))
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
		$cid = strtoupper(s_string($_POST["courseid"]));
		$pre = strtoupper(s_string($_POST["courseprefix"]));
		$num = s_int($_POST["coursenumber"]);
		$cred = s_int($_POST["credits"]);
		$name = s_string($_POST["coursename"]);
		$dept = strtolower(s_string($_POST["department"]));
		$prereq = strtoupper(s_string($_POST["prerequisites"]));
		$coreq = strtoupper(s_string($_POST["corequisites"]));
		
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
				
				$oc .= strtolower(s_string($sem));
			}
		
		if(!empty($wcarr))
			foreach($wcarr as $sem)
			{
				if(!empty($wc))
					$wc .= ",";
				
				$wc .= strtolower(s_string($sem));
			}
		
		//Check for conflicting course
		$sql = "SELECT * FROM course WHERE CONCAT(prefix, number) = :ncid AND CONCAT(prefix, number) != :cid";
		
		$sth = $dbh->prepare($sql);
		
		$ncid = $pre . $num;
		$sth->bindParam(":ncid", $ncid);
		$sth->bindParam(":cid", $cid);
		
		$sth->execute();
		$rownum = $sth->rowCount();
		
		if($rownum)
			echo "Course with that prefix & number already exist in database.<br/>\n";
		else
		{
			//Update course
			$sql = "UPDATE course SET prefix = :pre, number = :num, no_of_credits = :cred, course_name = :name, department = :dept, on_campus_semesters = :oc, web_campus_semesters = :wc WHERE CONCAT(prefix, number) = :cid";
			
			$sth = $dbh->prepare($sql);
			
			$sth->bindParam(":pre", $pre);
			$sth->bindParam(":num", $num);
			$sth->bindParam(":cred", $cred);
			$sth->bindParam(":name", $name);
			$sth->bindParam(":dept", $dept);
			$sth->bindParam(":oc", $oc);
			$sth->bindParam(":wc", $wc);
			$sth->bindParam(":cid", $cid);
			
			$sth->execute();
			
			//Insert to course prerequisites
			if(!empty($prereq))
			{
				//Check for duplicates
				$sql = "SELECT * FROM course_prerequisites WHERE parent_course_id = :cid";
				
				$sth = $dbh->prepare($sql);
				$sth->bindParam(":cid", $cid);
				$sth->execute();
				
				$rownum = $sth->rowCount();
				
				//If course has existing prerequisites
				if($rownum)
					$sql = "UPDATE course_prerequisites SET parent_course_id = :ncid, and_course_id = :acid, or_course_id = :ocid WHERE parent_course_id = :cid";
				//If course doesn't have prerequisites
				else
					$sql = "INSERT INTO course_prerequisites(parent_course_id, and_course_id, or_course_id) VALUES (:ncid, :acid, :ocid)";
				
				$sth = $dbh->prepare($sql);
				
				$and_list = "";
				$or_list = "";
				
				//Parse AND
				$prereq_arr = explode("\n", $prereq);
				foreach($prereq_arr as $value)
				{
					//Parse OR
					if(strpos($value, " OR ") > 0)
					{
						//Delimiter for each group of OR
						if($or_list !== "")
							$or_list .= ",";
						
						$or_list .= implode("|", explode(" OR ", $value));
					}
					else
					{
						if($and_list !== "")
							$and_list .= ",";
						
						$and_list .= $value;
					}
				}
				
				$ncid = $pre . $num;
				$sth->bindParam(":ncid", $ncid);
				$sth->bindParam(":acid", $and_list);
				$sth->bindParam(":ocid", $or_list);
				if($rownum)
					$sth->bindParam(":cid", $cid);
			
				$sth->execute();
			}
			
			//Insert to course corequisites
			if(!empty($coreq))
			{
				//Check for duplicates
				$sql = "SELECT * FROM course_corequisites WHERE parent_course_id = :cid";
				
				$sth = $dbh->prepare($sql);
				$sth->bindParam(":cid", $cid);
				$sth->execute();
				
				$rownum = $sth->rowCount();
				
				//If course has existing corequisites
				if($rownum)
					$sql = "UPDATE course_corequisites SET parent_course_id = :ncid, and_course_id = :acid, or_course_id = :ocid WHERE parent_course_id = :cid";
				//If course doesn't have corequisites
				else
					$sql = "INSERT INTO course_corequisites(parent_course_id, and_course_id, or_course_id) VALUES (:ncid, :acid, :ocid)";
				
				$sth = $dbh->prepare($sql);
				
				$and_list = "";
				$or_list = "";
				
				//Parse AND
				$coreq_arr = explode("\n", $coreq);
				foreach($coreq_arr as $value)
				{
					//Parse OR
					if(strpos($value, " OR ") > 0)
					{
						//Delimiter for each group of OR
						if($or_list !== "")
							$or_list .= ",";
						
						$or_list .= implode("|", explode(" OR ", $value));
					}
					else
					{
						if($and_list !== "")
							$and_list .= ",";
						
						$and_list .= $value;
					}
				}
				
				$ncid = $pre . $num;
				$sth->bindParam(":ncid", $ncid);
				$sth->bindParam(":acid", $and_list);
				$sth->bindParam(":ocid", $or_list);
				if($rownum)
					$sth->bindParam(":cid", $cid);
			
				$sth->execute();
			}
		}
		
		echo "Course successfully edited.<br/>\n";
	}
	//If edit form is submitted & course is not empty
	else if(isset($_POST["submit"]) && !empty($_POST["courseid"]))
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
			echo "Course doesn't exist in database.<br/>\n";
		else
		{
			//Get course details
			$row = $sth->fetch(PDO::FETCH_ASSOC);
			
			$pre = $row["prefix"];
			$num = $row["number"];
			$cred = $row["no_of_credits"];
			$name = $row["course_name"];
			$dept = $row["department"];
			$oc = $row["on_campus_semesters"];
			$wc = $row["web_campus_semesters"];
			
			//Get prerequisites details
			$sql = "SELECT * FROM course_prerequisites WHERE parent_course_id = :cid";
			
			$sth = $dbh->prepare($sql);
			$sth->bindParam(":cid", $cid);
			$sth->execute();
			
			$rownum = $sth->rowCount();
			
			if($rownum)
			{
				$row = $sth->fetch(PDO::FETCH_ASSOC);
				
				$acid = $row["and_course_id"];
				$ocid = $row["or_course_id"];
				
				$prereq = implode("\n", explode(",", $acid));
				
				//Handling OR
				$or_groups = explode(",", $ocid);
				foreach($or_groups as $value)
					$prereq .= implode(" OR ", explode("|", $value));
			}
			
			//Get corequisites details
			$sql = "SELECT * FROM course_corequisites WHERE parent_course_id = :cid";
			
			$sth = $dbh->prepare($sql);
			$sth->bindParam(":cid", $cid);
			$sth->execute();
			
			$rownum = $sth->rowCount();
			
			if($rownum)
			{
				$row = $sth->fetch(PDO::FETCH_ASSOC);
				
				$acid = $row["and_course_id"];
				$ocid = $row["or_course_id"];
				
				$coreq = implode("\n", explode(",", $acid));
				
				//Handling OR
				$or_groups = explode(",", $ocid);
				foreach($or_groups as $value)
					$coreq .= implode(" OR ", explode("|", $value));
			}
			
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
						<select name="department" id="Department">
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
						<textarea name="prerequisites" id="Prerequisites"><?php if(isset($prereq)) echo $prereq; ?></textarea>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">Corequisites</label>
					<div class="controls">
						<textarea name="corequisites" id="Corequisites"><?php if(isset($coreq)) echo $coreq; ?></textarea> 
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
						<input type="hidden" name="courseid" value="<?php echo $cid; ?>">
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
					<label class="control-label" for="CourseId">Course</label>
					<div class="controls">
						<input type="text" name="courseid" id="CourseId" class="input-small" placeholder="e.g. CS101" />
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