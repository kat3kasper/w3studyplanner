<!DOCTYPE html>
<html>
	<head>
		<title>Stevens' Study Planner &raquo; Construct Schedule</title>
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
			
			<h4>Construct Schedule</h4>
			
			<p>Here is suggested schedule. If you wish to save your schedule, please click the download button on the bottom of the page</p>
			
			<!--
			<table class="table table-bordered table-condensed well">
				<thead>
					<tr>
						<th>Fall 2012</th>
						<th>Spring 2013</th>
						<th>Summer A 2013</th>
						<th>Summer B 2013</th>
					</tr>
				</thead>
				<tbody>
					<tr> 
						<td>CS115</td>
						<td>CS284</td>
						<td>CS385</td>
						<td>	</td>
					</tr>
					<tr> 
						<td>HUM103</td>
						<td>HUM105</td>
						<td>	</td>
						<td>	</td>
					</tr>
					<tr> 
						<td>MA112</td>
						<td>CS146</td>
						<td>	</td>
						<td>	</td>
					</tr>
					<tr> 
						<td>CH114</td>
						<td>CH115</td>
						<td>	</td>
						<td>	</td>
					</tr>
				</tbody>
				<thead>
					<tr>
						<th>Fall 2013</th>
						<th>Spring 2014</th>
						<th></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<tr> 
						<td>CS347</td>
						<td>CS392</td>
						<td>	</td>
						<td>	</td>
					</tr>
					<tr> 
						<td>HHS371</td>
						<td>MA123</td>
						<td>	</td>
						<td>	</td>
					</tr>
					<tr> 
						<td>SSW564</td>
						<td>CS344</td>
						<td>	</td>
						<td>	</td>
					</tr>
					<tr> 
						<td>CS509</td>
						<td>CS442</td>
						<td>	</td>
						<td>	</td>
					</tr>
				</tbody>
			</table>
			-->
			
			<ul class="pager">
				<li><a href="#">Back</a></li>
				<li><a href="#">Download Schedule</a></li>
			</ul>		
			
			<footer>
				<p>© Study Planner 2013</p>
			</footer>
		</div>
		
		<?php require("../includes/scripts.php"); ?>
	</body>
</html>