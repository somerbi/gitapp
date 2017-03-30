<?php if (!defined('insite')) die("no access"); 
ob_start();

global $db;
global $content;
global $config;
if (!$_REQUEST["sendmail"])
{
 $content->set('|addingmsg|', "");
}
else
{
 $msg = trim($_POST["Newmsg"]);
 if(strlen($msg)>10)
 {
  if($db->query("INSERT INTO MWC_messages (memb___id,message,date)VALUES('".$_SESSION["user"]."','".$msg ."','".time()."')"))
    header("Location:".$config["siteaddress"]."/index.php?up=amail&act=1");
  else
    $content->set('|addingmsg|', "Error");
 }
 else
  $content->set('|addingmsg|', "");
}
if ($_GET["act"]==1) $content->set('|addingmsg|', "Done.");
$content->out_content("theme/".$config["theme"]."/them/amail.html");

$temp = ob_get_contents();
ob_end_clean();