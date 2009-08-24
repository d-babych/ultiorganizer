<?php
include_once 'view_ids.inc.php';
include_once 'lib/database.php';
include_once 'lib/season.functions.php';
include_once 'lib/serie.functions.php';
include_once 'builder.php';

$LAYOUT_ID = TEAMS;

//common page
pageTop();
leftMenu($LAYOUT_ID);
contentStart();
?>
<?php

echo "<h1>"._("Joukkueet")."</h1>\n";

OpenConnection();
$season="";

if(!empty($_GET["Season"]))
	$season = mysql_real_escape_string($_GET["Season"]);
	
if(is_null($season) || $season=="")
	{
	$season=CurrenSeason();
	}

$series = Series($season);
while($serie = mysql_fetch_assoc($series))
	{
	echo "<h2>".htmlentities($serie['nimi'])."</h2>";
	
	echo "
	<table border='0' cellspacing='0' cellpadding='2' width='100%'>
	<tr>
	<th>"._("Nimi")."</th><th>"._("Seura")."</th><th></th><th></th><th></th><th colspan='2'>"._("Pistep&ouml;rssi")."</th>
	</tr>";
	$teams = Teams($serie['sarja_id']);

	while($row = mysql_fetch_assoc($teams))
		{
		echo "
		<tr>
		<td><a href='teamcard.php?Team=".$row['Joukkue_ID']."'>".htmlentities($row['Nimi'])."</a></td>
		<td>".htmlentities($row['Seura'])."</td>";
		echo "<td><a href='playerlist.php?Team=".$row['Joukkue_ID']."'>"._("Pelaajalista")."</a></td>";
		echo "<td><a href='timetables.php?Team=".$row['Joukkue_ID']."'>"._("Tulevat&nbsp;pelit")."</a></td>";
		echo "<td><a href='played.php?Team=".$row['Joukkue_ID']."'>"._("Pelatut&nbsp;pelit")."</a></td>";
		echo "<td><a href='scorestatus.php?Team=".$row['Joukkue_ID']."&amp;Series=". $serie['sarja_id'] ."'>"._("Sarja")."</a></td>";
		echo "<td><a href='scorestatus.php?Team=".$row['Joukkue_ID']."'>"._("Kausi")."</a></td>";
		echo "</tr>\n";
		}
	echo "</table>\n";
	echo "<hr/>";
	
	}
CloseConnection();

echo "<p><a href='javascript:history.go(-1);'>"._("Palaa")."</a></p>";

contentEnd();
pageEnd();
?>