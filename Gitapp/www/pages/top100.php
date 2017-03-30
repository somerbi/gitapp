<?php
if (!defined('insite')) die("no access"); 
global $config;
require "configs/top100_cfg.php";
global $db;		
unset($okon,$ntime);

$gettime = time();
if ($_GET["class"])
{
 $classtop = validate($_GET["class"]);
 
 switch($classtop) 
 {
  case "dw": $texq="and Class = 0 or Class = 1 or Class = 2 ";  $okon = "sm"; break;
  case "dk": $texq="and Class = 16 or Class = 17 or Class = 18 "; $okon = "bm"; break;
  case "elf":$texq="and Class = 32 or Class = 33 or Class = 34 "; $okon = "he"; break;
  case "mg": $texq="and Class = 48 or Class = 49 or Class = 50 "; $okon = "mg"; break;
  case "dl": $texq="and Class = 64 or Class = 65 or Class = 66 "; $okon = "dl"; break;
  case "sum":$texq="and Class = 80 or Class = 81 or Class = 82 "; $okon = "sum";break;
  case "rf": $texq="and Class = 96 or Class = 97 or Class = 98 "; $okon = "rf"; break;
  default: die("no such class!");
 }
}
elseif($_GET["class"]==NULL || empty($_GET["class"])){$okon="";$texq = "";}

$resultop = "SELECT TOP 100 Name, Class, cLevel, ".$top100["t100res_colum"].", Strength, Dexterity, Vitality, Energy, Leadership, AccountID,gr_res FROM Character WHERE  CtlCode != '1' and CtlCode != '17' ".$texq.$top100["t100str_sort"];

