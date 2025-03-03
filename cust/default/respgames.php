<?php
include_once 'lib/common.functions.php';
include_once 'lib/team.functions.php';
include_once 'lib/season.functions.php';
include_once 'lib/series.functions.php';
$html = "";
mobilePageTop(_("Game responsibilities"));
$season = CurrentSeason();
$reservationgroup = "";
$location = "";
$showall = false;
$day="";

if(isset($_GET['rg'])){
	$reservationgroup = urldecode($_GET['rg']);
}

if(isset($_GET['loc'])){
	$location = urldecode($_GET['loc']);
}

if(isset($_GET['day'])){
	$day = urldecode($_GET['day']);
}

if(isset($_GET['all'])){
	$showall = intval($_GET['all']);
}

$respGameArray = GameResponsibilityArray($season);
$html .= "<form action='?".utf8entities($_SERVER['QUERY_STRING'])."' method='post'>\n"; 
$html .= "<table id='gamelist'>\n";


if(count($respGameArray) == 0) {
	$html .= "<tr><td colspan='3'>\n";
	$html .= "<p>"._("No game responsibilities").".</p>\n";
	$html .= "</td></tr>\n";	
} else	{
	$prevdate="";
	$prevrg = "";
	$prevloc = "";
	foreach ($respGameArray as $tournament => $resArray) {
		foreach($resArray as $resId => $gameArray) {
			foreach ($gameArray as $gameId => $game) {
				if (!is_numeric($gameId)) {
					continue;
				}
				
				if($showall){
					if(!empty($prevdate) && $prevdate != JustDate($game['time'])){
						$html .= "<tr><td colspan='3'>\n";
						$html .= "<hr/>\n";
						$html .= "</td></tr>\n";
					}
					
					$html .= gamerow($gameId, $game);
					
					$prevdate = JustDate($game['time']);
					continue;
				}
				
				if($prevrg != $game['reservationgroup']){
					$html .= "<tr><td colspan='3'>\n";
					if($reservationgroup == $game['reservationgroup']){
						$html .= "<b>".utf8entities($game['reservationgroup'])."</b>";
					}else{
						$html .= "+ <a href='?view=touch/respgames&amp;rg=".urlencode($game['reservationgroup'])."'>".utf8entities($game['reservationgroup'])."</a>";
					}
					$html .= "</td></tr>\n<tr><td colspan='3'>\n";
					$prevrg = $game['reservationgroup'];
					$html .= "</td></tr>\n";
				}

				if($reservationgroup == $game['reservationgroup']){

					$gameloc = $game['location']."#".$game['fieldname'];
					
					if($prevloc != $gameloc){
						$html .= "<tr><td colspan='3'>";
						if($location == $gameloc && $day==JustDate($game['starttime'])){
							$html .= "<b>". utf8entities($game['locationname']) . " " . _("Field") . " " . utf8entities($game['fieldname'])."</b>";
						}else{
							$html .= "+<a href='?view=touch/respgames&amp;rg=".urlencode($game['reservationgroup'])."&amp;loc=".urlencode($gameloc)."&amp;day=".urlencode(JustDate($game['starttime']))."'>";
							$html .= utf8entities($game['locationname']) . " " . _("Field") . " " . utf8entities($game['fieldname'])."</a>";
						}
						
						$html .= "</td></tr>\n";
						$html .= "<tr><td colspan='3'>";
						$prevloc = $gameloc;
						$html .= "</td></tr>\n";
					}
					
					if($location == $gameloc && $day==JustDate($game['starttime'])){
						$html .= gamerow($gameId, $game);
					}
				}

			}
		}
	}
}
$html .= "<tr><td colspan='3'>\n";
$html .= "<hr/>\n";
if($showall){
	$html .= "<a href='?view=touch/respgames'>"._("Group games")."</a>";
}else{
	$html .= "<a href='?view=touch/respgames&amp;all=1'>"._("Show all")."</a>";
}
$html .= "</td></tr>\n";
$html .= "<tr><td colspan='3'>";
$html .= "<a href='?view=touch/logout'>"._("Logout")."</a>";
$html .= "</td></tr>\n";
$html .= "<tr><td  colspan='3'>";
$html .= "<a href='?view=frontpage'>"._("Back to the Ultiorganizer")."</a>";
$html .= "</td></tr>\n";
$html .= "</table>\n";
$html .= "</form>"; 

echo $html;
		
pageEnd();

function gamerow($gameId, $game){
	// $ret = "&nbsp;&nbsp;&nbsp;&nbsp;";
	$ret .= "<tr class='gamehead'>";

	$ret .= "<td class='date'>". "<i>". str_replace (" ", "</i><br/>", DefTimeFormat($game['time'])) ."</td>\n";

	if($game['hometeam'] && $game['visitorteam']){
		$ret .= "<td class='game'>". utf8entities($game['hometeamname']) ." - ". utf8entities($game['visitorteamname']) ."</td>";
		if((intval($game['homescore'])+intval($game['visitorscore']))>0){
			$ret .=  "<td class='score'><a style='white-space: nowrap' href='?view=touch/gameplay&amp;Game=".$gameId."'><b>".intval($game['homescore']) ."</b> <i>:</i> <b>". intval($game['visitorscore'])."</b></a></td>\n";
		}else{
			$ret .= "<td class='score'>". intval($game['homescore']) ."</b> - <b>". intval($game['visitorscore']) ."</td>\n";
		}
		$ret .= "</tr>\n";
		$ret .= "<tr class='gameedit'><td colspan='3'>";
		//$ret .= "&nbsp;&nbsp;&nbsp;&nbsp;";
		$ret .=  "<a style='white-space: nowrap' href='?view=touch/addresult&amp;Game=".$gameId."'>"._("set result")."</a> | ";
		$ret .=  "<a style='white-space: nowrap' href='?view=touch/addplayerlists&amp;Game=".$gameId."&amp;Team=".$game['hometeam']."'>"._("set rosters")."</a> | ";
		$ret .=  "<a style='white-space: nowrap' href='?view=touch/addscoresheet&amp;Game=$gameId'>"._("record scores")."</a> | ";
		$ret .=  "<a style='white-space: nowrap' href='?view=touch/fullscoresheet&amp;Game=$gameId'>"._("edit scoresheet")."</a>";
		$ret .= "</td></tr>\n";
	}else{
		$ret .= utf8entities($game['phometeamname']) ." - ". utf8entities($game['pvisitorteamname']) ." ";
		$ret .= "</td></tr>\n";
	}
	
	return $ret;
}
?>
