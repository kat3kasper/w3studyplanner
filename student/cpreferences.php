<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Course Preferences</title>
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
	if(isset($_POST["step4"]))
	{
		//Setup database
		$host = DB_HOST;
		$dbname = DB_NAME;
		$user = DB_USER;
		$pass = DB_PASS;
		
		$dbh = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $user, $pass);
		$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		
	}
?>
			
			<h4>Course Preferences</h4>
			
			<form class="form-horizontal" method="post" action="cschedule.php">
				<div class="row-fluid">
					<div id="formLeft" class="span4">
						<div class="control-group">
							<div class="controls">
								Course
							</div>
						</div>
					</div>
					<div id="formLeft" class="span1">
						Completed?
					</div>
					<div id="formLeft" class="span2">
						Preferred term
					</div>
					<div id="formLeft" class="span2">
						Preferred year
					</div>
				</div>
				
				<!-- Math -->
				<div class="row-fluid">
					<div id="formLeft" class="span4">
						<div class="control-group">
							<label class="control-label">Math</label>
							<div class="controls">
								<select name="course" id="course" class="span8">
									<option value="">---</option>
									<option value="ma115">MA115</option>
									<option value="ma116">MA116</option>
									<option value="ma222">MA222</option>
									<option value="ma331">MA331</option>
								</select>
							</div>
						</div>
					</div>
					<div id="formLeft" class="span1">
						<input type="checkbox" name="completed" id="completed" value="completed" />
					</div>
					<div id="formLeft" class="span2">
						<select name="term" id="term" class="span8">
							<option value="">---</option>
							<option value="spring">Spring</option>
							<option value="summer">Summer</option>
							<option value="Fall">Fall</option>
						</select>
					</div>
					<div id="formLeft" class="span2">
						<select name="year" id="year" class="span8">
							<option value="">---</option>
<?php
	$year = 1999;
	while($year++ < date("Y"))
		echo "<option value=\"" . $year . "\">" . $year . "</option>";
?>
						</select>
					</div>
				</div>
				
				<div class="row-fluid">
					<div id="formLeft" class="span4">
						<div class="control-group">
							<div class="controls">
								<select id="course" class="span8">
									<option value="">---</option>
									<option value="ma115">MA115</option>
									<option value="ma116">MA116</option>
									<option value="ma222">MA222</option>
									<option value="ma331">MA331</option>
								</select>
							</div>
						</div>
					</div>
					<div id="formLeft" class="span1">
						<input type="checkbox" name="completed" id="completed" value="completed" />
					</div>
					<div id="formLeft" class="span2">
						<select id="term" class="span8">
							<option value="">---</option>
							<option value="spring">Spring</option>
							<option value="summer">Summer</option>
							<option value="Fall">Fall</option>
						</select>
					</div>
					<div id="formLeft" class="span2">
						<select id="year" class="span8">
							<option value="">---</option>
<?php
	$year = 1999;
	while($year++ < date("Y"))
		echo "<option value=\"" . $year . "\">" . $year . "</option>";
?>
						</select>
					</div>
				</div>
				
				<div class="row-fluid">
					<div id="formLeft" class="span4">
						<div class="control-group">
							<div class="controls">
								<select id="course" class="span8">
									<option value="">---</option>
									<option value="ma115">MA115</option>
									<option value="ma116">MA116</option>
									<option value="ma222">MA222</option>
									<option value="ma331">MA331</option>
								</select>
							</div>
						</div>
					</div>
					<div id="formLeft" class="span1">
						<input type="checkbox" name="completed" id="completed" value="completed" />
					</div>
					<div id="formLeft" class="span2">
						<select id="term" class="span8">
							<option value="">---</option>
							<option value="spring">Spring</option>
							<option value="summer">Summer</option>
							<option value="Fall">Fall</option>
						</select>
					</div>
					<div id="formLeft" class="span2">
						<select id="year" class="span8">
							<option value="">---</option>
<?php
	$year = 1999;
	while($year++ < date("Y"))
		echo "<option value=\"" . $year . "\">" . $year . "</option>";
