<?php
include_once 'lib/common.functions.php';
include_once 'lib/game.functions.php';
include_once 'lib/team.functions.php';
include_once 'lib/player.functions.php';
include_once 'lib/standings.functions.php';
include_once 'lib/pool.functions.php';
include_once 'lib/configuration.functions.php';
include_once 'lib/localization.functions.php';

if (version_compare(PHP_VERSION, '5.0.0', '>')) {
	include_once 'lib/twitter.functions.php';
}

//start
mobilePageTop(_("Score&nbsp;sheet"));
if (version_compare(PHP_VERSION, '5.0.0', '>')) {
	include_once 'lib/twitter.functions.php';
}
$LAYOUT_ID = ADDSCORESHEET;
$title = _("Feed in score sheet");
$maxtimeouts = 6;
$maxscores = 41;
ob_start();
//common page
	//pageTopHeadOpen($title);
include_once 'script/disable_enter.js.inc';
include_once 'lib/yui.functions.php';
echo yuiLoad(array("yahoo-dom-event"));
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

function highlightError(id) 
	{
	var errorDiv = YAHOO.util.Dom.get(id);
	YAHOO.util.Dom.setStyle(errorDiv,"background-color","#FF0000");
	}
	
function updateScores(index) 
	{
	var i=0;
	var h=0;
	var a=0;
	
	for (i=0;i<<?php echo $maxscores;?>;i++)
		{
		var hradio = document.getElementById("hteam"+i);
		var aradio = document.getElementById("ateam"+i);
		
		if(hradio.checked)
			{
			h++;
			}
		else if(aradio.checked)
			{
			a++;
			}
		else
			{
			break;
			}
			
		var input = document.getElementById("sit"+i);
		input.value = h+" - "+a;
		}
	}
function eraseLast() 
	{
	var answer = confirm('<?php echo _("Are you sure you want to delete last score?");?>');
	if (answer){
	
		var i=(<?php echo $maxscores;?>-1);
		
		for (i;i>=0;i=i-1)
			{
			var hradio = document.getElementById("hteam"+i);
			var aradio = document.getElementById("ateam"+i);
			
			if(aradio.checked || hradio.checked)
				{
				var input = document.getElementById("sit"+i);
				input.value = "";
				var input = document.getElementById("pass"+i);
				input.value = "";
				var input = document.getElementById("goal"+i);
				input.value = "";
				var input = document.getElementById("time"+i);
				input.value = "";
				aradio.checked=false;
				hradio.checked=false;
				break;
				}
			}
		}
	}

var focused;
onload=function(){
var el = document.getElementById('scoresheet').elements; 
	for(var i=0;i<el.length;i++){
		el[i].onfocus=function(){focused=this};
	}
};

function chgFocus(event){
  var code=event.keyCode? event.keyCode : event.charCode; 
  //alert(code);
   switch(code){

      case 43:
		var elem = document.getElementById('scoresheet').elements;
		if(!focused){
			focused=elem[0];
		}
		for(var i = 0; i < elem.length; i++) { 
			if(elem[i] == focused){
				
				i++;
				while(elem[i].disabled || elem[i].type=='submit'|| elem[i].type=='reset'){
					i++;
				}
				elem[i].focus();
				focused=elem[i];
				break;
			}
		}
      break;
	  case 13:
		var elem = document.getElementById('scoresheet').elements;
		if(!focused){
			focused=elem[0];
		}
		if(focused.type=='radio'){
			focused.checked = true;
			updateScores(0);
		}
	  break;
	}
}
function keyfilter(e){
  var evt = window.event? event : e;
  var code=evt.keyCode? evt.keyCode : evt.charCode;
//alert(code);
if(code==43){
	return false;
}
  var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
  if ((code == 13) && ((node.type=="text")||(node.type=="checkbox")||(node.type=="radio")))  {return false;}
}
document.onkeypress = keyfilter;
//-->
</script>
<?php
$scrolling = "onkeypress='chgFocus(event);'";
	//pageTopHeadClose($title,false, $scrolling);
	//leftMenu($LAYOUT_ID);
	//contentStart();
//content

$gameId = intval($_GET["Game"]);
$season = GameSeason($gameId);
$seasoninfo = SeasonInfo($season);

$game_result = GameResult($gameId);
$homecaptain = -1;
$awaycaptain = -1;

