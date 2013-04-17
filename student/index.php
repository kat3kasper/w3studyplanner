<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Degree Programs</title>
		<?php require("../includes/styles.php"); ?>
		<?php require("../includes/config.php"); ?>
		<?php require("../includes/functions.php"); ?>
		<?php require("../includes/scripts.php"); ?>
	</head>
	<body>
		<?php require("../includes/navigation.php"); ?>
		
		<div class="container">
			<?php
				echo "<p>Welcome, " . $_SERVER["REDIRECT_displayName"] . "</p>";
			?>
		
<?php
	
		//Setup database
		$host = DB_HOST;
		$dbname = DB_NAME;
		$user = DB_USER;
		$pass = DB_PASS;
		
		$dbh = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $user, $pass);
		$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);	

		$sql = "SELECT * FROM degree";
		
		$sth = $dbh->prepare($sql);
		//$sth->bindParam(":year", $year);
		//$sth->bindParam(":dept", $dept);
		//$sth->bindParam(":degname", $degname);
		$sth->execute();
		$rownum = $sth->rowCount();
		$rowarray = $sth->fetchAll(PDO::FETCH_ASSOC); //move down
				
		if(!$rownum)
			echo "There is no degree available.<br/>\n";
		else
		{
	?>				
			<div class="well">
			<h4>Degree Programs</h4>
			
			<form class="form-horizontal" method="post" action="ssetup.php">
				<div class="control-group">
					<label class="control-label" for="YearEntered">Please select the year you entered school</label>
					<div class="controls">
						<select name="yearEntered" id="year">
							<option> Year Entered </option>
							<?php					
								for($year = date("Y"); $year > 2005; $year--)
								echo "<option>$year</option>"; 
							?>
						</select>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="Department">Please select the department your major is under</label>
					<div class="controls">
						<select name="department" id="department" class="span4">
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
				<div class="control-group">
					<label class="control-label" for="DegreeProgram">Please select your degree program</label>
					<div class="controls">
						<select name="degreeName" id="degreeName" class="span4">
							<option>Select your degree program</option>
<?php							
								foreach ($rowarray as $row)
								{
									$degname = $row['degree_name'];
									echo "<option value=\"$degname\">" .$degname. "</option>\n";
								}
?>
						</select>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<button type="submit" name="step2" class="btn btn-primary">Next</button>
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
	</body>
</html>