<?php
include_once 'lib/common.functions.php';
include_once 'lib/game.functions.php';
$html = "";

$gameId = intval($_GET["Game"]);
$game_result = GameResult($gameId);
$goals = GameGoals($gameId);
$gameevents = GameEvents($gameId);

$html = "";
$errors = false;
//$gameId = intval($_GET["Game"]);
$seasoninfo = SeasonInfo(GameSeason($gameId));
//$game_result = GameResult($gameId);
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
		//$html .= "<p class='warning'>"._("time can not be the same or earlier than the previous point")."!</p>\n";
	}
		
	if(strcasecmp($uo_goal['assist'],'xx')==0 || strcasecmp($uo_goal['assist'],'x')==0)
		$uo_goal['iscallahan'] = 1;
			
	if(!empty($team) && $team=='H'){
		$scoredteam = $game_result['hometeam'];
		$uo_goal['homescore']++;
		$uo_goal['ishomegoal']=1;
		if(!$uo_goal['iscallahan']){
			$uo_goal['assist'] = GamePlayerFromNumber($gameId, $game_result['hometeam'], $uo_goal['assist']);
			if($uo_goal['assist']==-1){
				//$html .= "<p class='warning'>"._("assisting player's number")." '".$_POST['pass']."' "._("Not on the roster")."!</p>\n";
			}
		}else{
			$uo_goal['assist']=-1;
		}
		$uo_goal['scorer'] = GamePlayerFromNumber($gameId, $game_result['hometeam'], $uo_goal['scorer']);
		if($uo_goal['scorer']==-1){
			//$html .= "<p class='warning'>"._("scorer's number")." '".$_POST['goal']."' "._("Not on the roster")."!</p>\n";
		}
				
	}elseif(!empty($team) && $team=='A'){
		$scoredteam = $game_result['visitorteam'];
		$uo_goal['visitorscore']++;
		$uo_goal['ishomegoal']=0;
			if(!$uo_goal['iscallahan'])
				{
				$uo_goal['assist'] = GamePlayerFromNumber($gameId, $game_result['visitorteam'], $uo_goal['assist']);
				if($uo_goal['assist']==-1){
					//$html .= "<p class='warning'>"._("assisting player's number")." '".$_POST['pass']."' "._("Not on the roster")."!</p>\n";
					}
				}
			else
				$uo_goal['assist']=-1;
				
			$uo_goal['scorer'] = GamePlayerFromNumber($gameId, $game_result['visitorteam'], $uo_goal['scorer']);
			if($uo_goal['scorer']==-1){
				//$html .= "<p class='warning'>"._("scorer's number")." '".$_POST['goal']."' "._("Not on the roster")."!</p>\n";
			}
	}
	if(($uo_goal['assist']!=-1 || $uo_goal['scorer']!=-1) && $uo_goal['assist']==$uo_goal['scorer']){
		//$html .= "<p class='warning'>"._("Scorer and assist have the same number")." '".$_POST['goal']."'!</p>\n";
	}
	if(empty($team)){
		//$html .=  "<p class='warning'>"._("select team scored")."!</p>\n";
	}
 	
	//if(empty($html) || isset($_POST['forceadd'])){
	if(isset($_POST['forceadd'])){
		GameAddScoreEntry($uo_goal);
		$result = GameResult($gameId );
		//save as result, if result is not already set
		if(($uo_goal['homescore'] + $uo_goal['visitorscore']) > ($result['homescore']+$result['visitorscore'])){
			LogGameUpdate($gameId,"result: $home - $away", "Mobile");
			GameUpdateResult($gameId, $uo_goal['homescore'], $uo_goal['visitorscore']);
		}
		header("location:?view=touch/newscoresheet&Game=".$gameId);
	}else{
		//$errors=true;
	mobilePageTop(_("Game play"));	
	
	echo $html;
	
	$html .= '<script language="JavaScript" type="text/javascript">';
	$html .= 'function funcname(){';
	//$html .= 'document.getElementById('."'".'test_but'."'".').onclick=function(){';
	$html .= 'alert(this);';
	//$html .= '}';
	$html .= '}';
	$html .= '</script>';
echo $html;
$html="";
	?>
	<script type="text/javascript"> 

function passSelect(n){ 

document.forms["f2"]["pass"][n].checked=true 

} 

function goalSelect(n){ 

document.forms["f2"]["goal"][n].checked=true 

} 

</script> 
 

	<?php
	$html .= "<form name=".'"'."f2".'"'." action='?".utf8entities($_SERVER['QUERY_STRING'])."' method='post'>\n"; 
	$html .= "<table cellpadding='2'>\n";
	
	$vgoal="";
$hgoal="";
if($team=='H'){
$hgoal="checked='checked'";
}elseif($team=='A'){
$vgoal="checked='checked'";
}

$html .= "<tr><td>\n";
$html .= "<input id='hteam' name='team' type='radio' $hgoal value='H' />". utf8entities($game_result['hometeamname']);
$html .= "</td><td>\n";
$html .= "<input id='ateam' name='team' type='radio' $vgoal value='A' />". utf8entities($game_result['visitorteamname']);			
$html .= "</tr></td>\n";
$html .= "</td></tr><tr><td>\n";
$html .= "</td></tr><tr><td>\n";
$html .= "</td></tr><tr><td>\n";$html .= "</td></tr><tr><td>\n";$html .= "</td></tr><tr><td>\n";

	
	
	$html .= "<tr><td>\n";
	$html .= "Закинул";
	$html .= "</td><td>\n";
	$html .= "Поймал\n";
	$html .= "</tr></td>\n";
	
	$played_players = GamePlayers($gameId, $scoredteam);
	
	//delete unchecked players
	$i=-1;
	while($player = mysql_fetch_assoc($played_players))	{
	$i++;
	$playerinfo = PlayerInfo($player['player_id']);
		$html .= "<tr><td>\n";
		$html .= "<input type='radio' name='pass' value='";
		$html .= $player['num'];
		$html .= "'>";
		$html .= $player['num']." ";
		
		$html .= '<img src="images/uploads/players/'.$playerinfo['profile_id']."/thumbs/".$playerinfo['profile_image'].'" onclick="passSelect('.$i.')" height="70">';
		
		$html .= "<br> ".$playerinfo['firstname']." ".$playerinfo['lastname'];
	$html .= "</td><td>\n";
		$html .= "<input type='radio' name='goal' value='";
		$html .= $player['num'];
		$html .= "'>";
		$html .= $player['num']." ";
		
		$html .= '<img src="images/uploads/players/'.$playerinfo['profile_id']."/thumbs/".$playerinfo['profile_image'].'" onclick="goalSelect('.$i.')" height="70">';
		
		$html .= "<br> ".$playerinfo['firstname']." ".$playerinfo['lastname'];
	$html .= "</td></tr>\n";
		}
	$html .= "<tr><td>.</td><td></td></tr>\n";
	//$html .= "<tr><td>.</td><td></td></tr>\n";
	//$html .= "<tr><td>.</td><td></td></tr>\n";
	
	
	$html .= "<tr><td>\n";
	$html .= "</td><td>\n";
	$html .= "<input class='button' type='submit' name='forceadd' value='"."SAVE_IT"."'/>";
	$html .= "</td></tr></table>\n";
	
	
	echo $html;
	pageEnd();
	die();
	echo "adddddddd";
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
	header("location:?view=touch/gameplay&Game=".$gameId);	
}





