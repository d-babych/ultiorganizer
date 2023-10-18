<?php
include_once 'lib/common.functions.php';
$html = "";

if (isset($_POST['login'])) {
	if (!isset($_SESSION['uid']) || $_SESSION['uid'] == "anonymous") {
		$html .= "<p class='warning'>"._("Check the username and password.")."</p>\n";
	}else{
	header("location:?view=mobile/respgames");	
	}
}elseif(isset($_SESSION['uid']) && $_SESSION['uid'] != "anonymous") {
	header("location:?view=mobile/respgames");	
}

//mobilePageTop(_("Login"));


$browser = strpos($_SERVER['HTTP_USER_AGENT'],"iPhone");
  if ($browser == true){
  $browser = 'iphone';
  }

if($browser == 'iphone'){ 
  echo "<title>Короткий заголовок только для iPhone</title>";
  }else{
  echo "<title>Обычный заголовк</title>";
  }


if($browser == 'iphone'){
  echo '<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0" />';
  } 


$html .= "<form action='?".utf8entities($_SERVER['QUERY_STRING'])."' method='post'>\n"; 
$html .= "<table cellpadding='2'>\n";
$html .= "<tr><td>\n";
$html .= _("Username").":";
$html .= "</td></tr><tr><td>\n";
$html .= "<input class='input' type='text' id='myusername' name='myusername' size='15'/> ";
$html .= "</td></tr><tr><td>\n";
$html .= _("Password").":";
$html .= "</td></tr><tr><td>\n";
$html .= "<input class='input' type='password' id='mypassword' name='mypassword' size='15'/> ";
$html .= "</td></tr><tr><td>\n";
$html .= "<input class='button' type='submit' name='login' value='"._("Login")."'/>";
$html .= "</td></tr><tr><td>\n";
$html .= "<hr/>\n";
$html .= "</td></tr><tr><td>\n";
$html .= "<a href='?view=frontpage'>"._("Back to the Ultiorganizer")."</a>";
$html .= "</td></tr>\n";
$html .= "</table>\n";
$html .= "</form>"; 

echo $html;
		
echo "</body></html>"
?>
