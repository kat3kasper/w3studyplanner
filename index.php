<?php
echo 'Welcome ' .$_ENV["REDIRECT_displayName"]. ',<br/>';
$member = substr($_ENV["REDIRECT_unscoped_affiliation"],7);
echo 'You are currently logged in as ' .$member. '.'; 
phpinfo(); 
?>
