<?php

session_start();

session_destroy();
setcookie("user_id", "", time()-3600);
setcookie("email", "", time()-3600);

//unset cookies
setcookie("email","",time()-7200);
setcookie("password","",time()-7200);

//redirect to index
$URL="index.php";
echo "<meta http-equiv='refresh' content='0;url=$URL'>";
exit();

?>
