<?php if (!defined('inpanel')) die("no access"); 
ob_start();
if (get_accesslvl()>49)
{
 global $db;
 global $content;
 global $config;
 $content->out_content("_sysvol/_a/theme/checkmail_h.html");
 if($_GET["del"])
 {
  $n = checknum(substr($_GET["del"],0,5));
  $db->query("DELETE FROM MWC_messages WHERE id='".$n."'");
  
  WriteLogs("Messages","администратор ".$_SESSION["sadmin"]." удалил сообщение");
   header("Location:".$config["siteaddress"]."/control.php?page=checkmail");
 }
 if ($_GET["mid"])
 {
   $n = checknum(substr($_GET["mid"],0,5));
   $array = $db->fetchrow("SELECT * FROM MWC_messages WHERE id='".$n."'");
   $content->set('|mnik|', $array[1]);
   $content->set('|msg|', $array[2]);
   $content->set('|id|', $array[0]);
   $content->out_content("_sysvol/_a/theme/checkmail_form.html");
 }
 else
 {
  $query = $db->query("SELECT * FROM MWC_messages");
  while($myrows = $db->fetchrow($query))
  {
   
   $content->set('|m_name|', $myrows[1]);
   $content->set('|m_date|', @date("H:i m,Y",$myrows[5]));
   $content->set('|id|', $myrows[0]);
   $content->out_content("_sysvol/_a/theme/checkmail_c.html");
  }
 }
 $content->out_content("_sysvol/_a/theme/checkmail_f.html");
  
}
else
 echo "<div style='text-align:center'>You don't have access to use this module</div>";
 $temp = ob_get_contents();
ob_end_clean(); 
