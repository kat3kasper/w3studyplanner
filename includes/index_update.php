<?php
	require("config.php");
	require("functions.php");
	
	//Setup database
	$host = DB_HOST;
	$dbname = DB_NAME;
	$user = DB_USER;
	$pass = DB_PASS;
	
	$dbh = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $user, $pass);
	$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	$year = s_int($_GET["year"]);
	$dept = s_string($_GET["dept"]);
	
	$sql = "SELECT * FROM degree WHERE year = :year AND department = :dept";
	
	$sth = $dbh->prepare($sql);
	$sth->bindParam(":year", $year);
	$sth->bindParam(":dept", $dept);
	$sth->execute();
	$rownum = $sth->rowCount();
			
	if($rownum)
	{
		$rowarray = $sth->fetchAll(PDO::FETCH_ASSOC);
		
		foreach($rowarray as $row)
		{
			$name = $row["degree_name"];
			echo "<option value=\"" . $name . "\">" .$name. "</option>\n";
		}
	}
?>