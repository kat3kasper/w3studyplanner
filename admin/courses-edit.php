<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Edit Course</title>
		<?php require("../includes/styles.php"); ?>
		<?php require("../includes/config.php"); ?>
		<?php require("../includes/functions.php"); ?>
		<?php require("../includes/scripts.php"); ?>
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
				document.getElementById("courseId").value = course;
			}
			
			//Validates form inputs
			function validateForm1()
			{
				var x = document.getElementById("courseId").value;
					
				//Check for empty fields
				if(x == null || x == "")
				{
					alert("Please fill in the required field");
					return false;
				}
			}
			
			//Validates form inputs
			function validateForm()
			{
				var inputs = ["coursePrefix", "courseNumber", "credits", "courseName", "department"];
				
				for(var i = 0; i < inputs.length; i++)
				{
					var x = document.getElementById(inputs[i]).value;
					
					//Check for empty fields
					if(x == null || x == "")
					{
						alert("Please fill in all required fields");
						return false;
					}
					
					//Check numeric inputs
					else if(inputs[i] == "courseNumber" || inputs[i] == "credits")
						if(isNaN(x))
						{
							alert("Please fill in Course Number and Credits with numeric inputs only");
							return false;
						}
				}
				
				var ocChecked = false;
				var wcChecked = false;
				var y = document.getElementsByName("onCampus[]");
				var z = document.getElementsByName("webCampus[]");
				
				//Check if at least one checkbox is checked
				for(var j = 0; j < y.length; j++)
				{
					if(y[j].checked)
						ocChecked = true;
					if(z[j].checked)
						wcChecked = true;
				}
				if(!ocChecked && !wcChecked)
				{
					alert("Please tick at least one checkbox for Term Offered");
					return false;
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
				<li class="active"><a href="courses.php">Courses</a></li>
				<li><a href="cgroups.php">Course Groups</a></li>
				<li><a href="dprograms.php">Degree Programs</a></li>
			</ul>
			
			<ul class="nav nav-pills">
				<li><a href="courses-add.php">Add Course</a></li>
				<li class="active"><a href="courses-edit.php">Edit Course</a></li>
				<li><a href="courses-delete.php">Delete Course</a></li>
			</ul>
			
			<hr/>
			
<?php
	//If edited form is submitted
	if(isset($_POST["submitEdited"]))
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
		$pre = strtolower(s_string($_POST["coursePrefix"]));
		$num = s_int($_POST["courseNumber"]);
		$cred = s_int($_POST["credits"]);
		$name = s_string($_POST["courseName"]);
		$dept = strtolower(s_string($_POST["department"]));
		$prereq = strtolower(s_string($_POST["prerequisites"]));
		$coreq = strtolower(s_string($_POST["corequisites"]));
		
		if(isset($_POST["onCampus"]))
			$ocarr = $_POST["onCampus"];
		if(isset($_POST["webCampus"]))
			$wcarr = $_POST["webCampus"];
		
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
		{
?>
			
			<div class="alert alert-block">
				<button type="button" class="close" data-dismiss="alert"></button>
				<h4>Wait!</h4>
				<p>Course with that prefix & number already exist in database.</p>
			</div>
			
<?php
		}
		else
		{
			$valid_prereq = 1;
			$valid_coreq = 1;
			
			//Parsing course prerequisites
			if(!empty($prereq))
			{
				//Split ANDs
				$prereq_arr = array_map("trim", explode("\n", $prereq));
				
				//Check course exists
				foreach($prereq_arr as $value)
				{
					if(strpos($value, " or ") > 0)
					{
						$exploded = array_map("trim", explode(" or ", $value));
						foreach($exploded as $c)
							if(!course_exists($c))
								$valid_prereq = 0;
					}
					else if(!course_exists($value))
						$valid_prereq = 0;
				}
				
				if($valid_prereq)
					$formattedPrereq = wrap($prereq_arr, 1);
			}
			
			//Parsing course corequisites
			if(!empty($coreq) && $valid_prereq)
			{
				//Split ANDs
				$coreq_arr = array_map("trim", explode("\n", $coreq));
				
				//Check course exists
				foreach($coreq_arr as $value)
				{
					if(strpos($value, " or ") > 0)
					{
						$exploded = array_map("trim", explode(" or ", $value));
						foreach($exploded as $c)
							if(!course_exists($c))
								$valid_coreq = 0;
					}
					else if(!course_exists($value))
						$valid_coreq = 0;
				}
				
				if($valid_coreq)
					$formattedCoreq = wrap($coreq_arr, 1);
			}
			
			//Insert into db
			if($valid_prereq && $valid_coreq)
			{
				//Insert into prereq
				{
					//Check for existing prereq
					$sql = "SELECT * FROM course_prerequisites WHERE parent_course_id = :cid";
					
					$sth = $dbh->prepare($sql);
					$sth->bindParam(":cid", $cid);
					$sth->execute();
					
					$rownum = $sth->rowCount();
					
					//If course has existing prerequisites
					if($rownum)
					{
						//Prerequisites erased on form
						if(empty($prereq))
							$sql = "DELETE FROM course_prerequisites WHERE parent_course_id = :cid";
						else
							$sql = "UPDATE course_prerequisites SET parent_course_id = :ncid, prereq_course_id = :pcid WHERE parent_course_id = :cid";
					}
					//If course doesn't have prerequisites
					else
						$sql = "INSERT INTO course_prerequisites(parent_course_id, prereq_course_id) VALUES (:ncid, :pcid)";
					
					$sth = $dbh->prepare($sql);
					
					$ncid = $pre . $num;
					if($rownum)
					{
						$sth->bindParam(":cid", $cid);
						if(!empty($prereq))
						{
							$sth->bindParam(":pcid", $formattedPrereq);
							$sth->bindParam(":ncid", $ncid);
						}
					}
					else
					{
						$sth->bindParam(":ncid", $ncid);
						$sth->bindParam(":pcid", $formattedPrereq);
					}
				
					$sth->execute();
				}
				
				//Insert into coreq
				{				
					//Check for existing coreq
					$sql = "SELECT * FROM course_corequisites WHERE parent_course_id = :cid";
					
					$sth = $dbh->prepare($sql);
					$sth->bindParam(":cid", $cid);
					$sth->execute();
					
					$rownum = $sth->rowCount();
					
					//If course has existing corequisites
					if($rownum)
					{
						//Corequisites erased on form
						if(empty($coreq))
							$sql = "DELETE FROM course_corequisites WHERE parent_course_id = :cid";
						else
							$sql = "UPDATE course_corequisites SET parent_course_id = :ncid, coreq_course_id = :ccid WHERE parent_course_id = :cid";
					}
					//If course doesn't have corequisites
					else
						$sql = "INSERT INTO course_corequisites(parent_course_id, coreq_course_id) VALUES (:ncid, :ccid)";
					
					$sth = $dbh->prepare($sql);
					
					$ncid = $pre . $num;
					if($rownum)
					{
						$sth->bindParam(":cid", $cid);
						if(!empty($coreq))
						{
							$sth->bindParam(":pcid", $formattedCoreq);
							$sth->bindParam(":ncid", $ncid);
						}
					}
					else
					{
						$sth->bindParam(":ncid", $ncid);
						$sth->bindParam(":pcid", $formattedCoreq);
					}
				
					$sth->execute();
				}
			
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

				
				coursegroup_prologize();

?>
			
			<div class="alert alert-success alert-block">
				<button type="button" class="close" data-dismiss="alert"></button>
				<h4>Success!</h4>
				<p>Changes successfully saved.</p>
			</div>
			
<?php
			}
			//Invalid prereq/coreq
			else
			{
?>
			
			<div class="alert alert-block">
				<button type="button" class="close" data-dismiss="alert"></button>
				<h4>Wait!</h4>
				<p>Course cannot be edited. Make sure the prerequisites or corequisites have been added as course first</p>
			</div>
			
<?php
			}
		}
	}
	//If default edit form is submitted
	else if(isset($_POST["submit"]))
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
			
			<div class="alert alert-error alert-block">
				<button type="button" class="close" data-dismiss="alert"></button>
				<h4>Oh Snap!</h4>
				<p><?php echo $cid ?> does not exists in database...</p>
			</div>
			
<?php
		}
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
				$pcid = $row["prereq_course_id"];
				$prereq = implode("\n", unwrap($pcid));
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
				$ccid = $row["coreq_course_id"];
				$coreq = implode("\n", unwrap($ccid));
			}
			
