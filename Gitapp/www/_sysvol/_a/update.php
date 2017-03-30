<?php

global $config;
require "_sysvol/imbrowser.php";
if ($_REQUEST["refresh"]) @unlink("_dat/updates/updlist");
$time = @filemtime("_dat/updates/updlist");
$nupdate = @file("_dat/updates.dat");
$i=0;
foreach ($nupdate as $n=>$v)
{
$anupd[$i]=$v;
$i++;
}
if($i==0)$anupd[0]=-1;

if(!$time or time() - $time >3600) //update updates list once per hour
{
if (function_exists('curl_init')) $str= get_content("http://www.p4f.ru/mwc/updatelist.mwc");
else $str= EmulBrowser("http://www.p4f.ru/mwc/updatelist.mwc",2,2,NULL,"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)",Null,NULL);
 $handle = fopen("_dat/updates/updlist","w");
 fwrite ($handle,$str);
 fclose ($handle);
}
$file = @file("_dat/updates/updlist"); 
$count = @count($file);

ob_start();
if($count>0)
{
 if (isset($_GET["uid"]) && $_GET["uid"]<$count && !in_array($_GET["uid"],$anupd))//установка модуля
 {
  $id = checknum($_GET["uid"]);
  $array = explode("|",$file[$id]);
  if (function_exists('curl_init')) $toDo = get_content(trim($array[2]));
  else   $toDo = EmulBrowser(trim($array[2]),2,2,NULL,"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)",Null,NULL);
  eval($toDo);
 }
 else if (!isset($_GET["uid"]))
 {
  echo "<table align='center' valign=top' border='0' width='90%'>";
  for($i=0;$i<$count;$i++)
  {
   $array = explode("|",$file[$i]);
   
   if (in_array((int)$array[3],$anupd))
    $insert="<div align='center' style='font-weight:bold;color:green'>Installed</div>";
   else
    $insert="<form method='POST' action='".$config["siteaddress"]."/control.php?page=update&uid=".$i."'><input type='submit' value='Install' class='button'></form>";
   
   echo "<tr><td style='font-weight:bold;' colspan='2'>".$array[0]."</td></tr>
       <tr><td style='font-style:italic;'>".$array[1]."</td><td align='center'>".$insert."</td></tr>
	   <tr><td height='20' colspan='2'>&nbsp;</td></tr>";
  }
  echo "<tr><td colspan='2' align='center'><form method='POST' action='".$config["siteaddress"]."/control.php?page=update'><input type='submit' value='Refresh' name='refresh' class='button'></form></td></tr></table>";
 }
}
else echo "<div align='center'>No available updates.</div>";
$temp = ob_get_contents();
ob_end_clean(); 
