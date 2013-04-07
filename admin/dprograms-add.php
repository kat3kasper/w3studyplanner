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
			
			//select all courses in the list to be stored in database
			function selectAll()
			{	
				var x = document.getElementById("Requirements");
				for(var i = 0; i < x.length; i++) 
					x.options[i].selected = true;
		
			}
		
			//Add requirement into list
			function addRequirement()
			{
				var e1 = document.getElementById('Requirement');
				var e2 = document.getElementById('Requirements');
				var o = document.createElement('option');
				o.value = e1.options[e1.selectedIndex].value;
				o.text = e1.options[e1.selectedIndex].text;
				
				if (e1.value==null || e1.value=="")//check for empty form
				{
				  alert("Please select a requirement from the list first");
				  return false;
				}
				
				for(var i = 0; i < e2.length; i++)
				{
					if(e2.options[i].value === o.value)
					{
						alert("That requirement is already added.");
						return false;
					}
				}
				
				e2.options.add(o);
			}
			
			//Remove course from the list
			function removeRequirement()
			{
				var e2 = document.getElementById("Requirements");
				//x.remove(x.selectedIndex);
				if (e2.value==null || e2.value=="")//check for empty form
				{
				  alert("Please select a requirement from the list first");
				  return false;
				}
				
				var ce=confirm("Are you sure you want to remove the selected requirement?");
				if(ce===true)
				{
					e2.remove(e2.selectedIndex);
				}
				else
				{
					return false;
				}
			}
			
			/*
			//select all courses in the list to be stored in database
			function validate()
			{	
				var x = document.getElementById("Requirements");
				for(var i = 0; i < x.length; i++) 
					x.options[i].selected = true;
		
			}
			*/
			
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
			$rid = s_string(implode(",", $_POST["requirements"]));
		}
		else
		{
			$rid = "";
		}
		
		if(isset($_POST["department"])&& !empty($_POST["department"]))
		{
			$dept = strtolower(s_string($_POST["department"]));
		}
		else
		{
			$rid = "";
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
			$sql = "INSERT INTO degree(degree_name, year, department, requirement_id) VALUES (:dn, :year, :dept, :rid)";
			
			$sth = $dbh->prepare($sql);
			
			$sth->bindParam(":dn", $dn);
			$sth->bindParam(":year", $year);
			$sth->bindParam(":dept", $dept);
			$sth->bindParam(":rid", $rid);
				
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
			<div class="well">
			<h4>Add Degree Program</h4>
			
			<div class="alert alert-info">
				<button type="button" class="close" data-dismiss="alert"></button>
				<p>Please fill in the required fields (*) below and click <em>"Add Degree Program"</em> button.</p>
			</div>

			<form class="form-horizontal" action="dprograms-add.php" method="POST" onsubmit="return validateForm()">
				<div class="control-group">
					<label class="control-label" for="DegreeName">Degree Program Name*</label>
					<div class="controls">
						<input type="text" name="degreename" id="DegreeName" class="span4" placeholder="e.g. CS_2011.START_WITH_CS105">
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label" for="Year">Year</label>
					<div class="controls">
						<input type="text" name="year" id="Year" class="span1"/>
					</div>
				</div>
					
					
				<div class="control-group">
					<label class="control-label" for="Department">Department</label>
					<div class="controls">
						<select name="department" id="Department" class="span4">
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
				
				<div class="control-group">
					<label class="control-label" for="Requirements">Requirements</label>
					<div class="controls">
						<select name="requirement" id="Requirement" class="span4">
							<option value="">Select a Requirement...</option>
								<?php
								foreach($req_arr as $inner_arr)
								{
									echo "<option value=\"" . $inner_arr["requirement_id"] . "\">" . ucwords($inner_arr["requirement_name"]) . "</option>\n";
								}
								?>	
						</select>
						<button class="btn btn-success" type="button" onclick="addRequirement()" id="add-btn" title="Add Requirement to the list"><i class="icon-plus"></i></button>
					</div>
				</div>
					
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
				
				<div class="form-actions">
				  <button type="submit" name="submit" class="btn btn-primary">Add Degree Program</button>
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