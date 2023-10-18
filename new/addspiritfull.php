<?php
include_once 'lib/common.functions.php';
include_once 'lib/game.functions.php';
include_once 'lib/standings.functions.php';
include_once 'lib/pool.functions.php';
include_once 'lib/configuration.functions.php';

if (version_compare(PHP_VERSION, '5.0.0', '>')) {
//	include_once 'lib/twitter.functions.php';
}

$html = "";

$gameId = intval($_GET["Game"]);

$gameInfo = GameResult($gameId );

$spiritReceiverId = intval($_GET["SpiritReceiver"]);
if(!$spiritReceiverId)
{
	echo "no receiver";
	exit;
}
elseif ($spiritReceiverId==$gameInfo['hometeam'])
{
	$receiverTeamName = $gameInfo['hometeamname'];
	$appraiserTeamName = $gameInfo['visitorteamname'];
	$receiverIsHome = 1;
	$swipedReceiverId = $gameInfo['visitorteam'];
}
elseif ($spiritReceiverId==$gameInfo['visitorteam'])
{
	$receiverTeamName = $gameInfo['visitorteamname'];
	$appraiserTeamName = $gameInfo['hometeamname'];
	$receiverIsHome = 0;
	$swipedReceiverId = $gameInfo['hometeam'];
}
else
{
	echo "bad receiver";
	exit;
}

if(isset($_POST['save'])) {
	$rules = intval($_POST['Rules']);
	$fouls = intval($_POST['Fouls']);
	$fair = intval($_POST['Fair']);
	$attitude = intval($_POST['Attitude']);
	$compared = intval($_POST['Compared']);
	$mvp = intval($_POST['mvp']);
	$msp = intval($_POST['msp']);
	$mpp = intval($_POST['mpp']);
	
//echo $mvp.$msp.$mpp;		
	
/*	
function GameSetResult($gameId, $home, $away) {
	if (hasEditGameEventsRight($gameId)) {
		$query = sprintf("UPDATE uo_game SET homescore='%s', visitorscore='%s', isongoing='0' WHERE game_id='%s'",
			mysql_real_escape_string($home),
			mysql_real_escape_string($away),
			mysql_real_escape_string($gameId));
		$result = mysql_query($query);
		if (!$result) { die('Invalid query: ' . mysql_error()); }
		if (IsFacebookEnabled()) {
			TriggerFacebookEvent($gameId, "game", 0);
		}
		return $result;
	} else { die('Insufficient rights to edit game'); }
}
function GameSetSpiritPoints($gameId, $homepoints, $awaypoints) {
	if (hasEditGameEventsRight($gameId)) {

		$query = sprintf("
				UPDATE uo_game SET homesotg=%d, visitorsotg=%d
				WHERE game_id=%d",
				(int)$homepoints,
				(int)$awaypoints,
				(int)$gameId);
		
		return DBQuery($query);
	} else { die('Insufficient rights to edit game'); }
}
*/

function GameSetSpiritFull($gameId, $spiritReceiverId, $rules, $fouls, $fair, $attitude, $compared, $mvp, $msp, $mpp) {
	if (hasEditGameEventsRight($gameId)) {
			
		$query = sprintf("
				INSERT INTO uo_spirit (game_id, receiver, rules, fouls, fair, attitude, compared, mvp, msp, mpp)
				VALUES (%d, %d, %d, %d, %d, %d, %d, %d, %d, %d)
				ON DUPLICATE KEY UPDATE  rules=%d, fouls=%d, fair=%d, attitude=%d, compared=%d, mvp=%d, msp=%d, mpp=%d",
				
				(int)$gameId,
				(int)$spiritReceiverId,
				(int)$rules,
				(int)$fouls,
				(int)$fair,
				(int)$attitude,
				(int)$compared,
				(int)$mvp,
				(int)$msp,
				(int)$mpp,
				
				(int)$rules,
				(int)$fouls,
				(int)$fair,
				(int)$attitude,
				(int)$compared,
				(int)$mvp,
				(int)$msp,
				(int)$mpp);
				//(int)$gameId,
				//(int)$spiritReceiverId); //
		//echo $query;
		
		return DBQuery($query);
		
		$total5 = 0;
		if ($rules && $fouls && $fair && $attitude && $compared)
		{
			$total5 = $rules + $fouls + $fair + $attitude + $compared;
			if ($receiverIsHome)
				GameSetSpiritPoints($gameId, $total5-5, $gameInfo['visitorsotg']);
			else
				GameSetSpiritPoints($gameId, $gameInfo['homesotg'], $total5-5);
		} 	
		
	} else { die('Insufficient rights to edit game'); }
}
	
GameSetSpiritFull($gameId, $spiritReceiverId, $rules, $fouls, $fair, $attitude, $compared, $mvp, $msp, $mpp);

}

