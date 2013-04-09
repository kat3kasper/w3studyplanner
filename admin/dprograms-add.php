<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Add Degree Program</title>
		<?php require("../includes/styles.php"); ?>
		<?php require("../includes/config.php"); ?>
		<?php require("../includes/functions.php"); ?>
		<?php require("../includes/scripts.php"); ?>
		
		<script type="text/javascript">
		
			//Validates form inputs
			function validateForm()
			{
				var inputs = ["DegreeName"];//required fields
				var y = document.getElementById('Year');
				var z = document.getElementById("Requirements");
				
				for(var i = 0; i < inputs.length; i++)
				{
					var x = document.getElementById(inputs[i]).value;
					
					//Check for empty fields
					if(x == null || x == "")
					{
						alert("Please fill in all required fields");
						return false;
					}
					else if(/[^0-9]+/i.test(y.value))
					{
						alert("Please fill in the Year with numeric inputs only");
						return false;
					}
					else
					{
						for(var i = 0; i < z.length; i++) 
							z.options[i].selected = true;
					}

				}
				
				
			}
			
			
		
			function addCourseGroup()
			{
				var e1 = document.getElementById('NumberOfCourses');
				var e2 = document.getElementById('cgroup');
				var e3 = document.getElementById('Requirements');
				var e4 = " FROM ";
				var o=document.createElement('option');
				o.value = e1.value.concat(e4,e2.value);
				o.text = e1.value.concat(e4,e2.value);
				
				for(var i = 0; i < e3.length; i++)
				{
					if(e3.options[i].value === o.value)
					{
						alert("That requirement is already added.");
						return false;
					}
				}
				
				if (e1.value==null || e1.value=="")//check for empty form
				{
				  alert("Please input number of courses!");
				  return false;
				}
				else if (e2.value==null || e2.value=="")//check for empty form
				{
				  alert("Please select a course group first");
				  return false;
				}
				else if(/[^0-9]+/i.test(e1.value))//check for non numeric characters
				{
					alert("Please check for non-numeric characters!\n\ne.g. @#$Abc are not allowed!");
					return false;
				}
				else 
				{
					e3.options.add(o);
					//select.appendChild(from);
				}

			}
			
			function addOperator()
			{
				var e1 = document.getElementById('operator');
				var e2=document.getElementById('Requirements');
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
			function removeRequirement()
			{
				var e2=document.getElementById('Requirements');
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
			
			$(document).ready(function()
			{ $('button[name="sort"]').click(function()
				{
					var $op = $('#Requirements option:selected'),
						$this = $(this);
					if($op.length)
					{
						($this.val() == 'Up') ? 
							$op.first().prev().before($op) : 
							$op.last().next().after($op);
					}
				});
			});
			
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
				<li class="active"><a href="dprograms.php">Degree Programs</a></li>
				<li><a href="courses.php">Courses</a></li>
				<li><a href="cgroups.php">Course Groups</a></li>
				<li><a href="requirements.php">Requirements</a></li>
			</ul>
			
			<ul class="nav nav-pills">
				<li class="active"><a href="dprograms-add.php">Add Degree Program</a></li>
				<li><a href="dprograms-edit.php">Edit Degree Program</a></li>
				<li><a href="dprograms-delete.php">Delete Degree Program</a></li>
			</ul>
			<hr/>
			
			<?php
	//If add degree program form is submitted
	if(isset($_POST["submit"]))
	{
	
		//Sanitize & extract values
		if(isset($_POST["year"])&& !empty($_POST["year"]))
		{
			$year = s_int($_POST["year"]);
		}
		else
		{
			$year = "";
		}
		
		if(isset($_POST["requirements"])&& !empty($_POST["requirements"]))
		{
			$reqs = s_string(implode("|", $_POST["requirements"]));
		}
		else
		{
			$reqs = "";
		}
		
		if(isset($_POST["department"])&& !empty($_POST["department"]))
		{
			$dept = strtolower(s_string($_POST["department"]));
		}
		else
		{
			$dept = "";
		}
		
		$dn = strtoupper(s_string($_POST["degreename"]));

		
		//Setup database
		$host = DB_HOST;
		$dbname = DB_NAME;
		$user = DB_USER;
		$pass = DB_PASS;
		
		$dbh = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $user, $pass);
		$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	

		//Check for duplicates
		$sql = "SELECT * FROM degree WHERE degree_name = :dn";
		
		$sth = $dbh->prepare($sql);
		$sth->bindParam(":dn", $dn);
		$sth->execute();
		
		$rownum = $sth->rowCount();
		
		if($rownum)
		{
?>
			<div class="alert alert-error alert-block">
			 <button type="button" class="close" data-dismiss="alert"></button>
			 <h4>Oh Snap!</h4>
			<p><?php echo $dn ?> already exists in database...</p>
			<p>Make sure a degree program is named <b>uniquely</b></p>
			</div>
<?php
		}
		else
		{
			//Insert to degree
			$sql = "INSERT INTO degree(degree_name, year, department, degree_requirements) VALUES (:dn, :year, :dept, :reqs)";
			
			$sth = $dbh->prepare($sql);
			
			$sth->bindParam(":dn", $dn);
			$sth->bindParam(":year", $year);
			$sth->bindParam(":dept", $dept);
			$sth->bindParam(":reqs", $reqs);
				
			$sth->execute();
?>		
			<div class="alert alert-success alert-block">
			 <button type="button" class="close" data-dismiss="alert"></button>
			 <h4>Success!</h4>
			<p><?php echo $dn ?> successfully added</p>
			</div>
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
		
		//Get requirement list
		$sql = "SELECT requirement_id, requirement_name FROM requirements";
		
		$sth = $dbh->prepare($sql);
		$sth->execute();
		$rownum = $sth->rowCount();
		
		if(!$rownum)
			echo "There are no requirements to add to program.<br/>\n";
		else
		{
			$req_arr = $sth->fetchAll(PDO::FETCH_ASSOC);
?>			
			<div id="wrapper" class="fluid">
			<div class="row-fluid">
			<form id="frmOptions" method="post" class="well form-inline span12" onsubmit="return validateForm()">
			<h4>Add Degree Program</h4>
						
				<div class="alert alert-info">
					<button type="button" class="close" data-dismiss="alert"></button>
					<p>Please fill in the required fields (*) below and click <em>"Add Degree Program"</em> button.</p>
				</div>
			 
				<div class="row-fluid">   
					<div id="formLeft" class="span3">
						<div class="control-group">
								<label class="control-label" for="DegreeName">Degree Program Name*</label>
								<div class="controls">
									<input type="text" name="degreename" id="DegreeName" class="span12" placeholder="e.g. CS_2011.START_WITH_CS105">
								</div>
						</div>
					</div>
					
					<div id="formCenter" class="span3">
							<div class="control-group">
								<label class="control-label" for="Year">Year</label>
								<div class="controls">
									<input type="text" name="year" id="Year" class="span3"/>
								</div>
							</div>    
					</div>

				</div>
				
				<div class="row-fluid">
					<div id="formLeft" class="span3">
						<div class="control-group">
								<label class="control-label" for="Department">Department</label>
								<div class="controls">
									<select name="department" id="Department" class="span12">
										<option value="">Select a department...</option>
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
				<div class="alert alert-info">
					<button type="button" class="close" data-dismiss="alert"></button>
					<h4>Degree Requirements</h4>
					<p>Please add the following to the Degree Requirements box below to form a valid combination.</p>
				</div>
				
				<div class="row-fluid">
					<div id="formLeft" class="span2">
						<div class="control-group">
							<label class="control-label" for="NumberOfCourses">Number of Courses</label>
							<div class="controls">
								<input type="text" id="NumberOfCourses" class="span5"/>
							</div>
						</div>
					</div>
				</div>
				
				<div class="row-fluid">
					<div id="formLeft" class="span2">
						<div class="control-group">
							FROM
						</div>
					</div>
				</div>
					
					<?php
					$sql2 = "SELECT name FROM course_group";
					
					$sth2 = $dbh->prepare($sql2);
					$sth2->execute();
					$rownum2 = $sth2->rowCount();
					
					if(!$rownum2)
						echo "There are no course groups to add to the requirement.<br/>\n";
					else
					{
						$cgroup_arr = $sth2->fetchAll(PDO::FETCH_ASSOC);
					?>
						<div class="row-fluid">
						<div id="formCenter" class="span4">
							<div class="control-group">
									<label class="control-label" for="CourseGroup">Course Group</label>
									<div class="controls">
										<select name="cgroup" id="cgroup" class="span9">
										<option value="">Select a Course Group...</option>
										<?php
											foreach ($cgroup_arr as $row) 
											{
												$cgname = $row['name'];
												echo "<option value=\"$cgname\">" .$cgname. "</option>\n";
											}
										?>
										</select>
										<button name="cgroup-add" type="button" class="btn btn-success" onclick="addCourseGroup()"><i class="icon-plus"></i></button>
									</div>					
							</div>
						</div>
						</div>
					<?php
					}
					?>
					
				
				<div class="row-fluid">
				
				<div id="formLeft" class="span2">
				  <div class="control-group">
					<label for="operator" class="control-label">Operator</label>
					<div class="controls">
						<select id="operator" class="span5">
							<option value="">---</option>
							<option value="AND">AND</option>
							<option value="OR">OR</option>
						</select>
						<button class="btn btn-success" type="button" onclick="addOperator()" id="operator" title="Add Operator"><i class="icon-plus"></i></button>
					</div>      
				  </div>    
				</div>

				</div>
					
				<br>
				<div class="row-fluid">
					<div id="formLeft" class="span12">
							<div class="control-group">
								<label class="control-label" for="Degree Requirements">Degree Requirements</label>
								<div class="controls">
									<select multiple="multiple" name="requirements[]" id="Requirements" class="span4" size="5">
									</select>
									<div class="btn-group btn-group-vertical">
									  <button type="button" name="sort" class="btn" value="Up" id="moveup-btn" data-placement="right"title="Move Up"><i class="icon-arrow-up"></i></button>
									  <button type="button" name="sort" class="btn" value="Down" id="movedown-btn"  data-placement="right" title="Move Down"><i class="icon-arrow-down"></i></button>
									  <button type="button" class="btn btn-danger" onclick="removeRequirement()" id="remove-btn" data-placement="right"title="Remove selected requirement" ><i class="icon-remove"></i></button>
									</div>
								</div>
							</div>
					</div>
				</div>
				
				<div class="form-actions">
				  <button class="btn btn-primary" type="submit" name="submit">Add Degree Program</button>
				</div>
			 
			</form>
			</div>
			</div>

		<?php
		}
	}
		?>
			<footer>
				<p>© Study Planner 2013</p>
			</footer>
		</div>
	</body>
</html>