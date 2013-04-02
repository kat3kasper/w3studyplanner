<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Add Courses to Course Group</title>
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
					for(var i = 0; i < e2.length; i++)
					{
						if(e2.options[i].value === o.value)
						{
							alert("That course is already added.");
							return false;
						}
					}
					
					e2.options.add(o);
				}
			
			}
			//Sends value to main window
			function SendValueToParent(course)
			{
				//var myVal = document.getElementById('').value;
				window.opener.GetValueFromChild(course);
				window.close();
				return false;
			}
		</script>
		
	</head>
	<body>
			
		<div class="container">
		
	<?php
		//If add course is submitted & course is not empty
		/*if(isset($_POST["submit"]) && !empty($_POST["course"]))
		{
		
			//Setup database
			$host = DB_HOST;
			$dbname = DB_NAME;
			$user = DB_USER;
			$pass = DB_PASS;
			
			$dbh = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $user, $pass);
			$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION
			
			//Sanitize & extract values
			$course = strtoupper(s_string($_POST["course"]));
			
			//Check if course exists
			$sql = "SELECT * FROM course WHERE CONCAT(prefix, name) = :cid";
			
			$sth = $db->prepare($sql);
			$sth->bindParam(":cid", $cid);
			$sth->execute();
			
			$rownum = $sth->rowCount();
			
			if(!$rownum)
				echo "Course ". $cid . "does not exist in database.<br/>\n";
			else
			{
			}
		} */
		?>
	
		
			<h4>Add Course to Course Group</h4>
			<p>Please enter the course prefix & number and click <em>"Add Course"</em> button.</p>
			
			<form class="form-horizontal" action="courses-add-to-cgroup.php" method="POST">
				<div class="control-group">
					<label class="control-label" for="course">Course</label>
					<div class="controls">
						<input type="text" name="course" id="course" class="input-small" placeholder="e.g. CS101" />
						<a href="Javascript:newPopup('courses-find.php');"><button type="button" class="btn btn-info">Find</button></a>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<button class="btn" type="button" value="Add to List" onclick="addCourse()">Add Course</button>
					</div>
				</div>
			</form>
			
		
			<footer>
				<p>© Study Planner 2013</p>
			</footer>
		</div>
		
		<?php require("../includes/scripts.php"); ?>
	</body>
</html>