mobilePageTop(_("Game play"));

$html .= "<table cellpadding='2'>\n";
$html .= "<tr><td>\n";
	$html .=  "<a href='?view=touch/addfirstoffence&amp;Game=".$gameId."'>"._("First offence")."</a>";
	$html .= "</td></tr><tr><td>\n";
	$html .= "</td></tr><tr><td>\n";
	$html .= "</td></tr><tr><td>\n";
	$html .= "</td></tr><tr><td>\n";
	
if(mysql_num_rows($goals) <= 0){
	$html .= _("Not fed in");
	$html .= "</td></tr><tr><td>\n";
	$html .=  "<a href='?view=touch/addplayerlists&amp;Game=".$gameId."&amp;Team=".$game_result['hometeam']."'>"._("Feed in score sheet")."</a>";
	$html .=  "<a href='?view=touch/addplayerlists&amp;Game=".$gameId."&amp;Team=".$game_result['visitorteam']."'>"._("Feed in score sheet")."</a>";	
}else
{		
	
	$prevgoal = 0;
	$i = 0;
	while($goal = mysql_fetch_assoc($goals)){
	if (mysql_num_rows($goals) > $i++ + 5) continue;
	

/*		if((intval($game_result['halftime']) >= $prevgoal) &&
						(intval($game_result['halftime']) < intval($goal['time']))){
			$html .= "</td></tr><tr><td>\n";
			$html .= _("Half-time");
		}*/
		
		if(intval($goal['ishomegoal'])==1)
			$style = "class='homefontcolor'";
		else
			$style = "class='guestfontcolor'";
		
		$html .= "</td></tr><tr><td $style>\n";
		
/*		if(count($gameevents)){
			foreach($gameevents as $event){
				if((intval($event['time']) >= $prevgoal) &&
					(intval($event['time']) < intval($goal['time']))){
					if($event['type'] == "timeout")
						$gameevent = _("time-out");
					elseif($event['type'] == "turnover")
						$gameevent = _("turnover");
					elseif($event['type'] == "offence")
						$gameevent = _("offence");
					
					if(intval($event['ishome'])>0){
						$team = utf8entities($game_result['hometeamname']);
						$style = "class='homefontcolor'";
					}else{
						$team = utf8entities($game_result['visitorteamname']);
						$style = "class='guestfontcolor'";
					}
					
					$html .= SecToMin($event['time']) ." ". $team ." ". $gameevent;
					$html .= "</td></tr><tr><td  $style>\n";
				}
			}
		}*/
		
		$html .= SecToMin($goal['time']) ." ";
		$html .= $goal['homescore'] ." - ". $goal['visitorscore'] ." ";
		if(intval($goal['iscallahan']))
			$html .= _("Callahan-goal")."&nbsp;";
		else
			$html .= utf8entities($goal['assistfirstname']) ." ". utf8entities($goal['assistlastname']) ." --> ";
		$html .= utf8entities($goal['scorerfirstname']) ." ". utf8entities($goal['scorerlastname']) ."&nbsp;";
		
		$prevgoal = intval($goal['time']);
	}
	
	$html .= "</td></tr><tr><td>\n";
	$html .=  "<a href='?view=touch/deletescore&amp;Game=".$gameId."'>"._("Delete the last goal")."</a>";
	$html .= "</td></tr><tr><td> \n";
	$html .= "</td></tr><tr><td>...\n";
	$html .= "</td></tr><tr><td> \n";
	$html .= "</td></tr><tr><td> \n";
	$html .= "</td></tr><tr><td> \n";
	
	
	$html .= "<b>". utf8entities($game_result['hometeamname']);
$html .= " - ";
$html .= utf8entities($game_result['visitorteamname']);
$html .= " ". intval($game_result['homescore']) ." - ". intval($game_result['visitorscore']) ."</b>";
}
$html .= "</td></tr><tr><td>\n";
$html .= "</td></tr><tr><td><b>NEW POINT:</b>\n";
$html .= "</td></tr><tr><td>\n";



