<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Add Course</title>
		<?php require("../includes/styles.php"); ?>
		<?php require("../includes/config.php"); ?>
		<?php require("../includes/functions.php"); ?>
		<?php require("../includes/scripts.php"); ?>
    <?php require("../includes/db2prolog.php"); ?>
		
		<script type="text/javascript">
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
					
					//Check if prefix has numbers
					else if(inputs[i] == "coursePrefix")
						if(x.match(/\d+/g) != null)
						{
							alert("Prefix cannot contain numbers");
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
				<li class="active"><a href="courses-add.php">Add Course</a></li>
				<li><a href="courses-edit.php">Edit Course</a></li>
				<li><a href="courses-delete.php">Delete Course</a></li>
			</ul>
			
			<hr/>
			
<?php
	//If add form is submitted with required fields
	if(isset($_POST["submit"]))
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
		
		//Check for duplicates
		$sql = "SELECT * FROM course WHERE CONCAT(prefix, number) = :cid";
		
		$sth = $dbh->prepare($sql);
		
		$cid = $pre . $num;
		$sth->bindParam(":cid", $cid);
		$sth->execute();
		
		$rownum = $sth->rowCount();
		
		if($rownum)
		{
?>
			<div class="alert alert-error alert-block">
				<button type="button" class="close" data-dismiss="alert"></button>
				<h4>Oh Snap!</h4>
				<p><?php echo $cid ?> already exists in database...</p>
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
				if(!empty($prereq))
				{
					$sql = "INSERT INTO course_prerequisites(parent_course_id, prereq_course_id) VALUES (:cid, :pcid)";
					
					$sth = $dbh->prepare($sql);
					$sth->bindParam(":cid", $cid);
					$sth->bindParam(":pcid", $formattedPrereq);
				
					$sth->execute();
				}
				
				//Insert into coreq
				if(!empty($coreq))
				{				
					$sql = "INSERT INTO course_corequisites(parent_course_id, coreq_course_id) VALUES (:cid, :ccid)";
					
					$sth = $dbh->prepare($sql);
					$sth->bindParam(":cid", $cid);
					$sth->bindParam(":ccid", $formattedCoreq);
				
					$sth->execute();
				}
				
				//Insert into course
				$sql = "INSERT INTO course(prefix, number, no_of_credits, course_name, department, on_campus_semesters, web_campus_semesters) VALUES (:pre, :num, :cred, :name, :dept, :oc, :wc)";
				
				$sth = $dbh->prepare($sql);
				
				$sth->bindParam(":pre", $pre);
				$sth->bindParam(":num", $num);
				$sth->bindParam(":cred", $cred);
				$sth->bindParam(":name", $name);
				$sth->bindParam(":dept", $dept);	
				$sth->bindParam(":oc", $oc);
				$sth->bindParam(":wc", $wc);
					
				$sth->execute();
				
				coursegroup_prologize();

?>
			
			<div class="alert alert-success alert-block">
				<button type="button" class="close" data-dismiss="alert"></button>
				<h4>Success!</h4>
				<p>Course successfully added.</p>
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
				<p>Course cannot be added. Make sure the prerequisites or corequisites have been added as course first</p>
			</div>
			
<?php
			}
		}
	}
	//Default page
	else
	{
?>
			
			<div id="wrapper" class="fluid">
				<div class="row-fluid">
					<form id="frmOptions" class="well form-inline span12" action="courses-add.php" method="post" onsubmit="return validateForm()">
						<h4>Add Course</h4>
						<div class="alert alert-info">
							<button type="button" class="close" data-dismiss="alert"></button>
							<p>Please fill in the required fields (*) and click on <em>"Add Course"</em> button.</p>
						</div>
						<br>
						<div class="row-fluid">   
							<div id="formLeft" class="span2">
								<div class="control-group">
								<label class="control-label" for="coursePrefix">Course Prefix*</label>
								<div class="controls">
									<input type="text" name="coursePrefix" id="coursePrefix" class="input-medium" placeholder="e.g. cs" /> 
								</div>
								</div>
							</div>
							
							  <div id="formCenter" class="span2">
								<div class="control-group">
									<label class="control-label" for="courseNumber">Course Number*</label>
									<div class="controls">
										<input type="text" name="courseNumber" id="courseNumber" class="input-medium" placeholder="e.g. 101" /> 
									</div>
								</div>
							  </div>
							  
								<div id="formRight" class="span2">
									<div class="control-group">
										<label class="control-label" for="credits">Credits*</label>
										<div class="controls">
											<input type="text" name="credits" id="credits" class="input-medium" /> 
										</div>
									</div>
								</div>
						</div>
						<br>
						<div class="row-fluid">  
							<div id="formLeft" class="span3">
								<div class="control-group">
									<label class="control-label" for="courseName">Course Name*</label>
									<div class="controls">
										<input type="text" name="courseName" id="courseName"> 
									</div>
								</div>
							</div>
							
							<div id="formCenter" class="span7">
								<div class="control-group">
									<label class="control-label" for="department">Department*</label>
									<div class="controls">
										<select name="department" id="department" class="span5">
											<option value="">Select a department..</option>
											<option value="arts">Arts and Letters</option>
											<option value="business">Business and Technology</option>
											<option value="chemical">Chemical Engineering & Materials Science</option>
											<option value="chemistry">Chemistry, Biology & Biomedical Engineering</option>
											<option value="civil">Civil, Environmental & Ocean Engineering</option>
											<option value="computer">Computer Science</option>
											<option value="electrical">Electrical & Computer Engineering</option>
											<option value="mathematical">Mathematical Science</option>
											<option value="mechanical">Mechanical Engineering</option>
											<option value="physics">Physics & Engineering Physics</option>
											<option value="quantitative">Quantitative Finance</option>
											<option value="systems">Systems & Enterprises</option>
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
										<textarea name="prerequisites" id="prerequisites" placeholder="e.g. cs115 or cs180                    cs284"></textarea>
									</div>
								</div>
							</div>
							
							<div id="formCenter" class="span7">
								<div class="control-group">
									<label class="control-label" for="corequisites">Corequisites <a onClick="alert('Insert OR combinations on the same line\nInsert AND combinations on different lines\n\ne.g. (cs105 or cs135) and cs284 will be:\ncs105 or cs135\ncs284');">(How to?)</a></label>
									<div class="controls">
										<textarea name="corequisites" id="corequisites" class="span5" placeholder="e.g. cs115 or cs180                    cs284"></textarea> 
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
													Fall<input type="checkbox" name="onCampus[]" id="fall" value="fall" />
												</label>
												<label class="checkbox inline">
													Spring<input type="checkbox" name="onCampus[]" id="spring" value="spring" />
												</label>
												<label class="checkbox inline">
													Summer 1<input type="checkbox" name="onCampus[]" id="summer1" value="summer1" />
												</label>
												<label class="checkbox inline">
													Summer 2<input type="checkbox" name="onCampus[]" id="summer2" value="summer2" />
												</label>
											</div>
										</div>
									</div>
									<div class="controls">
										<div class="control-group">
											<label class="control-label"><br>Web Campus</label>
											<div class="controls">
												<label class="checkbox inline">
													Fall<input type="checkbox" name="webCampus[]" id="fall" value="fall" />
												</label>
												<label class="checkbox inline">
													Spring<input type="checkbox" name="webCampus[]" id="spring" value="spring" />
												</label>
												<label class="checkbox inline">
													Summer 1<input type="checkbox" name="webCampus[]" id="summer1" value="summer1" />
												</label>
												<label class="checkbox inline">
													Summer 2<input type="checkbox" name="webCampus[]" id="summer2" value="summer2" />
												</label>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						
						<div class="form-actions">
						 <button type="submit" name="submit" class="btn btn-primary">Add Course</button>
						</div>
					
					</form>
				</div>
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
