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
		$yearEntered = s_int($_POST["yearEntered"]);
		$department = s_string($_POST["department"]);
		$degreeName = s_string($_POST["degreeName"]);
		
		$step1Info = htmlspecialchars(json_encode(array($yearEntered, $department, $degreeName)));
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
		$termGraduate = s_string($_POST["termGraduate"]);
		$yearGraduate = s_int($_POST["yearGraduate"]);
		$step1Info = htmlspecialchars($_POST["step1Info"]);
		
		$step2Info = htmlspecialchars(json_encode(array($termGraduate, $yearGraduate)));
?>
			
			<h4>Semester Setup</h4>
			
			<p>Please choose how many credits you wish to take per semester:</p>
			
			<form class="form-horizontal" method="post" action="ssetup.php">
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
	//Shows table from current year to graduation year
	$yearCurrent = date("Y");
	for(; $yearCurrent < $yearGraduate; $yearCurrent++)
	{
		//2 rows for max/min credits
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
				
				for($i = 0; $i <= 30; $i++)
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
	else if(isset($_POST["step4"]))
	{
		echo "These should pass to cpreferences.php<br/>";
		
		echo "Testing json data so far...<br/>";
		
		echo "<br/>step1Info<br/>";
		echo "Encoded: " . $_POST["step1Info"] . "<br/>";
		echo "Decoded: ";
		var_dump(json_decode(htmlspecialchars_decode($_POST["step1Info"])));
		
		echo "<br/><br/>step2Info<br/>";
		echo "Encoded: " . $_POST["step2Info"] . "<br/>";
		echo "Decoded: ";
		var_dump(json_decode(htmlspecialchars_decode($_POST["step2Info"])));
		
		echo "<br/><br/>\$_POST dump<br/>";
		var_dump($_POST);
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