$html .= "<form action='?".utf8entities($_SERVER['QUERY_STRING'])."' method='post'>\n"; 
$html .= "<table cellpadding='2'>\n";
$html .= "<tr><td>\n";



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
/*$html .= "<input class='input' id='pass' name='pass' maxlength='2' size='3' value='".$pass."'/> ". _("Assist");
$html .= "</td></tr><tr><td>\n";
$html .= "<input class='input' id='goal' name='goal' maxlength='2' size='3' value='".$goal."'/> ". _("Goal");
$html .= "</td></tr><tr><td>\n";
$html .= "<input class='input' id='timemm' name='timemm' maxlength='3' size='3' value='".$timemm."'/>:";	
$html .= "<input class='input' id='timess' name='timess' maxlength='2' size='2' value='".$timess."'/> ". _("Time"). " " ._("min").":". _("sec");	
$html .= "</td></tr><tr><td>\n";
*/
if(!$errors){
	$html .= "</td></tr><tr><td>\n";
	$html .= "</td></tr><tr><td>\n";
	$html .= "</td></tr><tr><td>\n";
	$html .= "</td></tr><tr><td>\n";
	$html .= "</td></tr><tr><td>\n";
	$html .= "</td></tr><tr><td>\n";
	$html .= "<input class='button' type='submit' name='add' value='"."СОХРАНИТЬ ГОЛ"."'/>";
	$html .= "</td></tr><tr><td>\n";
	$html .= "</td></tr><tr><td>\n";
	$html .= "</td></tr><tr><td>\n";
	$html .= "</td></tr><tr><td>\n";
	$html .= "</td></tr><tr><td>\n";
	$html .= "</td></tr><tr><td>\n";

	$html .= "</td></tr><tr><td>\n";
	$html .= "<input class='button' type='submit' name='save' value='"."ИГРА ОКОНЧЕНА"."'/>";
	
}else{
	$html .= _("Correct the errors or save goal with errors");
	$html .= "</td></tr><tr><td>\n";
	//$html .= "<input class='button' type='submit' name='forceadd' value='"."Save_goal"."'/>";
	$html .= "</td></tr><tr><td>\n";
	$html .= "<input class='button' type='submit' name='cancel' value='"."Cancel"."'/>";
}