$ntime = @filemtime("_dat/cach/top_100".$okon); 
if(!$ntime or time() - $ntime >$top100["t100cach"])
{
 $hidead = explode(",",$top100["hidenicks"]);
 ob_start();
 $content = new content();
 $content->set('|siteaddress|', $config["siteaddress"]);
 $content->out_content("theme/".$config["theme"]."/them/top100_h.html");
			
 $show_chrs = explode(",",$top100["t100show_class"]);
 if (count($show_chrs)>0)
 {
  function s_cl($class)
  {
   if (strlen($class)>1)
   {
	switch($class)
	{
	 case "dw":return "DW/SM/GM"; 
	 case "dk":return "DK/BK/BM"; 
	 case "dl":return "DL/LE"; 
	 case "elf":return "Elf/ME/HE"; 
	 case "mg":return "MG/DM"; 
	 case "sum":return "S/BS/DMS"; 
	 case "rf":return "RF/FM"; 
	 case "all":return "All"; 
	 default: die("wrong paramenter");
	}
   }
  }						
	
  foreach ($show_chrs as $val) 
  {
   if ($val !="all") $lval= "&class=".$val; else $lval="";
   $content->set('|lval|', $lval);
   $content->set('|cl_n|', s_cl($val));
   $content->out_content("theme/".$config["theme"]."/them/top100_m_chrs.html");
  }
 }
 
 $content->out_content("theme/".$config["theme"]."/them/top100_h1.html");
			
 $columns = explode(",",$top100["t100_titles"]);
 if (in_array("imggres",$columns))
 $columns[array_search("imggres",$columns)] = "<img src=\"imgs/gres.gif\"  border=\"0\" />";
			
 foreach ($columns as $n=>$c)
 {
  if (($top100["t100greset"]!=1 && $n==4) or ($top100["t100top_stats"]!="on" && $n>=7 && $n<=11) or  $n==6) unset ($c);   
  
  if ($c) 
  {
	$content->set('|cap|', $c);
	$content->out_content("theme/".$config["theme"]."/them/top100_h1_c.html");
  }
 }
	
 $content->out_content("theme/".$config["theme"]."/them/top100_h1_f.html");
 $rank = 1;
 $resultop = $db->query($resultop);		
 for($i=0;$i < $db->numrows($resultop);++$i)
 {
  $rowop = $db->fetchrow($resultop);
  if (in_array($rowop[0],$hidead)==false)
  {
   $resultop2 = $db->query("Select MEMB_STAT.ConnectStat, AccountCharacter.GameIDC FROM MEMB_STAT,AccountCharacter WHERE MEMB_STAT.ConnectStat =1 and MEMB_STAT.memb___id='".$rowop[9]."' and AccountCharacter.GameIDC='".$rowop[0]."'");
   $rowop2 = $db->numrows($resultop2);				
   $rowop[1] = classname($rowop[1]);

   if ($rowop[8]==NULL || empty($rowop[8])){$rowop[8]="0";}
   if($rowop2 == 0){ $mresult = $db->fetchrow($db->query("SELECT DisConnectTM FROM MEMB_STAT WHERE memb___id='$rowop[9]'"));if($mresult[0]){$mresult[0] = "Disconnected  ".$mresult[0];} $bgcol="background:#fd7272;"; $statz = "offline";}
   if($rowop2 == 1){ $mresult = $db->fetchrow($db->query("SELECT ConnectTM FROM MEMB_STAT WHERE memb___id='$rowop[9]'"));if($mresult[0]){$mresult[0] = "Connected ".$mresult[0];} $bgcol="background:#b0fdab;"; $statz = "online";}
   $hideronot = $db->fetchrow($db->query("SELECT opt_inv FROM MEMB_INFO WHERE memb___id='$rowop[9]'"));
   
   if ($hideronot[0]==0)
   {
	/**
	* ������������ 65� ������
	*/
	$rowop[4] = stats65($rowop[4]);
	$rowop[5] = stats65($rowop[5]);
	$rowop[6] = stats65($rowop[6]);
	$rowop[7] = stats65($rowop[7]);
	$rowop[8] = stats65($rowop[8]);
   }
   else
   {
    $rowop[4] = "<img src='imgs/lock.png' border='0' alt='hide'>";
    $rowop[5] = "<img src='imgs/lock.png' border='0' alt='hide'>";
    $rowop[6] = "<img src='imgs/lock.png' border='0' alt='hide'>";
    $rowop[7] = "<img src='imgs/lock.png' border='0' alt='hide'>";
    $rowop[8] = "<img src='imgs/lock.png' border='0' alt='hide'>";
   }

   if ($rank % 2 ==0) $sh_pl = "class='lighter1'"; else $sh_pl="";	
					
   $queryguildm = $db->query("SELECT G_Name FROM GuildMember WHERE Name = '".$rowop[0]."'");
   $guildmres = $db->fetchrow($queryguildm);
   if (strlen($guildmres[0])>1)
   {
	$quildm = $db->fetchrow($db->query("SELECT G_Mark FROM guild where G_Name='".$guildmres[0]."'"));
	$mark = GuildLogo($quildm[0],$guildmres[0],10,$config["logotime"]);
	$guildmres[0] = "<a href='".$config["siteaddress"]."/?p=topguild#".$guildmres[0]."'>".$mark.$guildmres[0]."</a>";
   }
   else 
   {
    $guildmres[0]="&nbsp;";
	$mark="";
   }
   //header center
   $content->set('|style|', $sh_pl);
   $content->out_content("theme/".$config["theme"]."/them/top100_c_h.html");
   $rank1= "<a href='#".$rank."' id='tooltiper' title='".$statz." ".$mresult[0]."'>".$rank."</a>";

   $show_h[0] = $rank1;
   $show_h[1] = $rowop[0];
   $show_h[2] = $rowop[1];
   $show_h[3] = $rowop[3];
   $show_h[4] = $rowop[10];
   $show_h[5] = $rowop[2];
   $show_h[6] = $rowop[11];
   $show_h[7] = $rowop[4];
   $show_h[8] = $rowop[5];
   $show_h[9] = $rowop[6];
   $show_h[10] = $rowop[7];
   $show_h[11] = $rowop[8];
   $show_h[12] = $guildmres[0];
   //center center
   foreach ($show_h as $n=>$vl)
   {
    if ($n==0) $content->set('|style|', $bgcol); else $content->set('|style|', "");
    if (($top100["t100greset"]!=1 && $n==4) or ($top100["t100top_stats"]!="on" && $n>=7 && $n<=11)or $n==6) $vl=-1;
    if ($vl!=-1)
    {
     if ($n==1) $content->set('|data|', "<a href='".$config["siteaddress"]."/?p=search&caracter=".$vl."'>".$vl."</a>");
     else $content->set('|data|', $vl);
     $content->out_content("theme/".$config["theme"]."/them/top100_c_c.html");
    }
   }
   $rank++;
  }
  unset ($show_h);				
 }		
 $content->out_content("theme/".$config["theme"]."/them/top100_f.html");
 timing($top100["t100cach"]);
 $temp = ob_get_contents();
 write_catch("_dat/cach/top_100".$okon,$temp);ob_end_clean(); 
}
else $temp = file_get_contents( "_dat/cach/top_100".$okon);
			