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
	
	$cgroup = s_string($_GET["cgroup"]);
	
	$sql = "SELECT course_id FROM course_group WHERE name = :cgroup";
	
	$sth = $dbh->prepare($sql);
	$sth->bindParam(":cgroup", $cgroup);
	$sth->execute();
	$rownum = $sth->rowCount();
	
	$noc = 0;
	
	if($rownum)
	{
		$row = $sth->fetch(PDO::FETCH_ASSOC);
		$courses = $row["course_id"];
		
		foreach(explode(",", $courses) as $val)
			$noc++;
		
		echo $noc;
	}
?>