?>
						</select>
					</div>
				</div>
				
				<div class="row-fluid">
					<div id="formLeft" class="span4">
						<div class="control-group">
							<div class="controls">
								<select id="course" class="span8">
									<option value="">---</option>
									<option value="ma115">MA115</option>
									<option value="ma116">MA116</option>
									<option value="ma222">MA222</option>
									<option value="ma331">MA331</option>
								</select>
							</div>
						</div>
					</div>
					<div id="formLeft" class="span1">
						<input type="checkbox" name="completed" id="completed" value="completed" />
					</div>
					<div id="formLeft" class="span2">
						<select id="term" class="span8">
							<option value="">---</option>
							<option value="spring">Spring</option>
							<option value="summer">Summer</option>
							<option value="Fall">Fall</option>
						</select>
					</div>
					<div id="formLeft" class="span2">
						<select id="year" class="span8">
							<option value="">---</option>
<?php
	$year = 1999;
	while($year++ < date("Y"))
		echo "<option value=\"" . $year . "\">" . $year . "</option>";
?>
						</select>
					</div>
				</div>
				
				<hr/>
				
				<!-- Humanities Group A -->
				<!--
				<div class="row-fluid">
					<div id="formLeft" class="span4">
						<div class="control-group">
							<label class="control-label">Humanities Group A</label>
							<div class="controls">
								<select id="course" class="span8">
									<option value="">---</option>
									<option value="hum103">HUM103</option>
									<option value="hum104">HUM104</option>
								</select>
							</div>
						</div>
					</div>
					<div id="formLeft" class="span1">
						<input type="checkbox" name="completed" id="completed" value="completed" />
					</div>
					<div id="formLeft" class="span2">
						<select id="term" class="span8">
							<option value="">---</option>
							<option value="spring">Spring</option>
							<option value="summer">Summer</option>
							<option value="Fall">Fall</option>
						</select>
					</div>
					<div id="formLeft" class="span2">
						<select id="year" class="span8">
							<option value="">---</option>
<?php
	$year = 1999;
	while($year++ < date("Y"))
		echo "<option value=\"" . $year . "\">" . $year . "</option>";
?>
						</select>
					</div>
				</div>
				
				<div class="row-fluid">
					<div id="formLeft" class="span4">
						<div class="control-group">
							<div class="controls">
								<select id="course" class="span8">
									<option value="">---</option>
									<option value="hum103">HUM103</option>
									<option value="hum104">HUM104</option>
								</select>
							</div>
						</div>
					</div>
					<div id="formLeft" class="span1">
						<input type="checkbox" name="completed" id="completed" value="completed" />
					</div>
					<div id="formLeft" class="span2">
						<select id="term" class="span8">
							<option value="">---</option>
							<option value="spring">Spring</option>
							<option value="summer">Summer</option>
							<option value="Fall">Fall</option>
						</select>
					</div>
					<div id="formLeft" class="span2">
						<select id="year" class="span8">
							<option value="">---</option>
<?php
	$year = 1999;
	while($year++ < date("Y"))
		echo "<option value=\"" . $year . "\">" . $year . "</option>";
?>
						</select>
					</div>
				</div>
				
				<hr/>
				-->
				<!-- Humanities Group B -->
				<!--
				<div class="row-fluid">
					<div id="formLeft" class="span4">
						<div class="control-group">
							<label class="control-label">Humanities Group B</label>
							<div class="controls">
								<select id="course" class="span8">
									<option value="">---</option>
									<option value="hum103">HUM103</option>
									<option value="hum104">HUM104</option>
								</select>
							</div>
						</div>
					</div>
					<div id="formLeft" class="span1">
						<input type="checkbox" name="completed" id="completed" value="completed" />
					</div>
					<div id="formLeft" class="span2">
						<select id="term" class="span8">
							<option value="">---</option>
							<option value="spring">Spring</option>
							<option value="summer">Summer</option>
							<option value="Fall">Fall</option>
						</select>
					</div>
					<div id="formLeft" class="span2">
						<select id="year" class="span8">
							<option value="">---</option>
<?php
	$year = 1999;
	while($year++ < date("Y"))
		echo "<option value=\"" . $year . "\">" . $year . "</option>";
?>
						</select>
					</div>
				</div>
				
				<div class="row-fluid">
					<div id="formLeft" class="span4">
						<div class="control-group">
							<div class="controls">
								<select id="course" class="span8">
									<option value="">---</option>
									<option value="hum103">HUM103</option>
									<option value="hum104">HUM104</option>
								</select>
							</div>
						</div>
					</div>
					<div id="formLeft" class="span1">
						<input type="checkbox" name="completed" id="completed" value="completed" />
					</div>
					<div id="formLeft" class="span2">
						<select id="term" class="span8">
							<option value="">---</option>
							<option value="spring">Spring</option>
							<option value="summer">Summer</option>
							<option value="Fall">Fall</option>
						</select>
					</div>
					<div id="formLeft" class="span2">
						<select id="year" class="span8">
							<option value="">---</option>
