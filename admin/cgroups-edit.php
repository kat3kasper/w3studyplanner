<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Edit Course Group</title>
		<?php require("../includes/styles.php"); ?>
		<?php require("../includes/config.php"); ?>	
		<?php require("../includes/functions.php"); ?>
		<?php require("../includes/scripts.php"); ?>
		
		<script type="text/javascript">
			//Validates form inputs
			function validateForm1()
			{
				var w = document.getElementById("cgroup");
				//Check for empty fields
				if (w.value==null || w.value=="")
				{
					alert("Please select a Course Group from the list!");
					return false;
				}
			}
			
			//Validates form inputs
			function validateForm2()
			{
				var inputs = ["CGName"];//required fields
		
				for(var i = 0; i < inputs.length; i++)
				{
					var x = document.getElementById(inputs[i]).value;
					
					//Check for empty fields
					if(x == null || x == "")
					{
						alert("Please fill in all required fields");
						return false;
					}
					else
					{
						var x=document.getElementById("courses");
						for (var i=0; i<x.length; i++) 
						{
							x.options[i].selected = true;
						}
					}
				}
				
			}
			
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
				var e1=document.getElementById('course');
				var e2=document.getElementById('courses');
				var o=document.createElement('option');
				o.value=e1.value;
				o.text=e1.value;
				
				if (e2.value==null || e2.value=="")//check for empty form
				{
				  alert("Please select a course from the list above first");
				  return false;
				}
				
				var ce=confirm("Are you sure you want to remove the course from the course group?");
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
					var $op = $('#courses option:selected'),
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
				<li class="active"><a href="cgroups.php">Course Groups</a></li>
				<li><a href="dprograms.php">Degree Programs</a></li>
			</ul>
			
			<ul class="nav nav-pills">
				<li><a href="cgroups-add.php">Add Course Group</a></li>
				<li class="active"><a href="cgroups-edit.php">Edit Course Group</a></li>
				<li><a href="cgroups-delete.php">Delete Course Group</a></li>
			</ul>
			
			<hr/>
			
<?php		
	//If edited form of course group is submitted
	if(isset($_POST["submit2"]))
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
		if(isset($_POST["course_id"])&& !empty($_POST["course_id"]))
		{
			$course_id = s_string(implode(",", $_POST["course_id"])); //comma separate courses before storing into db
		}
		else
		{
			$course_id = "";
		}
			
		//Sanitize & extract values
		if(isset($_POST["course_id"])&& !empty($_POST["course_id"]))
		{
			$ocgname = strtoupper(s_string($_POST["oldcgname"]));
		}
		else
		{
			$ocgname = "";
		}
			
		$cgname = strtoupper(s_string($_POST["cgname"]));
		
		
		//Check for duplicates
		$sql = "SELECT * FROM course_group WHERE name = :cgname AND name != :ocgname";
		
		$sth = $dbh->prepare($sql);
		$sth->bindParam(":cgname", $cgname);
		$sth->bindParam(":ocgname", $ocgname);
		$sth->execute();
		
		$rownum = $sth->rowCount();
		
		if($rownum)
		{
			?>
			<div class="alert alert-error alert-block">
				<button type="button" class="close" data-dismiss="alert"></button>
				<h4>Oh Snap!</h4>
				<p><?php echo $cgname; ?> already exists in database...</p>
				<p>Make sure a course group is named <b>uniquely</b></p>
			</div>
<?php
		}
		else
		{
			$valid_courses = 1;
			$invalid_list = "<ul>";
			
			//Check if courses exist in database
			if(isset($_POST["course_id"]))
				foreach($_POST["course_id"] as $c)
				{
					$sql = "SELECT * FROM course WHERE CONCAT(prefix, number) = :c";
			
					$sth = $dbh->prepare($sql);
					$sth->bindParam(":c", $c);
					$sth->execute();
					
					$rownum = $sth->rowCount();
					
					if(!$rownum)
					{
						$valid_courses = 0;
						$invalid_list .= "<li>" . $c . "</li>";
					}
				}
			$invalid_list .= "</ul>";
			
			if($valid_courses)
			{
				//Update course
				$sql = "UPDATE course_group SET name= :cgname, course_id = :course_id WHERE name= :ocgname";
				
				$sth = $dbh->prepare($sql);
				
				$sth->bindParam(":cgname", $cgname);
				$sth->bindParam(":course_id", $course_id);	
				$sth->bindParam(":ocgname", $ocgname);
				$sth->execute();
						
	?>
				<div class="alert alert-success alert-block">
					<button type="button" class="close" data-dismiss="alert"></button>
					<h4>Success!</h4>
					<p>Changes saved successfully</p>
				</div>
<?php
			}
			else
			{
?>
			<div class="alert alert-error alert-block">
				<button type="button" class="close" data-dismiss="alert"></button>
				<h4>Oh Snap!</h4>
				<p>The following courses doesn't exist in the database:<?php echo $invalid_list; ?></p>
				<p>Please only enter courses that have been added to the database first</p>
			</div>
<?php
			}
		}
		
	}
	else if(isset($_POST["submit1"])) //if edit course group form is submitted
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
			