function GameTeamSpirit($gameId, $spiritReceiverId) {
	$query = sprintf("
		SELECT *
		FROM uo_spirit  
		WHERE game_id='%s' AND receiver='%s'",
		mysql_real_escape_string($gameId), mysql_real_escape_string($spiritReceiverId));
	$result = mysql_query($query);
	if (!$result) { die('Invalid query: ' . mysql_error()); }
	
	return mysql_fetch_assoc($result);
}

$spiritInfo = GameTeamSpirit($gameId, $spiritReceiverId);
//echo $result2['compared'];
$result =(array)$gameInfo + (array)$spiritInfo;

mobilePageTop(_("Game result"));

	?>
	<script type="text/javascript"> 

function mvpSelect(n){ 

document.forms["f2"]["mvp"][n].checked=true 

} 

function mspSelect(n){ 

document.forms["f2"]["msp"][n].checked=true 

}  

function mppSelect(n){ 

document.forms["f2"]["mpp"][n].checked=true 

} 

</script> 
 

	<?php

//$result = $result . $result2;
//$result = array_merge((array)$result, (array)$result2);
/*
$html .= "<form action='?".utf8entities($_SERVER['QUERY_STRING'])."' method='post'>\n"; 
$html .= "<table cellpadding='2'>\n";
$html .= "<tr><td>\n";
$html .= utf8entities($result['hometeamname']) ." - ". utf8entities($result['visitorteamname']);
$html .= "</td></tr><tr><td>\n";
$html .= "<input class='input' name='home' value='". intval($result['homescore']) ."' maxlength='2' size='5'/>";
$html .= " - ";
$html .= "<input class='input' name='away' value='". intval($result['visitorscore']) ."' maxlength='2' size='5'/>";
$html .= "</td></tr><tr><td>\n";
$html .= "</td></tr><tr><td>\n";
$html .= _("If game ongoing:");
$html .= "</td></tr><tr><td>\n";
$html .= "<input class='button' type='submit' name='update' value='"._("Update as current result")."'/>";
$html .= "</td></tr><tr><td>\n";
$html .= "</td></tr><tr><td>\n";
$html .= _("If game ended:");
$html .= "</td></tr><tr><td>\n";
$html .= "<input class='button' type='submit' name='save' value='"._("Save as final result")."'/>";
$html .= "</td></tr><tr><td>\n";
$html .= "<a href='?view=touch/respgames'>"._("Back to game responsibilities")."</a>";
$html .= "</td></tr>\n";

$html .= "<tr><td>\n";
$html .= "<a href='?view=touch/respgames'>"._("Back to game responsibilities")."</a>";
$html .= "</td></tr>\n";

$html .= "</table>\n";
$html .= "</form>"; 
*/


echo $html;



$html = "<hr>";
$html .= "<table cellpadding='2'>\n";
$html .= "<tr><td>\n";
$html .= "Выставляют";
$html .= "</td><td>\n";
$html .= "";
$html .= "</td><td>\n";
$html .= "Получают";
$html .= "</td><td>\n";
$html .= "Дата";
$html .= "</td></tr>\n";
$html .= "<tr><td>\n";
$html .= utf8entities($appraiserTeamName);
$html .= "</td><td>\n";
$swipedURL = str_replace ("SpiritReceiver=".$spiritReceiverId, "SpiritReceiver=".$swipedReceiverId, $_SERVER['QUERY_STRING']);
$html .= "<form action='?".utf8entities($swipedURL)."' method='post'>\n"; 
$html .= "<input class='button' type='submit' name='swipe' value='<->'/>";
$html .= "</form>\n"; 
$html .= "</td><td>\n";
$html .= utf8entities($receiverTeamName);
$html .= "</td><td>\n";
$html .= str_replace (" ","<br>", $result['time']);
$html .= "</td></tr>\n";
$html .= "</table>\n";


$html .= "<hr>";

$html .= "<form name=f2 action='?".utf8entities($_SERVER['QUERY_STRING'])."' method='post'>\n"; 
$html .= "<table cellpadding='2'>\n";
$html .= "<tr><td>..</td>\n";
for($i = 0; $i < 5; $i++) {
	$html .= "<td>".$i."</td>\n";
}
$html .= "</tr>\n";

$html .= "<tr><td>Правила</td>\n";
for($i = 0; $i < 5; $i++) {
	$html .= "<td><input type='radio' name='Rules' value='".($i+1)."'".($result['rules']==($i+1) ? " checked" : "")."></td>\n";
}
$html .= "</tr>\n";