$html .= "</td></tr><tr><td>\n";
$html .= "</td></tr><tr><td>\n";
$html .= "</td></tr><tr><td>\n";
$html .= "</td></tr><tr><td>\n";
//$html .= "<a href='?view=touch/respgames'>"._("Back to game responsibilities")."</a>";
$html .= "</td></tr><tr><td>\n";
	//if(intval($seasoninfo['spiritpoints'])){
		$html .=  "<a href='?view=touch/addspiritfull&amp;Game=".$gameId."&amp;SpiritReceiver=".$game_result['hometeam']."'>"._("Spirit points")."</a> | ";
	//}
	$html .= "</td></tr><tr><td>\n";
$html .= "</td></tr>\n";
$html .= "</table>\n";
$html .= "</form>"; 



/*
$html .= '<div id="question_scored">';
$html .= '<p class="answer">';
$html .= '<a href="#" class="scored_home"><span class="vbutton">'.$game_result['hometeamname'].'</span></a>';
$html .= ' - - - ';
$html .= '<a href="#" class="scored_guest"><span class="vbutton">'.$game_result['visitorteamname'].'</span></a>';
$html .= '</p></div>';*/

$html .= "</td></tr><tr><td>\n";
$html .= "</td></tr><tr><td>\n";
$html .= "</td></tr><tr><td>\n";
	
	
//	$html .= _("Game official").": ". utf8entities($game_result['official']);
//}
$html .= "</td></tr><tr><td>\n";
$html .= "<a href='?view=touch/respgames'>"._("Back to game responsibilities")."</a>";
$html .= "</td></tr><tr><td>\n";
$html .=  "<a href='?view=gameplay&amp;Game=".$gameId."'>"._("Desktop score sheet")."</a>";

$html .= "</td></tr><tr><td>\n";
$html .= "</td></tr>___<tr><td>\n";
$html .= "</td></tr><tr><td>\n";
$html .=  "<a href='?view=user/addscoresheet&amp;Game=".$gameId."'><b>"."РЕДАКТИРОВАТЬ НАСТОЛЬНЫЙ ПРОТОКОЛ"."</b></a>";

$html .= "</td></tr><tr><td>\n";
$html .= "</td></tr>___<tr><td>\n";
$html .= "</td></tr><tr><td>\n";
$html .=  "<a href='?view=touch/addscoresheet&amp;Game=".$gameId."'><b>"."РЕДАКТИРОВАТЬ ТЕЛЕФОННЫЙ ПРОТОКОЛ"."</b></a>";

$html .= "</td></tr>\n";
$html .= "</table>\n";

echo $html;
		
pageEnd();
?>
