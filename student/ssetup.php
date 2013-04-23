<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Semester Setup</title>
		<?php require("../includes/styles.php"); ?>
		<?php require("../includes/config.php"); ?>
		<?php require("../includes/functions.php"); ?>
		<?php require("../includes/scripts.php"); ?>
		
		<script type="text/javascript">
			//Validate form inputs
			function validateForm()
			{
				var inputs = ["termGraduate", "yearGraduate"];
				
				for(var i = 0; i < inputs.length;i++)
				{
					var x = document.getElementById(inputs[i]).value;
					
					//Check for empty fields
					if(x == null || x == "")
					{
						alert("Please fill in all required fields");
						return false;
					}
				}
			}
			
			//Validate semester credits table
			function validateSem(yearCurrent, yearGraduate)
			{
				//Form control b/c of brackets in child names
				var myForm = document.getElementById("semesterCredits");
				
				for(; yearCurrent <= yearGraduate; yearCurrent++)
				{
					var max = myForm["maxCredits[" + yearCurrent + "][]"];
					var min = myForm["minCredits[" + yearCurrent + "][]"];
					
					for(x = 0; x < max.length; x++)
						if(parseInt(max[x].value) < parseInt(min[x].value))
						{
							alert("Maximum credit values should be equal to or larger than minimum values");
							return false;
						}
				}
			}
		</script>
		
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
		
		$step1Info = htmlspecialchars(json_encode(
			array(
				"yearEntered" => $yearEntered,
				"department" => $department,
				"degreeName" => $degreeName
			)));
?>
			
			<h4>Semester Setup</h4>
			
			<form class="form-horizontal" method="post" action="ssetup.php" onSubmit="return validateForm()">
				<div class="control-group">
					<label class="control-label">Please enter the semester you wish to graduate</label>
					<div class="controls">
						<select name="termGraduate" id="termGraduate">
							<option value="">Select a term..</option>
							<option value="spring">Spring</option>
							<option value="fall">Fall</option>
						</select>
						<select name="yearGraduate" id="yearGraduate">
							<option value="">Select a year..</option>
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
		
		$step2Info = htmlspecialchars(json_encode(
			array(
				"termGraduate" => $termGraduate,
				"yearGraduate" => $yearGraduate
			)));
?>
			
			<h4>Semester Setup</h4>
			
			<p>Please choose how many credits you wish to take per semester:</p>
			
			<form class="form-horizontal" id="semesterCredits" method="post" action="cpreferences.php" onSubmit="return validateSem(<?php echo date("Y") . ", " . $yearGraduate; ?>)">
				<table class="table table-bordered table-condensed well">
					<thead>
						<tr>
							<th>Year</th>
							<th>	</th>
							<th>Spring</th>
							<th>Summer 1</th>
							<th>Summer 2</th>
							<th>Fall</th>
						</tr>
					</thead>
					<tbody>
						
<?php
	//Shows table from current year to graduation year
	for($yearCurrent = date("Y"); $yearCurrent <= $yearGraduate; $yearCurrent++)
	{
		//2 rows for max/min credits
		for($k = 0; $k < 2; $k++)
		{
			echo "<tr>";
			if($k === 0)
				echo "<td rowspan=\"2\">" . $yearCurrent . "</td>";
			
			echo "<td>" . ($k === 0 ? "Max" : "Min") . "</td>";
			
			for($j = 0; $j < 4; $j++)
			{
				echo "<td>";
				echo "<select name=\"" . ($k === 0 ? "max" : "min") . "Credits[" . $yearCurrent . "][]\">";
				
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
	else
		header("Location: index.php");
?>
			
			<footer>
				<p>© Study Planner 2013</p>
			</footer>
		</div>
	</body>
</html>