$errIds=array();
//process itself if submit was pressed
if(!empty($_POST['save']))
	{
	LogGameUpdate($gameId, "scoresheet saved", "addscoresheet");
	$time_delim = array(",", ".", ";", ":");
	//set score sheet keeper
	//GameSetScoreSheetKeeper($gameId, $_POST['secretary']);
	
	//set spirit points
	if($seasoninfo['spiritpoints']){
		//GameSetSpiritPoints($gameId, intval($_POST['homespirit']), intval($_POST['awayspirit']));
	}
	
	//set captains
	if(intval($_POST['homecaptain'])){
		//GameSetCaptain($gameId, $game_result['hometeam'],intval($_POST['homecaptain']));
	}
	if(intval($_POST['awaycaptain'])){
		//GameSetCaptain($gameId, $game_result['visitorteam'],intval($_POST['awaycaptain']));
	}
	
	//set halftime
	$htime = $_POST['halftime'];
	$htime = str_replace($time_delim,".",$htime);
	$htime = TimeToSec($htime);
	//GameSetHalftime($gameId, $htime);
	
	if(!empty($_POST['starting']))
		{
		$starting = $_POST['starting'];
		if($starting=="H"){
			//GameSetStartingTeam($gameId,1);
		}elseif($starting=="V"){
			//GameSetStartingTeam($gameId,0);
		}
	}
			
	//remove all old timeouts (if any)
	//GameRemoveAllTimeouts($gameId);
	
	//insert home timeouts
	$j=0;
	for($i=0;$i<$maxtimeouts; $i++)
		{
		$time = $_POST['hto'.$i];
		$time = str_replace($time_delim,".",$time);
		
		if(!empty($time))
			{
			$j++;
			//GameAddTimeout($gameId, $j, TimeToSec($time), 1);
			}
		}
		
	//insert away timeouts
	$j=0;
	for($i=0;$i<$maxtimeouts; $i++)
		{
		$time = $_POST['ato'.$i];
		$time = str_replace($time_delim,".",$time);
		
		if(!empty($time))
			{
			$j++;
			//GameAddTimeout($gameId, $j, TimeToSec($time), 0);
			}
		}
	
	//remove all old scores (if any)
	GameRemoveAllScores($gameId);

	//insert scores
	$h=0;
	$a=0;
	$prevtime=0;
	for($i=0;$i<$maxscores; $i++)
		{
		$iscallahan = 0;
		$team="";
		$pass=-1;
		$goal=-1;
		$time="";
		if(!empty($_POST['team'.$i]))
			$team = $_POST['team'.$i];
		if(!empty($_POST['pass'.$i]) || $_POST['pass'.$i]=="0")
			$pass = $_POST['pass'.$i];
		if(!empty($_POST['goal'.$i])  || $_POST['goal'.$i]=="0")
			$goal = $_POST['goal'.$i];
		if(!empty($_POST['time'.$i]))
			$time = $_POST['time'.$i];
			
		$time = str_replace($time_delim,".",$time);
		$time = TimeToSec($time);
		if(!empty($team) && $time == $htime){
			//echo "<p class='warning'>"._("Point")." ",$i+1,": "._("time can not be the same as half-time ending")."!</p>";
			//$errIds[]="time$i";
		}
			
		if(!empty($team) && $time <= $prevtime){
			//echo "<p class='warning'>"._("Point")." ",$i+1,": "._("time can not be the same or earlier than the previous point")."!</p>";
			//$errIds[]="time$i";
		}
		
		if(strcasecmp($pass,'xx')==0 || strcasecmp($pass,'x')==0)
			$iscallahan = 1;
			
		$prevtime = $time;
			
		if(!empty($team) && $team=='H')
			{
			$h++;
			if(!$iscallahan)
				{
				$pass = GamePlayerFromNumber($gameId, $game_result['hometeam'], $pass);
				if($pass==-1){
					echo "<p class='warning'>"._("Point")." ",$i+1,": "._("assisting player's number")." '".$_POST['pass'.$i]."' "._("Not on the roster")."!</p>";
					$errIds[]="pass$i";
				}
				}
			else
				$pass=-1;
				
			$goal = GamePlayerFromNumber($gameId, $game_result['hometeam'], $goal);
			if($goal==-1){
				echo "<p class='warning'>"._("Point")." ",$i+1,": "._("scorer's number")." '".$_POST['goal'.$i]."' "._("Not on the roster")."!</p>";
				$errIds[]="goal$i";
			}
			
			if($pass==$goal && $pass>0){
				echo "<p class='warning'>"._("Point")." ",$i+1,": "._("Scorer and assist have the same number")." '".$_POST['goal'.$i]."'!</p>";
				$errIds[]="pass$i";
				$errIds[]="goal$i";
				}
				
			GameAddScore($gameId,$pass,$goal,$time,$i+1,$h,$a,1,$iscallahan);
			}
		elseif(!empty($team) && $team=='A')
			{
			$a++;
			if(!$iscallahan)
				{
				$pass = GamePlayerFromNumber($gameId, $game_result['visitorteam'], $pass);
				if($pass==-1){
					echo "<p class='warning'>"._("Point")." ",$i+1,": "._("assisting player's number")." '".$_POST['pass'.$i]."' "._("Not on the roster")."!</p>";
					$errIds[]="pass$i";
					}
				}
			else
				$pass=-1;
				
			$goal = GamePlayerFromNumber($gameId, $game_result['visitorteam'], $goal);
			if($goal==-1){
				echo "<p class='warning'>"._("Point")." ",$i+1,": "._("scorer's number")." '".$_POST['goal'.$i]."' "._("Not on the roster")."!</p>";
				$errIds[]="goal$i";
			}

			GameAddScore($gameId,$pass,$goal,$time,$i+1,$h,$a,0,$iscallahan);
			}
		}
	
	//protocol scores
	$protocol_homescore = 0;
	$protocol_visitorscore = 0;
	for($i=0;$i<$maxscores; $i++){
		if(!empty($_POST['team'.$i]) && $_POST['team'.$i] == "H")
			$protocol_homescore++;
		if(!empty($_POST['team'.$i]) && $_POST['team'.$i] == "A")
			$protocol_visitorscore++;
	}
		
	$isongoing = isset($_POST['isongoing'])?1:0;
	if($isongoing){
		//echo "<p>"._("Game ongoing. Current scores: $h - $a").".</p>";
		echo "<p>"._("Game ongoing. Current scores: $h - $a").". ";
		$ok=GameUpdateResult($gameId, $h, $a);
	}elseif($game_result['isongoing'] || $game_result['homescore'] != $protocol_homescore || $game_result['visitorscore'] != $protocol_visitorscore){
		$ok=GameSetResult($gameId, $h, $a);
		if($ok)	{
			//echo "<p>"._("Final result saved: $h - $a").".</p>";
			echo "<p>"._("Final result saved: $h - $a").". ";
			ResolvePoolStandings(GamePool($gameId));
			PoolResolvePlayed(GamePool($gameId));
			if(IsTwitterEnabled()){
				TweetGameResult($gameId);
			}
		}
	}
	//echo "<p>"._("Score sheet saved")." (". _("at")." ".DefTimestamp().")!</p>";
	echo _("Score sheet saved")." (". _("at")." ".DefTimestamp().")!</p>";
	//echo "<a href='?view=gameplay&amp;Game=$gameId'>"._("Game play")."</a>";
	}
