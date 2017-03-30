<?php session_start();
define ('insite', 1);

if ($_SESSION["install"]==true)
{
 if ($_GET["cheack"] && $_GET["host"] &&  $_GET["db"] && $_GET["usr"] &&  $_GET["pwd"])
 {
  require "_sysvol/fsql.php";
  switch($_GET["cheack"])
  {
   case 1: $ctype = "ODBC";break;
   case 2: $ctype = "SQL";break;
   default: die("<div style='color:red;font-weight:bold;'>no connection type!</div>");
  }
  $db = new Connect ($ctype, $_GET["host"], $_GET["db"],$_GET["usr"], $_GET["pwd"],"SQL Server",0); 
  echo $db->check_c();
  $_SESSION["ulogin"] = $_GET["usr"];
  $_SESSION["upwd"] = $_GET["pwd"];
  $_SESSION["udb"] = $_GET["db"];
  $_SESSION["uhost"] = $_GET["host"];
  $_SESSION["utype"] = $ctype;
 }
 else echo "<div style='color:red;font-weight:bold;'>no data to connect</div>";
}
else
{
require ("_sysvol/security.php");
require "_sysvol/fsql.php";
require ('opt.php');
require "_sysvol/engine.php";
require '_sysvol/webv.php';
/**
* принцип рулетки 
*@param - возвращаемое значение в случае удачи
*@percent - "процент" удачи
*/
function answer($param,$percent=1)
{
 $rval = rand(1,10);
 if ($rval<=$percent) return $param;
 else return 0;
}
		
$db = new Connect ($config["ctype"], $config["db_host"], $config["db_name"], $config["db_user"], $config["db_upwd"],$config["odbc_driver"],$config["debug"]); 
	if(isset($_GET['acc']))
	{
	$account = validate(trim($_GET['acc']));
	$ar_adminss=explode(",",$adminss);
		if(in_array($account,$ar_adminss))
		{
			echo "<img src='imgs/x.gif' alt='already exists'/>";
		}
		else
		{		
			
			$laccount = strlen($account);
			if(($laccount>2 && $laccount<11))
			{	
				$getUser_sql = $db->numrows($db->query("SELECT memb___id FROM MEMB_INFO WHERE memb___id='".$account."'"));
				if ($getUser_sql>0){echo "<img src='imgs/x.gif' alt='already exists'/>";}else{echo "<img src='imgs/y.png' alt='already exists'/> account name \"$account\" can be used";}
			}
			else echo "<img src='imgs/x.gif' alt='already exists'/>";
		}
	}
	elseif(isset($_GET['mail']))
	{
		$email1 = checkwordm(substr($_GET['mail'],0,20));
		$lmail = strlen($email1);
		$getUser_sql = $db->numrows($db->query("SELECT mail_addr FROM MEMB_INFO WHERE mail_addr='".$email1."'"));$db->close();
		if ($getUser_sql>0 && $lmail<21){echo "<img src='imgs/x.gif' alt='already exists'/>";}else{echo "<img src='imgs/y.png' alt='all ok'/>";}	
	}
	elseif(isset($_GET['ref']))
	{
		$refferal = validate($_GET['ref']);
		$lrefferal = strlen($refferal);
				if(($lrefferal>2 && $lrefferal<11))
				{	
					$row = $db->fetchrow($db->query("SELECT cLevel, Resets FROM Character WHERE Name like '".$refferal."' and cLevel > =".$minlvl.""));$db->close();
					if(($row[0] >= $minlvl)||($row[1]>0)){echo "<img src='imgs/y.png' alt='all ok'/>";}
					else{echo "<img src='imgs/x.gif' alt='no!'/>";}
				}
				else{echo "<img src='imgs/x.gif' alt='already exists'/>";}		
	}
	elseif(isset($_GET["itmcl"]) and $_GET["id"]>=0)//строим саму добавлялку опций
	{
	  $group = checknum($_GET["itmcl"]);
	  $id = checknum($_GET["id"]);
	  
	  if ($group!="" and $id!="")
	  {
	   build_it($group,$id);
	   $_SESSION["s_group"] = $group;
	   $_SESSION["s_id"] = $id;
	  }
	  else echo "<img src='imgs/smity_main.png' border='0' align='center'>";
	}
	elseif(isset($_GET["Ilist"]) and $_GET["Ilist"]>=0)
	{
	
	  $groupnum = checknum($_GET["Ilist"]);
	   unset($_GET["Ilist"]);
	  if ($groupnum=="")$groupnum=0;
	 echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".smith_chosn_list." <select name='chzgroup' class='texbx' style='width:120px; height:20px;' onChange=\"javascript:action(this.value,'builder','itmcl=".$groupnum."&id');\"><option va;ue=''>Items</option>";
	
	 require "imgs/items.php";
	
	
	 foreach($config["s_".$groupnum] as $id=>$val)
	 {
	  echo "<option value='".$val."'>".$itembd[$groupnum][$val][0]."</option>";
	 }
	 echo "</select>";
	}
	else die('not found 404!');
	$db->close();
	// WriteLogs("ajax_","Aккаунт ".$_SESSION["user"]);
}
?>