<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Degree Programs</title>
		<?php require("../includes/styles.php"); ?>
		<?php require("../includes/config.php"); ?>
		<?php require("../includes/functions.php"); ?>
		<?php require("../includes/scripts.php"); ?>
		
		<script type="text/javascript">
			//Validate form inputs
			function validateForm()
			{
				var inputs = ["yearEntered", "department", "degreeName"];
				
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
			
			function updateDegree()
			{
				var year = document.getElementById("yearEntered").value;
				var dept = document.getElementById("department").value;
				
				if(year == "" || dept == "")
				{
					document.getElementById("degreeName").innerHTML = "<option value=\"\">Select your degree program</option>";
					return;
				}
				
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
						document.getElementById("degreeName").innerHTML = res;
					}
				}
				
				xmlhttp.open("GET", "../includes/index_update.php?year=" + year + "&dept=" + dept, true);
				xmlhttp.send();
			}
		</script>
	</head>
	<body>
		<?php require("../includes/navigation.php"); ?>
		
		<div class="container">
			<?php
				echo "<p>Welcome, " . $_SERVER["REDIRECT_displayName"] . "</p>";
			?>
				
			<div class="well">
				<h4>Degree Programs</h4>
				
				<form class="form-horizontal" method="post" action="ssetup.php" onsubmit="return validateForm()">
					<div class="control-group">
						<label class="control-label" for="YearEntered">Please select the year you entered school</label>
						<div class="controls">
							<select name="yearEntered" id="yearEntered" onChange="updateDegree()">
								<option value=""> Year Entered </option>
								<?php					
									for($year = date("Y"); $year > 2005; $year--)
									echo "<option value=\"" . $year . "\">" . $year . "</option>"; 
								?>
							</select>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="Department">Please select the department your major is under</label>
						<div class="controls">
							<select name="department" id="department" class="span4" onChange="updateDegree()">
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
								

							</select>
						</div>
					</div>
					<div class="control-group">
						<div class="controls">
							<button type="submit" name="step2" class="btn btn-primary">Next</button>
						</div>
					</div>
				</form>
			</div>
			
			<footer>
				<p>© Study Planner 2013</p>
			</footer>
		</div>
	</body>
</html>