$game_result = GameResult($gameId);
$place = ReservationInfo($game_result['reservation']);
$homecaptain = GameCaptain($gameId, $game_result['hometeam']);
$awaycaptain = GameCaptain($gameId, $game_result['visitorteam']);

echo "<form name='scoresheet' id='scoresheet' action='?view=new/addscoresheet&amp;Game=$gameId' method='post'>";
//echo "<table cellspacing='5' cellpadding='5' width='600px'>";
echo "<table>";

echo "<tr><td colspan='2'><h1>"._("Game score sheet")." #$gameId</h1></td></tr>";
echo "<tr><td valign='top'>\n";

/*
//team, place, time info and scoresheet keeper's name
echo "<table cellspacing='0' width='100%' border='1'>";
echo "<tr><th>"._("Home team")."</th></tr>";
echo "<tr><td>". utf8entities($game_result['hometeamname']) ."</td></tr>";
echo "<tr><th>"._("Away team")."</th></tr>";
echo "<tr><td>". utf8entities($game_result['visitorteamname']) ."</td></tr>";
echo "<tr><th>"._("Field")."</th></tr>";
echo "<tr><td>". utf8entities($place['name']) ." ". _("field")." ".utf8entities($place['fieldname']) ."</td></tr>";
echo "<tr><th>"._("Scheduled start date and time")."</th></tr>";
echo "<tr><td>". ShortDate($game_result['time']) ." ". DefHourFormat($game_result['time']) ."</td></tr>";
echo "<tr><th>"._("Game official(s)")."</th></tr>";
echo "<tr><td><input class='input' style='WIDTH: 90%' type='text' name='secretary' id='secretary' value='". $game_result['official'] ."'/></td></tr>";
echo "</table>\n";

//starting team
$hoffence="";
$voffence="";
$ishome = GameIsFirstOffenceHome($gameId);
if($ishome==1){
$hoffence="checked='checked'";
}elseif($ishome==0){
$voffence="checked='checked'";
}

echo "<table cellspacing='0' width='100%' border='1'>\n";
echo "<tr><th colspan='2'>"._("Starting offensive team")."</th></tr>";
echo "<tr><td style='width: 40px' class='center'><input id='hstart' name='starting' type='radio' $hoffence value='H' /></td>";

echo "<td>". utf8entities($game_result['hometeamname']) ."</td></tr>";
echo "<tr><td style='width: 40px' class='center'><input id='vstart' name='starting' type='radio' $voffence value='V' /></td>";
echo "<td>". utf8entities($game_result['visitorteamname']) ."</td></tr>";
echo "</table>\n";

//timeouts
echo "<table cellspacing='0' width='100%' border='1'>";
echo "<tr><th colspan='",$maxtimeouts+1,"'>"._("Time-outs")."</th></tr>\n";

//echo "<tr><th>"._("Home team")."</th>\n";
echo "<tr><th>".utf8entities($game_result['hometeamname'])."</th>\n";

//home team used timeouts
$i=0;
$timeouts = GameTimeouts($gameId);
while($timeout = mysql_fetch_assoc($timeouts))
	{
	if (intval($timeout['ishome']))
		{
		echo "<td><input class='input' onkeyup=\"validTime(this);\" type='text' size='5' maxlength='8' id='hto$i' name='hto$i' value='". SecToMin($timeout['time']) ."' /></td>\n";
		$i++;
		}
	}

//empty slots
for($i;$i<$maxtimeouts; $i++)
	{
	//two last slot are smaller for visual reasons
	if($i>($maxtimeouts-3))
		echo "<td><input class='input' onkeyup=\"validTime(this);\" type='text' size='1' maxlength='8' id='hto$i' name='hto$i' value='' /></td>\n";	
	else
		echo "<td><input class='input' onkeyup=\"validTime(this);\" type='text' size='5' maxlength='8' id='hto$i' name='hto$i' value='' /></td>\n";		
	}
echo "</tr>\n";

//echo "<tr><th>"._("Away")."</th>\n";
echo "<tr><th>".utf8entities($game_result['visitorteamname'])."</th>\n";

//away team used timeouts
$i=0;
$timeouts = GameTimeouts($gameId);
while($timeout = mysql_fetch_assoc($timeouts))
	{
	if (!intval($timeout['ishome']))
		{
		echo "<td><input class='input' onkeyup=\"validTime(this);\" type='text' size='5' maxlength='8' id='ato$i' name='ato$i' value='". SecToMin($timeout['time']) ."' /></td>\n";
		$i++;
		}
	}

//empty slots
for($i;$i<$maxtimeouts; $i++)
	{
	//two last slot are smaller for visual reasons
	if($i>($maxtimeouts-3))
		echo "<td><input class='input' onkeyup=\"validTime(this);\" type='text' size='1' maxlength='8' id='ato$i' name='ato$i' value='' /></td>\n";	
	else
		echo "<td><input class='input' onkeyup=\"validTime(this);\" type='text' size='5' maxlength='8' id='ato$i' name='ato$i' value='' /></td>\n";	
	}
	
echo "</tr>";
echo "</table>";

//halftime
echo "<table cellspacing='0' width='100%' border='1'>\n";
echo "<tr><th>"._("Half-time ended at")."</th></tr>";
echo "<tr><td><input class='input' onkeyup=\"validTime(this);\"
	maxlength='8' type='text' name='halftime' id='halftime' value='". SecToMin($game_result['halftime']) ."'/></td></tr>";
echo "</table>\n";

//spirit points
if($seasoninfo['spiritpoints']){
	echo "<table cellspacing='0' width='100%' border='1'>\n";
	echo "<tr><th colspan='2'>"._("Spirit points")."</th></tr>";
	echo "<tr><td class='center' style='width:50%;'>". utf8entities($game_result['hometeamname']) ."</td><td class='center' style='width:50%;'>". utf8entities($game_result['visitorteamname']) ."</td></tr>";
	echo "<tr><td class='center'><input class='input' maxlength='4' size='8' type='text' onkeyup=\"validTime(this);\" name='homespirit' id='homespirit' value='".intval($game_result['homesotg'])."'/></td>";
	echo "<td class='center'><input class='input' maxlength='4' size='8' type='text' onkeyup=\"validTime(this);\" name='awayspirit' id='awayspirit' value='".intval($game_result['visitorsotg'])."'/></td></tr>";
	echo "</table>\n";
}

//result		
echo "<table cellspacing='0' width='100%' border='1'>\n";
if ($game_result['isongoing']) {
	echo "<tr><th>"._("Current score")."</th></tr>";
}else{
	echo "<tr><th>"._("Final score")."</th></tr>";
}
echo "<tr><td>". $game_result['homescore'] ." - ". $game_result['visitorscore'] ."</td></tr>";
echo "</table>\n";

echo "<table cellspacing='0' width='100%' border='1'>\n";
echo "<tr><th colspan='2'>"._("Captains")."</th></tr>";
echo "<tr><td>". utf8entities($game_result['hometeamname']) ."</td>";
echo "<td><select style='width:100%' class='dropdown' name='homecaptain'>\n";
echo "<option class='dropdown' value=''></option>\n";
$team_players = GamePlayers($gameId, $game_result['hometeam']);
while($player = mysql_fetch_assoc($team_players)){
	$playerInfo = PlayerInfo($player['player_id']);
	if($homecaptain==$player['player_id'])
		echo "<option class='dropdown' selected='selected' value='".$player['player_id']."'>".utf8entities($playerInfo['firstname'] ." ". $playerInfo['lastname'])."</option>\n";
	else
		echo "<option class='dropdown' value='".$player['player_id']."'>".utf8entities($playerInfo['firstname'] ." ". $playerInfo['lastname'])."</option>\n";
}
echo  "</select></td>\n";
echo "</tr><tr>";
echo "<td>". utf8entities($game_result['visitorteamname']) ."</td>";
echo "<td><select style='width:100%' class='dropdown' name='awaycaptain'>\n";
echo "<option class='dropdown' value=''></option>\n";
$team_players = GamePlayers($gameId, $game_result['visitorteam']);
while($player = mysql_fetch_assoc($team_players)){
	$playerInfo = PlayerInfo($player['player_id']);
	if($awaycaptain==$player['player_id'])
		echo "<option class='dropdown' selected='selected' value='".$player['player_id']."'>".utf8entities($playerInfo['firstname'] ." ". $playerInfo['lastname'])."</option>\n";
	else
		echo "<option class='dropdown' value='".$player['player_id']."'>".utf8entities($playerInfo['firstname'] ." ". $playerInfo['lastname'])."</option>\n";
}
echo "</select></td>\n";
echo "</tr>";
echo "</table>\n";
*/		
//buttons
echo "<table cellspacing='0' cellpadding='6px' width='100%'>\n";

