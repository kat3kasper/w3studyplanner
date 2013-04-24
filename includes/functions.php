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
		//Setup database
		$host = DB_HOST;
		$dbname = DB_NAME;
		$user = DB_USER;
		$pass = DB_PASS;
		
		$dbh = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $user, $pass);
		$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		$sql = "SELECT * FROM course WHERE CONCAT(prefix, number) = :cid";
		$sth = $dbh->prepare($sql);
		$sth->bindParam(":cid", $courseId);
		$sth->execute();
		
		$rownum = $sth->rowCount();
		
		if(!$rownum)
			return false;
		
		return true;
	}
	
	//Wrap prereqs and coreqs in specific format
	function wrap($arr, $type)
	{
		$str = array_shift($arr);
		$wrapper = ($type == 1 ? "and" : "or");
		
		//Empty array
		if($str == NULL)
			return "";
		//Last element
		else if(count($arr) == 0)
			return formatOR($str);
		else
			return $wrapper . "(" . formatOR($str) . "," . wrap($arr, $type) . ")";
	}

	// and(or(a,b),and(c,d))
	function unwrap($ststring, $cumresult = array())
	{
		if($cumresult == null)
			$cumresult = array();

    if(preg_match('/^and\((\w+\(?\w*\,?\w*\)?)\,(\w+\(?\w*\,?\w*\)?)\)$/', $ststring, $res))
    {
		$cumresult = array_merge(unwrap($res[1], $cumresult),unwrap($res[2], $cumresult), $cumresult);
    }
    elseif(preg_match('/^or\((.*)\,(.*)\)$/', $ststring, $res))
    {
		array_push($cumresult, "{$res[1]} OR {$res[2]}");
    }
    else
		array_push($cumresult, $ststring);
	
    return $cumresult;
	}
	
	//Handle OR
	function formatOR($str)
	{
		$arr = array_map("trim", explode(" or ", $str));
		
		//Not an OR statement
		if($arr[0] == $str)
			return $str;
		//OR statement
		else
			return wrap($arr, 2);
	}
?>