$html .= "<tr><td>Фолы</td>\n";
for($i = 0; $i < 5; $i++) {
	$html .= "<td><input type='radio' name='Fouls' value='".($i+1)."'".($result['fouls']==($i+1) ? " checked" : "")."></td>\n";
}
$html .= "</tr>\n";

$html .= "<tr><td>Справедливость</td>\n";
for($i = 0; $i < 5; $i++) {
	$html .= "<td><input type='radio' name='Fair' value='".($i+1)."'".($result['fair']==($i+1) ? " checked" : "")."></td>\n";
}
$html .= "</tr>\n";

$html .= "<tr><td>Отношение</td>\n";
for($i = 0; $i < 5; $i++) {
	$html .= "<td><input type='radio' name='Attitude' value='".($i+1)."'".($result['attitude']==($i+1) ? " checked" : "")."></td>\n";
}
$html .= "</tr>\n";

$html .= "<tr><td>Сравнение</td>\n";
for($i = 0; $i < 5; $i++) {
	$html .= "<td><input type='radio' name='Compared' value='".($i+1)."'".($result['compared']==($i+1) ? " checked" : "")."></td>\n";
}
$html .= "</tr>\n";

$html .= "<tr><td>\n";
$html .= "Итого:   =";
$html .= "</td><td>\n";
$html .= '0';
$html .= "</td><td>\n";
$html .= '+';
$html .= "</td><td>\n";
$html .= '+';
$html .= "</td><td>\n";
$html .= '+';
$html .= "</td><td>\n";
$html .= '+';
$html .= "</td></tr>\n";

$html .= "</table>\n";




$html .= "<table cellpadding='2'>\n";
	
/*	$vgoal="";
$hgoal="";
if($team=='H'){
$hgoal="checked='checked'";
}elseif($team=='A'){
$vgoal="checked='checked'";
}*/

$html .= "<tr><td>\n";
$html .= "MVP";
$html .= "</td><td>\n";
$html .= "MSP";			
$html .= "</td><td>\n";
$html .= "MPP";
$html .= "</td></tr><tr><td>\n";
$html .= "</td></tr><tr><td>\n";$html .= "</td></tr><tr><td>\n";$html .= "</td></tr><tr><td>\n";

	$played_players = GamePlayers($gameId, $spiritReceiverId);
	
	//delete unchecked players
	$i=-1;
	while($player = mysql_fetch_assoc($played_players))	{
	$i++;
	$playerinfo = PlayerInfo($player['player_id']);
		$html .= "<tr><td>\n";
		$html .= "<input type='radio' name='mvp' value='";
		$html .= $player['player_id']."'";
		$html .= ($result['mvp']==($player['player_id']) ? " checked" : "").">";
		$html .= $player['num']." ";
		
		$html .= '<img src="images/uploads/players/'.$playerinfo['profile_id']."/thumbs/".$playerinfo['profile_image'].'" onclick="mvpSelect('.$i.')" height="70">';
		
		$html .= "<br>".$playerinfo['firstname']." ".$playerinfo['lastname'];
	$html .= "</td><td>\n";
		$html .= "<input type='radio' name='msp' value='";
		$html .= $player['player_id']."'";
		$html .= ($result['msp']==($player['player_id']) ? " checked" : "").">";
		$html .= $player['num']." ";
		
		$html .= '<img src="images/uploads/players/'.$playerinfo['profile_id']."/thumbs/".$playerinfo['profile_image'].'" onclick="mspSelect('.$i.')" height="70">';
		
		$html .= "<br>".$playerinfo['firstname']." ".$playerinfo['lastname'];
	$html .= "</td><td>\n";
		$html .= "<input type='radio' name='mpp' value='";
		$html .= $player['player_id']."'";
		$html .= ($result['mpp']==($player['player_id']) ? " checked" : "").">";
		$html .= $player['num']." ";
		
		$html .= '<img src="images/uploads/players/'.$playerinfo['profile_id']."/thumbs/".$playerinfo['profile_image'].'" onclick="mppSelect('.$i.')" height="70">';
		
		$html .= "<br>".$playerinfo['firstname']." ".$playerinfo['lastname'];
	$html .= "</td></tr>\n";
		}
	$html .= "<tr><td>.</td><td></td></tr>\n";
	$html .= "<tr><td>.</td><td></td></tr>\n";
	$html .= "<tr><td>.</td><td></td></tr>\n";
	
	
	$html .= "<tr><td>\n";
	$html .= "</td><td>\n";
	//$html .= "<input class='button' type='submit' name='forceadd' value='"."SAVE_IT"."'/>";
	$html .= "</td></tr></table>\n";
	
	
	//echo $html;



$html .= "<input class='button' type='submit' name='save' value='"._("Save as final result")."'/>";
$html .= "</form>"; 

echo $html;

pageEnd();
?>