//echo "<tr><td colspan='2'></td></tr>";
	echo "<tr><td colspan='2'><a href='?view=new/addplayerlists&amp;Game=".$gameId."'>"._("Players")."</a></td></tr>";
echo "<tr><td colspan='2'></td></tr>";
	echo "<tr><td colspan='2'><a href='?view=new/addfirstoffence&amp;Game=".$gameId."'>"._("First offence")."</a></td></tr>";
	echo "<tr><td colspan='2'><a href='?view=new/addtimeouts&amp;Game=".$gameId."'>"._("Time-outs")."</a></td></tr>";
	echo "<tr><td colspan='2'><a href='?view=new/addhalftime&amp;Game=".$gameId."'>"._("Half time")."</a></td></tr>";
	echo "<tr><td colspan='2'><a href='?view=new/addofficial&amp;Game=".$gameId."'>"._("Game official")."</a></td></tr>";
	echo "<tr><td colspan='2'><a href='?view=new/addspiritpoints&amp;Game=".$gameId."'>"._("Spirit points")."</a></td></tr>";
echo "<tr><td colspan='2'></td></tr>";


echo "<tr><td colspan='2'></td></tr>";
echo "<tr><td colspan='2'>
		<a href='javascript://' onclick=\"eraseLast()\">"._("Delete the last goal")."</a></td></tr>";
