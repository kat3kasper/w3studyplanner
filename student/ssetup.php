<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Semester Setup</title>
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
	//Coming from Degree Program setup
	if(isset($_POST["step2"]))
	{
		//Setup database
		$host = DB_HOST;
		$dbname = DB_NAME;
		$user = DB_USER;
		$pass = DB_PASS;
		
		$dbh = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $user, $pass);
		$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		$yearEntered = s_int($_POST["yearEntered"]);
		$department = s_string($_POST["department"]);
		$degreeName = s_string($_POST["degreeName"]);
		
		$step1Info = array($yearEntered, $department, $degreeName);
?>
			
			<h4>Semester Setup</h4>
			
			<form class="form-horizontal" method="post" action="ssetup.php">
				<div class="control-group">
					<label class="control-label">Please enter the semester you wish to graduate</label>
					<div class="controls">
						<select name="termGraduate" id="termGraduate">
							<option>Spring</option>
							<option>Summer</option>
							<option>Fall</option>
						</select>
						<select name="yearGraduate" id="yearGraduate">
							<?php
								$limit = date("Y") + 10;
								for($year = date("Y"); $year < $limit; $year++)
									echo "<option>" . $year . "</option>"; 
							?>
						</select>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<input type="hidden" name="step1Info" value="<?php echo $step1Info; ?>">
						<button type="submit" name="step3" class="btn btn-primary">Next</button>
					</div>
				</div>
			</form>
			
<?php
	}
	//Coming from Semester setup part 1: term & year
	else if(isset($_POST["step3"]))
	{
		//Setup database
		$host = DB_HOST;
		$dbname = DB_NAME;
		$user = DB_USER;
		$pass = DB_PASS;
		
		$dbh = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $user, $pass);
		$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		$termGraduate = s_string($_POST["termGraduate"]);
		$yearGraduate = s_int($_POST["yearGraduate"]);
		$step1Info = $_POST["step1Info"];
		
		$step2Info = array($termGraduate, $yearGraduate);
?>
			
			<h4>Semester Setup</h4>
			
			<p>Please choose how many credits you wish to take per semester:</p>
			
			<form class="form-horizontal" method="post" action="cpreferences.php">
				<table class="table table-bordered table-condensed well">
					<thead>
						<tr>
							<th>Year</th>
							<th>	</th>
							<th>Fall</th>
							<th>Spring</th>
							<th>Summer 1</th>
							<th>Summer 2</th>
						</tr>
					</thead>
					<tbody>
						
<?php
	$yearCurrent = 2012;//date("Y");
	for(; $yearCurrent < $yearGraduate; $yearCurrent++)
	{
		for($k = 0; $k < 2; $k++)
		{
			echo "<tr>";
			if($k === 0)
				echo "<td rowspan=\"2\">" . $yearCurrent . "-" . ($yearCurrent + 1) . "</td>";
			
			echo "<td>" . ($k === 0 ? "Max" : "Min") . "</td>";
			
			for($j = 0; $j < 4; $j++)
			{
				echo "<td>";
				echo "<select name=\"" . $yearCurrent . ($k === 0 ? "Max" : "Min") . "Credits[]\">";
				
				for($i = 0; $i < 30; $i++)
					echo "<option>" . $i . "</option>";
				
				echo "</select>";
				echo "</td>";
			}
			
			echo "</tr>";
		}
	}
?>
						
					</tbody>	
				</table>
				
				<div class="control-group">
					<div class="controls">
						<input type="hidden" name="step1Info" value="<?php echo $step1Info; ?>">
						<input type="hidden" name="step2Info" value="<?php echo $step2Info; ?>">
						<button type="submit" name="step4" class="btn btn-primary">Next</button>
					</div>
				</div>
			</form>
			
<?php
	}
	else
		header("Location: index.php");
?>
			
			<footer>
				<p>© Study Planner 2013</p>
			</footer>
		</div>
	</body>
</html>