<?php
	$year = 1999;
	while($year++ < date("Y"))
		echo "<option value=\"" . $year . "\">" . $year . "</option>";
?>
						</select>
					</div>
				</div>
				
				<hr/>
				-->
				<!-- Humanities Higher Level -->
				<!--
				<div class="row-fluid">
					<div id="formLeft" class="span4">
						<div class="control-group">
							<label class="control-label">Humanities Higher Level</label>
							<div class="controls">
								<select id="course" class="span8">
									<option value="">---</option>
									<option value="hum103">HUM103</option>
									<option value="hum104">HUM104</option>
								</select>
							</div>
						</div>
					</div>
					<div id="formLeft" class="span1">
						<input type="checkbox" name="completed" id="completed" value="completed" />
					</div>
					<div id="formLeft" class="span2">
						<select id="term" class="span8">
							<option value="">---</option>
							<option value="spring">Spring</option>
							<option value="summer">Summer</option>
							<option value="Fall">Fall</option>
						</select>
					</div>
					<div id="formLeft" class="span2">
						<select id="year" class="span8">
							<option value="">---</option>
<?php
	$year = 1999;
	while($year++ < date("Y"))
		echo "<option value=\"" . $year . "\">" . $year . "</option>";
?>
						</select>
					</div>
				</div>
				
				<div class="row-fluid">
					<div id="formLeft" class="span4">
						<div class="control-group">
							<div class="controls">
								<select id="course" class="span8">
									<option value="">---</option>
									<option value="hum103">HUM103</option>
									<option value="hum104">HUM104</option>
								</select>
							</div>
						</div>
					</div>
					<div id="formLeft" class="span1">
						<input type="checkbox" name="completed" id="completed" value="completed" />
					</div>
					<div id="formLeft" class="span2">
						<select id="term" class="span8">
							<option value="">---</option>
							<option value="spring">Spring</option>
							<option value="summer">Summer</option>
							<option value="Fall">Fall</option>
						</select>
					</div>
					<div id="formLeft" class="span2">
						<select id="year" class="span8">
							<option value="">---</option>
<?php
	$year = 1999;
	while($year++ < date("Y"))
		echo "<option value=\"" . $year . "\">" . $year . "</option>";
?>
						</select>
					</div>
				</div>
				
				<div class="row-fluid">
					<div id="formLeft" class="span4">
						<div class="control-group">
							<div class="controls">
								<select id="course" class="span8">
									<option value="">---</option>
									<option value="hum103">HUM103</option>
									<option value="hum104">HUM104</option>
								</select>
							</div>
						</div>
					</div>
					<div id="formLeft" class="span1">
						<input type="checkbox" name="completed" id="completed" value="completed" />
					</div>
					<div id="formLeft" class="span2">
						<select id="term" class="span8">
							<option value="">---</option>
							<option value="spring">Spring</option>
							<option value="summer">Summer</option>
							<option value="Fall">Fall</option>
						</select>
					</div>
					<div id="formLeft" class="span2">
						<select id="year" class="span8">
							<option value="">---</option>
<?php
	$year = 1999;
	while($year++ < date("Y"))
		echo "<option value=\"" . $year . "\">" . $year . "</option>";
?>
						</select>
					</div>
				</div>
				
				<div class="row-fluid">
					<div id="formLeft" class="span4">
						<div class="control-group">
							<div class="controls">
								<select id="course" class="span8">
									<option value="">---</option>
									<option value="hum103">HUM103</option>
									<option value="hum104">HUM104</option>
								</select>
							</div>
						</div>
					</div>
					<div id="formLeft" class="span1">
						<input type="checkbox" name="completed" id="completed" value="completed" />
					</div>
					<div id="formLeft" class="span2">
						<select id="term" class="span8">
							<option value="">---</option>
							<option value="spring">Spring</option>
							<option value="summer">Summer</option>
							<option value="Fall">Fall</option>
						</select>
					</div>
					<div id="formLeft" class="span2">
						<select id="year" class="span8">
							<option value="">---</option>
<?php
	$year = 1999;
	while($year++ < date("Y"))
		echo "<option value=\"" . $year . "\">" . $year . "</option>";
?>
						</select>
					</div>
				</div>
				
				<hr/>
				-->
				
			</form>
			
			<footer>
				<p>© Study Planner 2013</p>
			</footer>
		</div>
	</body>
</html>