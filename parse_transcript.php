<!--
**Transcript parser
**Author: Raph
**Revision: Ucchishta
**Version: lost count
-->
<?php
if(isset($_POST['parser_submit']))
{
echo "Loading file...</br>";
$filebuf = $_POST["transcript"];
echo "File loaded. Parsing...</br>";

//$start_time = date_create();

if(preg_match("/
              Current\ Academic\ Program:\n
              \s+(.+)\n # catch degree type (a bunch of strings after whitespace)
              \s+(.+)\n # catch degree level
              \s+Major:\s+(.+) # catch major
              /x", $filebuf, $match))
{
  $fileinfo["degree_type"] = $match[1];
  $fileinfo["degree_level"] = $match[2];
  $fileinfo["major"] = $match[3];
  
  $degree_type = $fileinfo["degree_type"];
  $degree_level = $fileinfo["degree_level"];
  $major = $fileinfo["major"];
  
  //Key information such as degree type, degree level, and major are stored into variables.
  print_r("Degree type : ".$degree_type."<br>");
  print_r("Degree level : ".$degree_level."<br>");
  print_r("Major : ".$major."<br>");
  
  //trim course repeated word
  $filebuf = trim(preg_replace('/(\s+\n*\s+\COURSE REPEATED\n*)/', ' ', $filebuf));
  if(preg_match_all("/
					\-+(\d+\ \w+|\d+\ \w+\ \w+\ \d+)\-+ # catch semester
                     (?:(?:\s+\n\s+\w+\ \w+:\s+[\w\ \(\)]+\s+[\w\ ]+\s+[\S\ ]+\s+\-+)+)?  # ignore degree stuff (more often than not it is duplicated)
                     (?:(?:\s+\w+\s+\-\d+\-\w+\s+[\S\ ]+\s*.*[\S]{1,2}\s+\(?\d+.\d+\)?(?:\s+\d+.\d+)?)+)  # catch courses
                    /x", $filebuf, $match))
  {
	
    foreach ($match[0] as $index => $semester) {
		//this is to combine coursenames which takes up two lines into one line for easy parsing
		$semester = trim(preg_replace('/\s+\n+\s\s+/', '   ', $semester));
		
		//this is to fix an annoying error with a course name mistakenly considered as a grade due to its single character, e.g Senior Design I ----> I was considered a grade, so I specifically told the parser that I is not a grade and gave multiple spaces between grades and coursenames.
		$semester = trim(preg_replace('/((?:\s+(\(?[^I]\-*\+*\)?)\s+\(?\d+.\d+\)?(?:\s+\d+.\d+)?))/', '   $1', $semester));
		
		
		
      if(preg_match_all("/
                (?:(?:\s+(\w+)\s+\-(\d+)\-\w+\d*\s+([\S\ ]+)\s\s+(\(?\w\-*\+*\)?)\s+(\(?\d+.\d+\)?)(\(?\s+\d+.\d+\)?)*)) #a perfectly one-lined course info
				|
				(?:\s+(\w+)\s+\-(\d+)\-\w+\d*\s+([\S\ ]+)\s\s+(\(?\d+.\d+\)?))#a not so perfectly one line course info(current semester)
                    /x", $semester, $classes))
      {
		echo "<pre>";
		echo $semester;
		echo "</pre>";
        echo "<br>Semester: {$match[1][$index]}\n";

        foreach ($classes[1] as $ci => $class) {
		
		if($class=="")//if current semester
		{
          echo <<<COURSEINFO
               <br>Course: $class{$classes[7][$ci]}{$classes[8][$ci]}
               <br>Coursename: {$classes[9][$ci]}
			   <br>Grade: IP(in progress)
               <br>Credits: {$classes[10][$ci]}
			   <br>Credits earned: IP(in progress)<br>
COURSEINFO;
		}
		else//taken courses
		{
          echo <<<COURSEINFO
               <br>Course: $class{$classes[2][$ci]}
               <br>Coursename: {$classes[3][$ci]}
			   <br>Grade: {$classes[4][$ci]}
               <br>Credits: {$classes[5][$ci]}
			   <br>Credits earned: {$classes[6][$ci]}<br>
COURSEINFO;
		}
        }
      }
      else echo "class enumeration: no match found\n";
    }
  }
  else echo "semester enumeration: no match found\n";

//$elapsed = $start_time->diff(date_create());
//echo "\nDone processing. Elapsed time: ". $elapsed->format("%H:%I:%S") ."\n";
}
else echo "degree selection: no match found\n";
}
?>

<form action="" method="post">
<textarea name="transcript" placeholder="Paste unofficial transcript here" rows="32" cols="62"></textarea>
<br>
<input type="submit" name="parser_submit">
</form>