echo "<tr><td colspan='2'></td></tr>";
echo "<tr><td colspan='2'></td></tr>";

echo "<tr><td colspan='2'><input class='input' type='checkbox' name='isongoing' ";
//if ($game_result['isongoing']) {
	echo "checked='checked'";
//}
echo "/> "._("Game ongoing")."</td></tr>";
echo "<tr>";
echo "<td colspan='2'><input class='button' type='submit' value='"._("Save scores")."' name='save'/></td>";
echo "</tr>";
echo "<tr><td colspan='2'></td></tr>";
//echo "<tr><td colspan='2'>
//		<a href='javascript://' onclick=\"eraseLast()\">"._("Delete the last goal")."</a></td></tr>";

//echo "<tr><td colspan='2'>
//<p>"._("Feed in the scoresheet").":</p>
//<ul>
//<li>"._("In addition of tab-key you can use + key to swap between fields and enter key to select radio button.")."</li>
//<li>"._("As a separator in the time field you can use ")." .,:; "._("characters").".</li>
//<li>"._("Give XX as the assist in Callahan goals").".</li>
//<li>"._("You can save the score sheet any time while feeding it in")."</li></ul></td></tr>";
//echo "<tr><td colspan='2'><p><a href='?view=new/respgames'>"._("Back to game responsibilities")."</a></p></td></tr>";

echo "<tr><td colspan='2'></td></tr>";
echo "<tr><td colspan='2'></td></tr>";
echo "<tr>";
echo "<td colspan='2'><input class='button' type='reset' value='"._("Reset")."' name='reset'/></td>";
echo "</tr>";

echo "<tr><td colspan='2'></td></tr>";
echo "<tr><td colspan='2'><p><a href='?view=new/respgames'>"._("Back to game responsibilities")."</a></p></td></tr>";
echo "<tr><td colspan='2'></td></tr>";

echo "</table>\n";

//scores
/*$style_left = "border-left-style:solid;border-left-width:1px;border-left-color:#000000;";
$style_left .= "border-right-style:dashed;border-right-width:1px;border-right-color:#E0E0E0;";
$style_left .= "border-top-style:solid;border-top-width:1px;border-top-color:#000000;";
$style_left .= "border-bottom-style:solid;border-bottom-width:1px;border-bottom-color:#000000;";

$style_mid = "border-top-style:solid;border-top-width:1px;border-top-color:#000000;";
$style_mid .= "border-bottom-style:solid;border-bottom-width:1px;border-bottom-color:#000000;";
$style_mid .= "border-left-style:dashed;border-left-width:1px;border-left-color:#E0E0E0;";
$style_mid .= "border-right-style:dashed;border-right-width:1px;border-right-color:#E0E0E0;";

$style_right = "border-right-style:solid;border-right-width:1px;border-right-color:#000000;";
$style_right .= "border-top-style:solid;border-top-width:1px;border-top-color:#000000;";
$style_right .= "border-bottom-style:solid;border-bottom-width:1px;border-bottom-color:#000000;";
$style_right .= "border-left-style:dashed;border-left-width:1px;border-left-color:#E0E0E0;";
*/

echo "</td><td>";
echo "<table style='border-collapse:collapse' cellspacing='0' cellpadding='2px' border='1' rules='rows'>\n";
echo "<tr><th style='background-color:#FFFFFF;border-style:none;border-width:0;border-color:#FFFFFF'></th>";
//echo "<th style='$style_left'>"._("Home team")."</th><th style='$style_mid'>"._("Away")."</th>";
echo "<th style='$style_left'>".utf8entities($game_result['hometeamname'])."</th><th style='$style_mid'>".utf8entities($game_result['visitorteamname'])."</th>";
echo "<th style='$style_mid'>"._("Assist")."</th><th style='$style_mid'>"._("Goal")."</th><th style='$style_mid'>"._("Time")."</th>";
echo "<th style='$style_right'>"._("Score")."</th></tr>\n";

$scores = GameGoals($gameId);

