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
	
	$course = s_string($_GET["course"]);
	
	$sql = "SELECT on_campus_semesters, web_campus_semesters FROM course WHERE CONCAT(prefix, number) = :course";
			
	$sth = $dbh->prepare($sql);
	$sth->bindParam(":course", $course);
	$sth->execute();
	$rownum = $sth->rowCount();
	
	echo "<option value=\"\">---</option>";
	
	if($rownum)
	{
		$row = $sth->fetch(PDO::FETCH_ASSOC);
		
		$ocs = $row["on_campus_semesters"];
		if(!empty($ocs))
		{
			$ocarr = explode(",", $ocs);
			foreach($ocarr as $val)
				echo "<option value=\"" . $val . "\">" . ucfirst($val) . "</option>";
		}
		
		$wcs = $row["web_campus_semesters"];
		if(!empty($wcs))
		{
			$wcarr = explode(",", $wcs);
			foreach($wcarr as $val)
				echo "<option value=\"wc" . $val . "\">WC " . ucfirst($val) . "</option>";
		}
	}
?>