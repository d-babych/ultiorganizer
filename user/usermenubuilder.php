<?php 

function userMenu($id)
	{
	
	
	//if user owned view
	if($id >= 200 && $id<300)
		{
		$linklevel = "";
		include_once '../admin/adminmenubuilder.php';		
		}
	elseif($id >= 300 && $id<400)
		{
		$linklevel = "../user/";
		include_once 'adminmenubuilder.php';
		}
	elseif($id >= 400 && $id<500)
		{
		$linklevel = "../user/";
		include_once '../admin/adminmenubuilder.php';
		}
	else
		{
		$linklevel = "user/";	
		include_once 'admin/adminmenubuilder.php';
		}

	if(!isset($_SESSION))
		{ 
		//session_start();
		}

	//if not valid user session
	if(!isset($_SESSION['uid']))
		{
		echo "<tr><td style='height:10px'></td></tr>";
		echo "<tr><td>K&auml;ytt&auml;j&auml;:</td></tr>";
		echo "<tr><td>
				<input class='input' type='text' id='myusername' name='myusername' size='15' style='WIDTH:120px'/><br/>
			</td></tr>
			<tr><td>
				<input class='input' type='password' id='mypassword' name='mypassword' size='15' style='WIDTH:120px'/>
			</td></tr>
			<tr><td>
				<input class='button' type='submit' name='login' value='"._("Kirjaudu")."'/>
			</td></tr>\n";

		echo "<tr><td style='height:20px'></td></tr>\n";
		}
	else
		{
		//echo "<tr><td style='height:0px'></td></tr>";
		echo "<tr><td> ".$_SESSION['user'].":</td></tr>";
		echo "<tr><td style='padding-left:5px'>\n";
		echo "<a href='".$linklevel."userinfo.php'>&raquo; "._("Omat tiedot")."</a><br/>\n";
		echo "<a href='".$linklevel."teamplayers.php'>&raquo; "._("Pelaajalista")."</a><br/>\n";
		echo "<a href='".$linklevel."respgames.php'>&raquo; "._("Vastuupelit")."</a><br/>\n";
		echo "</td></tr>\n";
		
		//build admin menus if admin has logged in
		if(isset($_SESSION['admin']))
			{
			adminMenu($id);
			}
		echo "<tr><td style='height:5px'></td></tr>";
		echo "<tr><td>
				<input class='button' type='submit' name='logout' value='"._("Kirjaudu ulos")."'/>
			</td></tr>\n";
		//echo "<tr><td style='height:20px'></td></tr>";
		}
	}
?>