$i=0;
while($row = mysql_fetch_assoc($scores))
	{
	
	echo "<tr>"; 
	echo "<td class='center' style='width: 25px;color:#B0B0B0;'>",$i+1,"</td>\n";
	
	if (intval($row['ishomegoal']))
		{
		echo "<td style='width:40px;$style_left' class='center'><input onclick=\"updateScores($i);\" id='hteam$i' name='team$i' type='radio' checked='checked' value='H' /></td>";
		echo "<td style='width:40px;$style_mid' class='center'><input onclick=\"updateScores($i);\" id='ateam$i' name='team$i' type='radio' value='A' /></td>";			
		}
	else
		{
		echo "<td style='width:40px;$style_left' class='center'><input onclick=\"updateScores($i);\" id='hteam$i' name='team$i' type='radio' value='H' /></td>";
		echo "<td style='width:40px;$style_mid' class='center'><input onclick=\"updateScores($i);\" id='ateam$i' name='team$i' type='radio' checked='checked' value='A' /></td>";			
		}
	
	if (intval($row['iscallahan']))
		{
		//echo "<td class='center' style='width:50px;$style_mid'><input class='input' onkeyup=\"validNumberX(this);\" id='pass$i' name='pass$i' maxlength='2' size='3' value='XX'/></td>";
		echo "<td class='center' style='width:50px;$style_mid'><input type='tel' class='input' onkeyup=\"validNumberX(this);\" id='pass$i' name='pass$i' maxlength='2' size='3' value='XX'/></td>";
		}
	else
		{
		$n = PlayerNumber($row['assist'],$gameId);
		if($n < 0)
			$n="";
			
		//echo "<td class='center' style='width:50px;$style_mid'><input class='input' onkeyup=\"validNumberX(this);\" id='pass$i' name='pass$i' maxlength='2' size='3' value='$n'/></td>";
		echo "<td class='center' style='width:50px;$style_mid'><input  type='tel' class='input' onkeyup=\"validNumberX(this);\" id='pass$i' name='pass$i' maxlength='2' size='3' value='$n'/></td>";
		}
		
	$n = PlayerNumber($row['scorer'],$gameId);
	if($n < 0)
		$n="";
		
	//echo "<td class='center' style='width:50px;$style_mid'><input class='input' onkeyup=\"validNumber(this);\" id='goal$i' name='goal$i' maxlength='2' size='3' value='$n'/></td>";
	echo "<td class='center' style='width:50px;$style_mid'><input type='tel' class='input' onkeyup=\"validNumber(this);\" id='goal$i' name='goal$i' maxlength='2' size='3' value='$n'/></td>";
	//echo "<td style='width:60px;$style_mid'><input class='input' onkeyup=\"validTime(this);\" id='time$i' name='time$i' maxlength='8' size='8' value='". SecToMin($row['time']) ."'/></td>";
	echo "<td style='width:60px;$style_mid'><input type='tel' class='input' onkeyup=\"validTime(this);\" id='time$i' name='time$i' maxlength='6' size='6' value='". SecToMin($row['time']) ."'/></td>";
	echo "<td class='center' style='width:60px;$style_right'><input class='fakeinput center' id='sit$i' name='sit$i' size='7' disabled='disabled'
	value='". $row['homescore'] ." - ". $row['visitorscore'] ."'/></td>";
	
	echo "</tr>\n";
	$i++;	
	}

for($i;$i<$maxscores; $i++)
	{
	echo "<tr>"; 
	echo "<td class='center' style='width:25px;color:#B0B0B0;'>",$i+1,"</td>\n";
	echo "<td class='center' style='width:40px;$style_left'><input onclick=\"updateScores($i);\" id='hteam$i' name='team$i' type='radio' value='H' /></td>";
	echo "<td class='center' style='width:40px;$style_mid'><input onclick=\"updateScores($i);\" id='ateam$i' name='team$i' type='radio' value='A' /></td>";			
	//echo "<td class='center' style='width:50px;$style_mid'><input class='input' onkeyup=\"validNumberX(this);\" id='pass$i' name='pass$i' size='3' maxlength='2'/></td>";
	//echo "<td  class='center' style='width:50px;$style_mid'><input class='input' onkeyup=\"validNumber(this);\" id='goal$i' name='goal$i' size='3' maxlength='2'/></td>";
	//echo "<td style='width:60px;$style_mid'><input class='input' onkeyup=\"validTime(this);\" id='time$i' name='time$i' maxlength='8' size='8'/></td>";
	echo "<td class='center' style='width:50px;$style_mid'><input type='tel' class='input' onkeyup=\"validNumberX(this);\" id='pass$i' name='pass$i' size='3' maxlength='2'/></td>";
	echo "<td  class='center' style='width:50px;$style_mid'><input type='tel' class='input' onkeyup=\"validNumber(this);\" id='goal$i' name='goal$i' size='3' maxlength='2'/></td>";
	echo "<td style='width:60px;$style_mid'><input type='tel' class='input' onkeyup=\"validTime(this);\" id='time$i' name='time$i' maxlength='6' size='6'/></td>";
	echo "<td class='center' style='width:60px;$style_right'><input class='fakeinput center' id='sit$i' name='sit$i' size='7' disabled='disabled'/></td>";
	echo "</tr>\n";
	}
echo "</table>\n";		
echo "</td></tr></table></form>\n";		

foreach($errIds as $id){
	echo "<script type=\"text/javascript\">highlightError(\"$id\");</script>";
	}

//common end
//end

