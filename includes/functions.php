<?php
	//Sanitize strings
	function s_string($input)
	{
		$sanitized = trim($input);
		$sanitized = filter_var($sanitized, FILTER_SANITIZE_STRING);
		$sanitized = filter_var($sanitized, FILTER_SANITIZE_MAGIC_QUOTES);
		if($sanitized !== FALSE)
			return $sanitized;
		
		echo "Invalid input!</br>\n";
	}
	
	//Sanitize integers
	function s_int($input)
	{
		$sanitized = filter_var($input, FILTER_SANITIZE_NUMBER_INT);
		if($sanitized !== FALSE)
			return $sanitized;
		
		echo "Invalid input!</br>\n";
	}
	
	//Checks if the course exists in the course table
	function course_exists($courseId)
	{
		$sql = "SELECT * FROM course WHERE CONCAT(prefix, number) = :cid";
		$sth = $dbh->prepare($sql);
		$sth->bindParam(":cid", $courseId);
		$sth->execute();
		
		$rownum = $sth->rowCount();
		
		if(!$rownum)
			return false;
		
		return true;
	}
?>