<?php
include_once 'lib/common.functions.php';
include_once 'lib/game.functions.php';
include_once 'lib/team.functions.php';
include_once 'lib/player.functions.php';
$html = "";

$gameId = intval($_GET["Game"]);
$game_result = GameResult($gameId);
	
if(isset($_POST['save'])) {
	GameSetSpiritPoints($gameId, intval($_POST['homespirit']), intval($_POST['awayspirit']));
	
	header("location:?view=new/addscoresheet&Game=".$gameId);
	}

mobilePageTop(_("Score&nbsp;sheet"));

?>
<script type="text/javascript">
<!--
function validTime(field) 
	{
	field.value=field.value.replace(/[^0-9]/g, '.');
	}

function validNumber(field) 
	{
	field.value=field.value.replace(/[^0-9]/g, '');
	}

function validNumberX(field) 
	{
	field.value=field.value.replace(/[^0-9|^xX]/g, 'X');
	}


//-->
</script>
<?php

$html .= "<form action='?".utf8entities($_SERVER['QUERY_STRING'])."' method='post'>\n"; 
$html .= "<table cellpadding='2'>\n";
$html .= "<tr><td>\n";
$html .= _("Spirit points given for")." <b>".utf8entities($game_result['hometeamname'])."</b> :";
$html .= "</td></tr><tr><td>\n";
$html .= "<input class='input' onkeyup=\"validNumber(this);\" maxlength='2' size='5' type='tel' name='homespirit' id='homespirit' value='". $game_result['homesotg'] ."'/>";
$html .= "</td></tr><tr><td>\n";
$html .= _("Spirit points given for")." <b>".utf8entities($game_result['visitorteamname'])."</b> :";
$html .= "</td></tr><tr><td>\n";
$html .= "<input class='input' onkeyup=\"validNumber(this);\" maxlength='2' size='5' type='tel' name='awayspirit' id='awayspirit' value='". $game_result['visitorsotg'] ."'/>";
$html .= "</td></tr><tr><td>\n";
$html .= "</td></tr><tr><td>\n";//
$html .= "</td></tr><tr><td>\n";//
$html .= "</td></tr><tr><td>\n";//
$html .= "</td></tr><tr><td>\n";//

$html .= "<input class='button' type='submit' name='save' value='"._("Save")."'/>";
$html .= "</td></tr><tr><td>\n";
$html .= "</td></tr><tr><td>\n";//
$html .= "</td></tr><tr><td>\n";//
$html .= "</td></tr><tr><td>\n";//
$html .= "</td></tr><tr><td>\n";//
$html .= "<a href='?view=new/addscoresheet&amp;Game=".$gameId."'>"._("Back to score sheet")."</a>";
$html .= "</td></tr>\n";
$html .= "</table>\n";
$html .= "</form>"; 

echo $html;
		
pageEnd();
?>
