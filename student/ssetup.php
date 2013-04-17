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
							<th>	</th>
							<th>	</th>
							<th>Fall</th>
							<th>Spring</th>
							<th>Summer 1</th>
							<th>Summer 2</th>
						</tr>
					</thead>
					<tbody>
						<tr>

<?php
	$yearCurrent = date("Y");
	for(; yearCurrent < yearGraduate; yearCurrent++)
	{
		echo "<td rowspan=\"2\">" . $yearCurrent . "-" . $yearCurrent + 1 . "</td><br/>\n";
		echo "<td>Max</td><br/>\n";
		echo "<td><br/>\n";
		echo "	<select name=\"" . $yearCurrent . "MaxCredits[]\"><br/>\n";
		
		echo "	</select><br/>\n"
		echo "</td>";
	}
?>
						
							<td rowspan="2">2011-2012</td>
							<td>Max</td>
							<td>
								<select name="credits">
									<?php 
										$i="0";
										for (; $i < 30; $i++) 
											echo "<option>$i</option>";
									?>
								</select>
							</td>
							<td>
								<select name="credits">
									<?php 
										$i="0";
										for (; $i < 30; $i++) 
											echo "<option>$i</option>";
									?>
								</select>
							</td>
							<td>
								<select name="credits">
									<?php 
										$i="0";
										for (; $i < 30; $i++) 
											echo "<option>$i</option>";
									?>
								</select>
							</td>
							<td>
								<select name="credits">
									<?php 
										$i="0";
										for (; $i < 30; $i++) 
											echo "<option>$i</option>";
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td>Min</td>
							<td>
								<select name="credits">
									<?php 
										$i="0";
										for (; $i < 30; $i++) 
											echo "<option>$i</option>";
									?>
								</select>
							</td>
							<td>
								<select name="credits">
									<?php 
										$i="0";
										for (; $i < 30; $i++) 
											echo "<option>$i</option>";
									?>
								</select>
							</td>
							<td>
								<select name="credits">
									<?php 
										$i="0";
										for (; $i < 30; $i++) 
											echo "<option>$i</option>";
									?>
								</select>
							</td>
							<td>
								<select name="credits">
									<?php 
										$i="0";
										for (; $i < 30; $i++) 
											echo "<option>$i</option>";
									?>
								</select>
							</td>
						</tr>
					</tbody>	
				</table>
				
				<div class="control-group">
					<div class="controls">
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
			
			<ul class="pager">
				<li><a href="#">Back</a></li>
				<li><a href="#">Save</a></li>
				<li><a href="#">Save and Continue</a></li>
			</ul>
			
			<footer>
				<p>© Study Planner 2013</p>
			</footer>
		</div>
	</body>
</html>