?>
			<div class="well">
			<h4>Edit Course Group</h4>
			<div class="alert alert-info">
				<button type="button" class="close" data-dismiss="alert"></button>
				<p>Don't forget to hit <em>"Save Changes"</em> once you're done!</p>
			</div>
			<form class="form-horizontal" action="cgroups-edit.php" method="POST" onsubmit="return validateForm2()">
				<div class="control-group">
					<label class="control-label" for="CGName">Course Group Name*</label>
					<div class="controls">
						<input type="text" name="cgname" id="CGName" placeholder="CS_Group_A_Literature/Philosophy" class="span4" value="<?php if(isset($cgname)) echo $cgname; ?>"> 
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label" for="course">Find or Add Course</label>
					<div class="controls">
						<input type="text" name="course" id="course" class="input-small" placeholder="e.g. CS101" />
						<button type="button" class="btn btn-info" onclick="Javascript:newPopup('courses-find.php');" title="Find Course"><i class="icon-search"></i></button>
						</a>
						<button class="btn btn-success" type="button" onclick="addCourse()" id="add-btn" title="Add Course to the list" value="Add to List" ><i class="icon-plus"></i></button>	
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label" for="Courses">Courses in Course Group</label>
					<div class="controls">
						<select multiple="multiple" name="course_id[]" id="courses" class="span4" size="5">
						<?php
							foreach ($courses as $course_id) 
							{
								echo "<option value=\"$course_id\">" .$course_id. "</option>\n";
							}		
						?>
						</select>
						<div class="btn-group btn-group-vertical">
						  <button type="button" name="sort" class="btn" value="Up" id="moveup-btn" data-placement="right"title="Move Up"><i class="icon-arrow-up"></i></button>
						  <button type="button" name="sort" class="btn" value="Down" id="movedown-btn"  data-placement="right" title="Move Down"><i class="icon-arrow-down"></i></button>
						  <button type="button" class="btn btn-danger" onclick="removeCourse()" id="remove-btn" data-placement="right"title="Remove selected course" ><i class="icon-remove"></i></button>
						</div>
						
					</div>
				</div>
				
	
				
				<div class="form-actions">
					<input type="hidden" name="oldcgname" value="<?php echo $cgname; ?>">
					<button type="submit" name="submit2" class="btn btn-primary">Save Changes</button>
				</div>
				
			</form>
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
			<div class="well">
			<h4>Edit Course Group</h4>
			<div class="alert alert-info">
				<button type="button" class="close" data-dismiss="alert"></button>
				<p>Please select course group and click <em>"Edit Course Group"</em> button.</p>
			</div>
			
			<form class="form-horizontal" method="post" action="cgroups-edit.php" onsubmit="return validateForm1()">
				<div class="control-group">
					<label class="control-label" for="CourseGroup">Select Course Group</label>
					<div class="controls">
						<select name="cgroup" id="cgroup" class="span4">
							<option value="">--Course Groups--</option>
							
		<?php
			foreach ($rowarray as $row) 
			{
				$cgname = $row['name'];
				echo "<option value=\"$cgname\">" .$cgname. "</option>\n";
			}
					
		?>
						</select>
					</div>
				</div>
					
				<div class="form-actions">
					<button name="submit1" type="submit" class="btn btn-primary">Edit Course Group</button>
				</div>
				
			</form>
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