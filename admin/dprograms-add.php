<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Add Degree Program</title>
		<?php require("../includes/styles.php"); ?>
		<?php require("../includes/config.php"); ?>
		<?php require("../includes/functions.php"); ?>
		
		<script type="text/javascript">
			//Add requirement into list
			function addRequirement()
			{
				var e1 = document.getElementById('Requirement');
				var e2 = document.getElementById('Requirements');
				var o = document.createElement('option');
				o.value = e1.options[e1.selectedIndex].value;
				o.text = e1.options[e1.selectedIndex].text;
				
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
				  alert("Please select a requirement from the list above first");
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
			
			//select all courses in the list to be stored in database
			function selectAllRequirements()
			{
				var x = document.getElementById("Requirements");
				for(var i = 0; i < x.length; i++) 
					x.options[i].selected = true;
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
	if(isset($_POST["submit"]) && (!empty($_POST["degreename"]) && !empty($_POST["requirements"])))
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
		$dn = strtoupper(s_string($_POST["degreename"]));
		$year = s_int($_POST["year"]);
		$dept = strtolower(s_string($_POST["department"]));
		$rid = s_string(implode(",", $_POST["requirements"]));
		
		//Check for duplicates
		$sql = "SELECT * FROM degree WHERE degree_name = :dn";
		
		$sth = $dbh->prepare($sql);
		$sth->bindParam(":dn", $dn);
		$sth->execute();
		
		$rownum = $sth->rowCount();
		
		if($rownum)
			echo "Degree program already exists in database.<br/>\n";
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
			
			echo "Degree program successfully added.<br/>\n";
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
			<p>Please fill the required fields and click <em>"Add Degree Program"</em> button.</p>
			<form class="form-horizontal" action="dprograms-add.php" method="POST">
				
			
				<div class="control-group">
					<label class="control-label" for="DegreeName">Degree Program Name</label>
					<div class="controls">
						<input type="text" name="degreename" id="DegreeName" class="span4" placeholder="e.g. CS_2011.START_WITH_CS105">		
					<!--
					<label for="Year">Year</label>
						<input type="text" name="year" id="Year" class="span1">
					-->
					</div>
				</div>
				
				<div class="control-group">
					<label class="control-label" for="Year">Year</label>
					<div class="controls">
						<input type="text" name="year" id="Year" class="span1" />
					</div>
				</div>
					
					
				<div class="control-group">
					<label class="control-label" for="Department">Department</label>
					<div class="controls">
						<select name="department" id="Department" class="span4">
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
							
<?php
			foreach($req_arr as $inner_arr)
			{
				echo "<option value=\"" . $inner_arr["requirement_id"] . "\">" . ucwords($inner_arr["requirement_name"]) . "</option>\n";
			}
?>
							
						</select>
						<button class="btn btn-info" type="button" onclick="addRequirement()" id="add-btn" rel="tooltip" data-placement="right" data-trigger="hover" title="Add Requirement to the list"><i class="icon-plus"></i></button>

					</div>
				</div>
					
				<div class="control-group">
					<label class="control-label" for="Degree Requirements">Degree Requirements</label>
					<div class="controls">
						
						<select multiple="multiple" name="requirements[]" id="Requirements" class="span4" size="5">
						</select>
					
						<div class="btn-group btn-group-vertical">
						  <button type="button" name="sort" class="btn" value="Up" id="moveup-btn" rel="tooltip" data-placement="right" data-trigger="hover" title="Move Up"><i class="icon-arrow-up"></i></button>
						  <button type="button" name="sort" class="btn" value="Down" id="movedown-btn" rel="tooltip" data-placement="right" data-trigger="hover" title="Move Down"><i class="icon-arrow-down"></i></button>
						  <button type="button" class="btn btn-danger" onclick="removeRequirement()" id="remove-btn" rel="tooltip" data-placement="right" data-trigger="hover" title="Remove selected requirement" ><i class="icon-remove"></i></button>
						</div>
					
						
					</div>
				</div>
				
				<div class="form-actions">
				  <button type="submit" name="submit" class="btn btn-primary" onclick="selectAllRequirements()" >Add Degree Program</button>
				  <button type="submit" class="btn" onclick="/dprograms-add.php">Cancel</button>
				</div>
				<!--
				<div class="control-group">
					<div class="controls">
						<button class="btn btn-primary" type="submit" name="submit" onclick="selectAllRequirements()" >Add Degree Program</button>
					</div>
				</div>
				-->
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
		<?php require("../includes/scripts.php"); ?>
		<script>
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
		
		$(document).ready(function()
		{ $('#remove-btn').tooltip();
			$('#moveup-btn').tooltip();
			$('#movedown-btn').tooltip();
			$('#add-btn').tooltip();
		});
		
		
		</script>
	</body>
</html>