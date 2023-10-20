<?php
include_once 'lib/common.functions.php';
include_once 'lib/game.functions.php';
include_once 'lib/team.functions.php';
include_once 'lib/player.functions.php';
$html = "";
$maxtimeouts = 6;

$gameId = intval($_GET["Game"]);
$game_result = GameResult($gameId);
	
if(isset($_POST['save'])) {
	$time = "0.0";
	$time_delim = array(",", ";", ":", "#", "*");
	
	//remove all old timeouts (if any)
	GameRemoveAllTimeouts($gameId);
	
	//insert home timeouts
	$j=0;
	for($i=0;$i<$maxtimeouts; $i++){
		$time = $_POST['hto'.$i];
		$time = str_replace($time_delim,".",$time);
		
		if(!empty($time)){
			$j++;
			GameAddTimeout($gameId, $j, TimeToSec($time), 1);
		}
	}
		
	//insert away timeouts
	$j=0;
	for($i=0;$i<$maxtimeouts; $i++){
		$time = $_POST['ato'.$i];
		$time = str_replace($time_delim,".",$time);
		
		if(!empty($time)){
			$j++;
			GameAddTimeout($gameId, $j, TimeToSec($time), 0);
		}
	}
	
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
$html .= "<b>".utf8entities($game_result['hometeamname'])."</b> "._("time outs").":";
$html .= "</td></tr><tr><td>\n";
//used timeouts
$i=0;
$timeouts = GameTimeouts($gameId);
while($timeout = mysql_fetch_assoc($timeouts)){
	if (intval($timeout['ishome'])){
		$html .= "<input class='input' onkeyup=\"validTime(this);\" type='tel' size='5' maxlength='6' id='hto$i' name='hto$i' value='". SecToMin($timeout['time']) ."' /> ";
		$i++;
	}
}

//empty slots
for($i;$i<$maxtimeouts; $i++){
	//two last slot are smaller for visual reasons
	if($i>($maxtimeouts-3))
		$html .= "<input class='input' onkeyup=\"validTime(this);\" type='tel' size='1' maxlength='6' id='hto$i' name='hto$i' value=''/> ";	
	else
		$html .= "<input class='input' onkeyup=\"validTime(this);\" type='tel' size='5' maxlength='6' id='hto$i' name='hto$i' value=''/> ";		
	}
$html .= "</td></tr><tr><td>\n";
$html .= "<b>".utf8entities($game_result['visitorteamname'])."</b> "._("time outs").":";
$html .= "</td></tr><tr><td>\n";

//used timeouts
$i=0;
$timeouts = GameTimeouts($gameId);
while($timeout = mysql_fetch_assoc($timeouts)){
	if (intval(!$timeout['ishome'])){
		$html .= "<input class='input' onkeyup=\"validTime(this);\" type='tel' size='5' maxlength='6' id='ato$i' name='ato$i' value='". SecToMin($timeout['time']) ."' /> ";
		$i++;
	}
}

//empty slots
for($i;$i<$maxtimeouts; $i++){
	//two last slot are smaller for visual reasons
	if($i>($maxtimeouts-3))
		$html .= "<input class='input' onkeyup=\"validTime(this);\" type='tel' size='1' maxlength='6' id='ato$i' name='ato$i' value=''/> ";	
	else
		$html .= "<input class='input' onkeyup=\"validTime(this);\" type='tel' size='5' maxlength='6' id='ato$i' name='ato$i' value=''/> ";		
	}
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