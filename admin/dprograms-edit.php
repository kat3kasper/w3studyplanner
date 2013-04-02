<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Edit Degree Program</title>
		<?php require("../includes/styles.php"); ?>
		<?php require("../includes/config.php"); ?>
		<?php require("../includes/functions.php"); ?>
		
		<script type="text/javascript">
			//Add requirement into list
			function addRequirement()
			{
				var e1 = document.getElementById("Requirement");
				var e2 = document.getElementById("Requirements");
				var o = document.createElement("option");
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
				var x = document.getElementById("Requirements");
				x.remove(x.selectedIndex);
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
				echo "<p>Welcome, " . $_ENV["REDIRECT_displayName"] . "</p>";
			?>
			
			<ul class="nav nav-tabs">
				<li><a href="/studyplanner/admin">Admin Home</a></li>
				<li class="active"><a href="dprograms.php">Degree Programs</a></li>
				<li><a href="courses.php">Courses</a></li>
				<li><a href="cgroups.php">Course Groups</a></li>
				<li><a href="requirements.php">Requirements</a></li>
			</ul>
			
			<ul class="nav nav-pills">
				<li><a href="dprograms-add.php">Add Degree Program</a></li>
				<li class="active"><a href="dprograms-edit.php">Edit Degree Program</a></li>
				<li><a href="dprograms-delete.php">Delete Degree Program</a></li>
			</ul>
			
			<hr/>
			
<?php
	//If newly edited degree program form is submitted
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
		$odn = strtoupper(s_string($_POST["olddegreename"]));
		
		//Check for duplicates
		$sql = "SELECT * FROM degree WHERE degree_name = :dn AND degree_name != :odn";
	
		$sth = $dbh->prepare($sql);
		$sth->bindParam(":dn", $dn);
		$sth->bindParam(":odn", $odn);
		$sth->execute();
		
		$rownum = $sth->rowCount();
		
		if($rownum)
			echo "Degree program already exists in database. Please choose a unique name.<br/>\n";
		else
		{
			//Insert to degree
			$sql = "UPDATE degree SET degree_name = :dn, year = :year, department = :dept, requirement_id = :rid WHERE degree_name = :odn";
			
			$sth = $dbh->prepare($sql);
			
			$sth->bindParam(":dn", $dn);
			$sth->bindParam(":year", $year);
			$sth->bindParam(":dept", $dept);
			$sth->bindParam(":rid", $rid);
			$sth->bindParam(":odn", $odn);
				
			$sth->execute();
			
			echo "Degree program successfully edited.<br/>\n";
		}
	}
	//If initial edit form is submitted
	else if(isset($_POST["submit"]) && !empty($_POST["degreename"]))
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
		
		//Check for duplicates
		$sql = "SELECT * FROM degree WHERE degree_name = :dn";
		
		$sth = $dbh->prepare($sql);
		$sth->bindParam(":dn", $dn);
		$sth->execute();
		
		$rownum = $sth->rowCount();
		
		if(!$rownum)
			echo "Degree program doesn't exist in database.<br/>\n";
		else
		{
			//Get degree details
			$row = $sth->fetch(PDO::FETCH_ASSOC);
			
			$year = $row["year"];
			$dept = $row["department"];
			$rid_list = $row["requirement_id"];
			
			echo "Replace the details below with new values:<br/><br/>\n";
?>

			<form class="form-horizontal" action="dprograms-edit.php" method="POST">
				<div class="control-group">
					<label class="control-label" for="DegreeName">Degree Program Name</label>
					<div class="controls">
						<input type="text" name="degreename" id="DegreeName" class="input-xlarge" placeholder="e.g. CS_2011.START_WITH_CS105" value="<?php if(isset($dn)) echo $dn; ?>" />
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="Year">Year</label>
					<div class="controls">
						<input type="text" name="year" id="Year" class="input-small" value="<?php if(isset($year)) echo $year; ?>" />
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
					<label class="control-label" for="Requirements">Requirements</label>
					<div class="controls">
						<p>
						<select multiple="multiple" name="requirements[]" id="Requirements" class="input-xlarge">
						
<?php
			//Get requirement associated with degree program
			if(!empty($rid_list))
			{
				$rid_arr = explode(",", $rid_list);
				foreach($rid_arr as $value)
				{
					$sql = "SELECT requirement_id, requirement_name FROM requirements WHERE requirement_id = :rid";
			
					$sth = $dbh->prepare($sql);
					$sth->bindParam(":rid", $value);
					$sth->execute();
					
					$row = $sth->fetch(PDO::FETCH_ASSOC);
					
					$rid = $row["requirement_id"];
					$rn = $row["requirement_name"];
					
					echo "<option value=\"" . $rid . "\">" . $rn . "</option>\n";
				}
			}
?>
						
						</select>
						</p>
						
						<p>
						<button class="btn btn-link" type="button" onclick="removeRequirement()">Remove Requirement</button>
						</p>
						
						<p>
						
<?php
			//Get requirement list
			$sql = "SELECT requirement_id, requirement_name FROM requirements";
			
			$sth = $dbh->prepare($sql);
			$sth->execute();
			$rownum = $sth->rowCount();
			
			if(!$rownum)
				echo "There are no requirements to add to program.<br/>\n";
			else
			{
?>
						
						<select name="requirement" id="Requirement">
						
<?php
				$req_arr = $sth->fetchAll(PDO::FETCH_ASSOC);
				foreach($req_arr as $inner_arr)
				{
					echo "<option value=\"" . $inner_arr["requirement_id"] . "\">" . ucwords($inner_arr["requirement_name"]) . "</option>\n";
				}
?>
						
						</select>
						<button class="btn" type="button" onclick="addRequirement();">+</button>
						
<?php
			}
?>
						
						</p>
						
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<input type="hidden" name="olddegreename" value="<?php echo $dn; ?>">
						<button class="btn btn-primary" type="submit" name="submit" onclick="selectAllRequirements()" >Edit Degree Program</button>
					</div>
				</div>
			</form>

<?php
		}
	}
	//First run of edit degree program
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
		
		//Get degree list
		$sql = "SELECT degree_name FROM degree";
		
		$sth = $dbh->prepare($sql);
		$sth->execute();
		$rownum = $sth->rowCount();
		
		if(!$rownum)
			echo "There are no degree programs available.<br/>\n";
		else
		{
			$deg_arr = $sth->fetchAll(PDO::FETCH_COLUMN);
?>
			
			<h4>Edit Degree Program</h4>
			<p>Please select a degree program and click <em>"Edit Degree Program"</em> button.</p>
			
			<form class="form-horizontal" action="dprograms-edit.php" method="POST">
				<div class="control-group">
					<label class="control-label" for="DegreeName">Degree Program Name</label>
					<div class="controls">
						<select name="degreename" id="DegreeName">
							
<?php
			foreach($deg_arr as $value)
			{
				echo "<option value=\"" . $value . "\">" . $value . "</option>\n";
			}
?>
							
						</select>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<button class="btn btn-primary" type="submit" name="submit">Edit Degree Program</button>
					</div>
				</div>
			</form>
			
<?php
		}
	}
?>
			
			<footer>
				<p>© Study Planner 2013</p>
			</footer>
		</div>
		
		<?php require("../includes/scripts.php"); ?>
	</body>
</html>