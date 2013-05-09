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
				var inputs = ["DegreeName", "Year", "Department"];//required fields
				//var y = document.getElementById('Year');
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
					else if(inputs[i] == "Year")
					{
					if(isNaN(x))
						{
							alert("Please fill in the Year with numeric inputs only");
							return false;
						}
					}
				}


					/*if(/[^0-9]+/i.test(y.value))
					{
						alert("Please fill in the Year with numeric inputs only");
						return false;
					}*/
				
				for(var i = 0; i < z.length; i++) 
					z.options[i].selected = true;
			}
				
			
			//Clear text area
			function clearTextArea()
			{
				document.getElementById('NumberOfCourses').value = "";
				document.getElementById('cgroup').value = "";
			}
			
			//Add constraint into the list
			function addCourseGroup()
			{
				var e1 = document.getElementById('NumberOfCourses');
				var e2 = document.getElementById('cgroup');
				var e3 = document.getElementById('Requirements');
				var e4 = " FROM ";
				var o = document.createElement('option');
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
					clearTextArea();
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
			
			//Make sure number of courses required from group doesn't exceed course group size
			function checkNumberOfCourses()
			{
				var noc = document.getElementById("NumberOfCourses");
				var cg = document.getElementById("cgroup");
				
				var xmlhttp;
				
				//Modern browsers
				if(window.XMLHttpRequest)
					xmlhttp = new XMLHttpRequest();
				//IE5 & 6
				else
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				
				xmlhttp.onreadystatechange = function()
				{
					if(xmlhttp.readyState == 4 && xmlhttp.status == 200)
					{
						var res = xmlhttp.responseText;
						if(parseInt(noc.value) > parseInt(res))
							noc.value = res;
					}
				}
				
				xmlhttp.open("GET", "../includes/dprogram_update.php?cgroup=" + cg.value, true);
				xmlhttp.send();
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
				<li><a href="index.php">Admin Home</a></li>
				<li><a href="courses.php">Courses</a></li>
				<li><a href="cgroups.php">Course Groups</a></li>
				<li class="active"><a href="dprograms.php">Degree Programs</a></li>
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
			$reqs = s_string(implode(",", $_POST["requirements"]));
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
		$sql = "SELECT degree_requirements FROM degree";
		
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
								<label class="control-label" for="Year">Year*</label>
								<div class="controls">
									<input type="text" name="year" id="Year" class="span3"/>
								</div>
							</div>    
					</div>

				</div>
				
				<div class="row-fluid">
					<div id="formLeft" class="span3">
						<div class="control-group">
								<label class="control-label" for="Department">Department*</label>
								<div class="controls">
									<select name="department" id="Department" class="span12">
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
				<h4>Degree Requirements</h4>
				<p>Please add the following to the Degree Requirements box below to form a valid combination.</p>
				<br>
				
				<div class="row-fluid">
					<div id="formLeft" class="span2">
						<div class="control-group">
							<label class="control-label" for="NumberOfCourses">Number of Courses</label>
						</div>
					</div>

					<div id="formCenter" class="span4">
						<div class="control-group">
								<label class="control-label" for="CourseGroup">Course Group</label>
						</div>
					</div>
				</div>

				<div class="row-fluid" id="dynamicInput">
					<div id="formLeft" class="span2">
						<div class="control-group">
							<div class="controls">
								<input type="text" id="NumberOfCourses" class="span5" name="NumberOfCourses[]" onChange="checkNumberOfCourses()"/>
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
				
						<div id="formCenter" class="span4">
							<div class="control-group">
									<div class="controls">
										<select id="cgroup" class="span11" name="cgroup[]" onChange="checkNumberOfCourses()">
										<option value="">Select a Course Group...</option>
										<?php
											foreach ($cgroup_arr as $row) 
											{
												$cgname = $row['name'];
												echo "<option value=\"$cgname\">" .$cgname. "</option>\n";
											}
										?>
										</select>
									</div>					
							</div>
						</div>
					
					<?php
					}
					?>
					
				</div>	
					
				<br>
				<div class="row-fluid">
					<div id="formLeft" class="span12">
							<div class="control-group">
								<label class="control-label" for="Degree Requirements">Degree Requirements</label>
				 				<div class="controls">
									<select multiple="multiple" name="requirements[]" id="Requirements" class="span7" size="7">
									</select>
									<div class="btn-group btn-group-vertical">
									  <button class="btn btn-success" id="dynamic-btn" type="button" onClick="addCourseGroup()" title="Add Requirement"><i class="icon-plus"></i></button>
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