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
?>