/*$html = "";
$errors = false;
$gameId = intval($_GET["Game"]);
$seasoninfo = SeasonInfo(GameSeason($gameId));
$game_result = GameResult($gameId);
$result = GameGoals($gameId);
$scores = array();
while ($row = mysql_fetch_assoc($result)) {
	$scores[] = $row;
}
$uo_goal = array(
	"game"=>$gameId,
	"num"=>0,
	"assist"=>-1,
	"scorer"=>-1,
	"time"=>"",
	"homescore"=>0,
	"visitorscore"=>0,
	"ishomegoal"=>0,
	"iscallahan"=>0);
	$timemm="";
	$timess="";
	$pass="";
	$goal="";
	$team="";

if(isset($_POST['add']) || isset($_POST['forceadd'])) {
	
	$prevtime=0;
	$time_delim = array(",", ";", ":", "#", "*");
	$timemm = "0";
	$timess = "0";
		
	if(count($scores)>0){
		$lastscore = $scores[count($scores)-1];
		$prevtime=$lastscore['time'];
		$uo_goal['num'] = $lastscore['num'] + 1;
		$uo_goal['homescore'] = $lastscore['homescore'];
		$uo_goal['visitorscore'] = $lastscore['visitorscore'];
	}
	
	if(!empty($_POST['team']))
		$team = $_POST['team'];
	if(!empty($_POST['pass'])){
		$uo_goal['assist'] = $_POST['pass'];
		$pass = $_POST['pass'];
	}
	if(!empty($_POST['goal'])){
		$uo_goal['scorer'] = $_POST['goal'];
		$goal = $_POST['goal'];
	}
	if(!empty($_POST['timemm'])){
		$timemm = intval($_POST['timemm']);
	}
	if(!empty($_POST['timess'])){
		$timess = intval($_POST['timess']);
	}
		
		
	//$time = str_replace($time_delim,".",$time);
	$uo_goal['time'] = TimeToSec($timemm.".".$timess);
		
	if($uo_goal['time'] <= $prevtime){
		$html .= "<p class='warning'>"._("time can not be the same or earlier than the previous point")."!</p>\n";
	}
		
	if(strcasecmp($uo_goal['assist'],'xx')==0 || strcasecmp($uo_goal['assist'],'x')==0)
		$uo_goal['iscallahan'] = 1;
			
	if(!empty($team) && $team=='H'){
		$uo_goal['homescore']++;
		$uo_goal['ishomegoal']=1;
		if(!$uo_goal['iscallahan']){
			$uo_goal['assist'] = GamePlayerFromNumber($gameId, $game_result['hometeam'], $uo_goal['assist']);
			if($uo_goal['assist']==-1){
				$html .= "<p class='warning'>"._("assisting player's number")." '".$_POST['pass']."' "._("Not on the roster")."!</p>\n";
			}
		}else{
			$uo_goal['assist']=-1;
		}
		$uo_goal['scorer'] = GamePlayerFromNumber($gameId, $game_result['hometeam'], $uo_goal['scorer']);
		if($uo_goal['scorer']==-1){
			$html .= "<p class='warning'>"._("scorer's number")." '".$_POST['goal']."' "._("Not on the roster")."!</p>\n";
		}
				
	}elseif(!empty($team) && $team=='A'){
		$uo_goal['visitorscore']++;
		$uo_goal['ishomegoal']=0;
			if(!$uo_goal['iscallahan'])
				{
				$uo_goal['assist'] = GamePlayerFromNumber($gameId, $game_result['visitorteam'], $uo_goal['assist']);
				if($uo_goal['assist']==-1){
					$html .= "<p class='warning'>"._("assisting player's number")." '".$_POST['pass']."' "._("Not on the roster")."!</p>\n";
					}
				}
			else
				$uo_goal['assist']=-1;
				
			$uo_goal['scorer'] = GamePlayerFromNumber($gameId, $game_result['visitorteam'], $uo_goal['scorer']);
			if($uo_goal['scorer']==-1){
				$html .= "<p class='warning'>"._("scorer's number")." '".$_POST['goal']."' "._("Not on the roster")."!</p>\n";
			}
	}
	if(($uo_goal['assist']!=-1 || $uo_goal['scorer']!=-1) && $uo_goal['assist']==$uo_goal['scorer']){
		$html .= "<p class='warning'>"._("Scorer and assist have the same number")." '".$_POST['goal']."'!</p>\n";
	}
	if(empty($team)){
		$html .=  "<p class='warning'>"._("select team scored")."!</p>\n";
	}
 	
	if(empty($html) || isset($_POST['forceadd'])){
		GameAddScoreEntry($uo_goal);
		$result = GameResult($gameId );
		//save as result, if result is not already set
		if(($uo_goal['homescore'] + $uo_goal['visitorscore']) > ($result['homescore']+$result['visitorscore'])){
			LogGameUpdate($gameId,"result: $home - $away", "Mobile");
			GameUpdateResult($gameId, $uo_goal['homescore'], $uo_goal['visitorscore']);
		}
		header("location:?view=new/addscoresheet&Game=".$gameId);
	}else{
		$errors=true;
	}
}elseif(isset($_POST['save'])) {
	$home = 0;
	$away = 0;
	if(count($scores)>0){
		$lastscore = $scores[count($scores)-1];
		
		$home = $lastscore['homescore'];
		$away = $lastscore['visitorscore'];
	}
	LogGameUpdate($gameId,"result: $home - $away", "Mobile");
	GameSetResult($gameId, $home, $away);
	ResolvePoolStandings(GamePool($gameId));
	PoolResolvePlayed(GamePool($gameId));
	if(IsTwitterEnabled()){	
		TweetGameResult($gameId);
	}
	//header("location:?view=new/gameplay&Game=".$gameId);	
	header("location:?view=new/addscoresheet&Game=".$gameId);	
}

//mobilePageTop(_("Score&nbsp;sheet"));

$html .= "<form action='?".utf8entities($_SERVER['QUERY_STRING'])."' method='post'>\n"; 
$html .= "<table cellpadding='2'>\n";
$html .= "<tr><td>\n";


//last score
if(count($scores)>0){
	$lastscore = $scores[count($scores)-1];
	$html .= "#".($lastscore['num']+1) ." "._("Score").": ".$lastscore['homescore']." - ". $lastscore['visitorscore'];
	$html .= " [<i>".SecToMin($lastscore['time']);
	if (intval($lastscore['iscallahan'])){
		$lastpass = "xx";
	}else{
		$lastpass = PlayerNumber($lastscore['assist'],$gameId);
	}
	$lastgoal = PlayerNumber($lastscore['scorer'],$gameId);
	if($lastgoal==-1){$lastgoal="";}
	if($lastpass==-1){$lastpass="";}
	$html .= " ".$lastpass." --> ".$lastgoal."</i>]";
}else{
$html .= _("Score").": 0 - 0";
}

$vgoal="";
$hgoal="";
if($team=='H'){
$hgoal="checked='checked'";
}elseif($team=='A'){
$vgoal="checked='checked'";
}

$html .= "</td></tr><tr><td>\n";
$html .= "<input id='hteam' name='team' type='radio' $hgoal value='H' />". utf8entities($game_result['hometeamname']);
$html .= "<input id='ateam' name='team' type='radio' $vgoal value='A' />". utf8entities($game_result['visitorteamname']);			
$html .= "</td></tr><tr><td>\n";
$html .= "<input class='input' id='pass' name='pass' maxlength='2' size='3' value='".$pass."'/> ". _("Assist");
$html .= "</td></tr><tr><td>\n";
$html .= "<input class='input' id='goal' name='goal' maxlength='2' size='3' value='".$goal."'/> ". _("Goal");
$html .= "</td></tr><tr><td>\n";
$html .= "<input class='input' id='timemm' name='timemm' maxlength='3' size='3' value='".$timemm."'/>:";	
$html .= "<input class='input' id='timess' name='timess' maxlength='2' size='2' value='".$timess."'/> ". _("Time"). " " ._("min").":". _("sec");	
$html .= "</td></tr><tr><td>\n";
if(!$errors){
	$html .= "<input class='button' type='submit' name='add' value='"._("Save goal")."'/>";
	$html .= "</td></tr><tr><td>\n";
	$html .=  "<a href='?view=new/addtimeouts&amp;Game=".$gameId."'>"._("Time outs")."</a> | ";
	$html .=  "<a href='?view=new/addhalftime&amp;Game=".$gameId."'>"._("Half time")."</a>";
	$html .= "</td></tr><tr><td>\n";
	$html .=  "<a href='?view=new/addfirstoffence&amp;Game=".$gameId."'>"._("First offence")."</a> | ";
	$html .=  "<a href='?view=new/addofficial&amp;Game=".$gameId."'>"._("Game official")."</a>";
	$html .= "</td></tr><tr><td>\n";
	if(IsTwitterEnabled()){
		$html .=  "<a href='?view=new/tweet&amp;Game=".$gameId."'>"._("Tweet")."</a> | ";
	}
	if(intval($seasoninfo['spiritpoints'])){
		$html .=  "<a href='?view=new/addspiritpoints&amp;Game=".$gameId."'>"._("Spirit points")."</a> | ";
	}
	$html .=  "<a href='?view=new/deletescore&amp;Game=".$gameId."'>"._("Delete the last goal")."</a>";
	$html .= "</td></tr><tr><td>\n";
	$html .= "<input class='button' type='submit' name='save' value='"._("Save as result")."'/>";
}else{
	$html .= _("Correct the errors or save goal with errors");
	$html .= "</td></tr><tr><td>\n";
	$html .= "<input class='button' type='submit' name='forceadd' value='"._("Save goal")."'/>";
	$html .= "</td></tr><tr><td>\n";
	$html .= "<input class='button' type='submit' name='cancel' value='"._("Cancel")."'/>";
}
$html .= "</td></tr><tr><td>\n";
$html .= "<a href='?view=new/respgames'>"._("Back to game responsibilities")."</a>";
$html .= "</td></tr><tr><td>\n";
$html .= "_";
$html .= "</td></tr><tr><td>\n";
$html .= "<a href='?view=new/newscoresheet&amp;Game=".$gameId."'>"."NEW SCORESHEET"."</a>";
$html .= "</td></tr>\n";
$html .= "</table>\n";
$html .= "</form>"; 
*/
//echo $html;
		
pageEnd();
?>