?>

			<div id="wrapper" class="fluid">
				<div class="row-fluid">
					<form id="frmOptions" class="well form-inline span12" action="courses-edit.php" method="post" onsubmit="return validateForm()">
						<h4>Edit Course</h4>
						<div class="alert alert-info">
							<button type="button" class="close" data-dismiss="alert"></button>
							<p>Don't forget to hit <em>"Save Changes"</em> button once you're done.</p>
						</div>
						<br>
						<div class="row-fluid">   
							<div id="formLeft" class="span2">
								<div class="control-group">
									<label class="control-label" for="coursePrefix">Course Prefix</label>
									<div class="controls">
										<input type="text" name="coursePrefix" id="coursePrefix" class="input-medium" placeholder="e.g. cs" value="<?php if(isset($pre)) echo $pre; ?>" /> 
									</div>
								</div>
							</div>
							
							  <div id="formCenter" class="span2">
									<div class="control-group">
										<label class="control-label" for="courseNumber">Course Number</label>
										<div class="controls">
											<input type="text" name="courseNumber" id="courseNumber" class="input-medium" placeholder="e.g. 101" value="<?php if(isset($num)) echo $num; ?>" /> 
										</div>
									</div>
							  </div>
							  
								<div id="formRight" class="span2">
									<div class="control-group">
										<label class="control-label" for="credits">Credits</label>
										<div class="controls">
											<input type="text" name="credits" id="credits" class="input-medium" value="<?php if(isset($cred)) echo $cred; ?>" /> 
										</div>
									</div>
								</div>
						</div>
						<br>
						<div class="row-fluid">  
							<div id="formLeft" class="span3">
								<div class="control-group">
									<label class="control-label" for="courseName">Course Name</label>
									<div class="controls">
										<input type="text" name="courseName" id="courseName" value="<?php if(isset($name)) echo $name; ?>" /> 
									</div>
								</div>
							</div>
							
							<div id="formCenter" class="span7">
								<div class="control-group">
									<label class="control-label" for="department">Department</label>
									<div class="controls">
										<select name="department" id="department" class="span5">
											<option value="" <?php if(isset($dept) && $dept == "") echo "selected"; ?>>Select a department..</option>
											<option value="arts" <?php if(isset($dept) && $dept == "arts") echo "selected"; ?>>Arts and Letters</option>
											<option value="business" <?php if(isset($dept) && $dept == "business") echo "selected"; ?>>Business and Technology</option>				
											<option value="chemical" <?php if(isset($dept) && $dept == "chemical") echo "selected"; ?>>Chemical Engineering & Materials Science</option>
											<option value="chemistry" <?php if(isset($dept) && $dept == "chemistry") echo "selected"; ?>>Chemistry, Biology & Biomedical Engineering</option>
											<option value="civil" <?php if(isset($dept) && $dept == "civil") echo "selected"; ?>>Civil, Environmental & Ocean Engineering</option>
											<option value="computer" <?php if(isset($dept) && $dept == "computer") echo "selected"; ?>>Computer Science</option>
											<option value="electrical" <?php if(isset($dept) && $dept == "electrical") echo "selected"; ?>>Electrical & Computer Engineering</option>
											<option value="mathematical" <?php if(isset($dept) && $dept == "mathematical") echo "selected"; ?>>Mathematical Science</option>
											<option value="mechanical" <?php if(isset($dept) && $dept == "mechanical") echo "selected"; ?>>Mechanical Engineering</option>
											<option value="physics" <?php if(isset($dept) && $dept == "physics") echo "selected"; ?>>Physics & Engineering Physics</option>
											<option value="quantitative" <?php if(isset($dept) && $dept == "quantitative") echo "selected"; ?>>Quantitative Finance</option>
											<option value="systems" <?php if(isset($dept) && $dept == "systems") echo "selected"; ?>>Systems & Enterprises</option>
										</select>
									</div>
								</div>
							</div>
							
						</div>
						<br>
						<div class="row-fluid">  
							<div id="formLeft" class="span3">
								<div class="control-group">
									<label class="control-label" for="prerequisites">Prerequisites <a onClick="alert('Insert OR combinations on the same line\nInsert AND combinations on different lines\n\ne.g. (cs105 or cs135) and cs284 will be:\ncs105 or cs135\ncs284');">(How to?)</a></label>
									<div class="controls">
										<textarea name="prerequisites" id="prerequisites" placeholder="e.g. cs115 or cs180                   cs284"><?php if(isset($prereq)) echo $prereq; ?></textarea>
									</div>
								</div>
							</div>
							
							<div id="formCenter" class="span7">
								<div class="control-group">
									<label class="control-label" for="corequisites">Corequisites <a onClick="alert('Insert OR combinations on the same line\nInsert AND combinations on different lines\n\ne.g. (cs105 or cs135) and cs284 will be:\ncs105 or cs135\ncs284');">(How to?)</a></label>
									<div class="controls">
										<textarea name="corequisites" id="corequisites" class="span5" placeholder="e.g. cs115 or cs180                   cs284"><?php if(isset($coreq)) echo $coreq; ?></textarea> 
									</div>
								</div>
							</div>
						</div>
						
						<br>
						<div class="row-fluid">  
							<div id="formCenter" class="span12">
								<div class="control-group">
									<label class="control-label">Term Offered*</label>
									<div class="controls">
										<div class="control-group">
											<label class="control-label"><br>On Campus</label>
											<div class="controls">
												<label class="checkbox inline">
													Fall<input type="checkbox" name="onCampus[]" id="fall" value="fall" <?php if(isset($oc) && strpos($oc, "fall") !== FALSE) echo "checked"; ?> />
												</label>
												<label class="checkbox inline">
													Spring<input type="checkbox" name="onCampus[]" id="spring" value="spring" <?php if(isset($oc) && strpos($oc, "spring") !== FALSE) echo "checked"; ?> />
												</label>
												<label class="checkbox inline">
													Summer 1<input type="checkbox" name="onCampus[]" id="summer1" value="summer1" <?php if(isset($oc) && strpos($oc, "summer1") !== FALSE) echo "checked"; ?> />
												</label>
												<label class="checkbox inline">
													Summer 2<input type="checkbox" name="onCampus[]" id="summer2" value="summer2" <?php if(isset($oc) && strpos($oc, "summer2") !== FALSE) echo "checked"; ?> />
												</label>
											</div>
										</div>
									</div>
									<div class="controls">
										<div class="control-group">
											<label class="control-label"><br>Web Campus</label>
											<div class="controls">
												<label class="checkbox inline">
													Fall<input type="checkbox" name="webCampus[]" id="fall" value="fall" <?php if(isset($wc) && strpos($wc, "fall") !== FALSE) echo "checked"; ?> />
												</label>
												<label class="checkbox inline">
													Spring<input type="checkbox" name="webCampus[]" id="spring" value="spring" <?php if(isset($wc) && strpos($wc, "spring") !== FALSE) echo "checked"; ?> />
												</label>
												<label class="checkbox inline">
													Summer 1<input type="checkbox" name="webCampus[]" id="summer1" value="summer1" <?php if(isset($wc) && strpos($wc, "summer1") !== FALSE) echo "checked"; ?> />
												</label>
												<label class="checkbox inline">
													Summer 2<input type="checkbox" name="webCampus[]" id="summer2" value="summer2" <?php if(isset($wc) && strpos($wc, "summer2") !== FALSE) echo "checked"; ?> />
												</label>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						
						<div class="form-actions">
							<input type="hidden" name="courseId" value="<?php echo $cid; ?>">
							<button type="submit" name="submitEdited" class="btn btn-primary">Save Changes</button>
						</div>
					
					</form>
				</div>
			</div>
			
<?php
		}
	}
	//Default page
	else
	{
?>
			<div class="well">
				<h4>Edit Course</h4>
				<div class="alert alert-info">
					<button type="button" class="close" data-dismiss="alert"></button>
					<p>Please enter the course prefix & number and click <em>"Edit Course"</em> button.</p>
				</div>
				
				<form class="form-horizontal" action="courses-edit.php" method="post" onsubmit="return validateForm1()">
					<div class="control-group">
						<label class="control-label" for="courseId">Course</label>
						<div class="controls">
							<input type="text" name="courseId" id="courseId" class="input-small" placeholder="e.g. cs101" />
							<button type="button" class="btn btn-info" onclick="Javascript:newPopup('courses-find.php');" title="Find Course"><i class="icon-search"></i></button>
						</div>
					</div>
				
					
					<div class="form-actions">
					  <button type="submit" name="submit" class="btn btn-primary">Edit Course</button>
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
	</body>
</html>
