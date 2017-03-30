<?php
if (!defined('insite')) die("no access"); 
require 'opt.php';
global $db;
global $content;
 if (strlen($_SESSION["lang"])>1)
 {	 
  if(is_file("lang/".$_SESSION["mwclang"]."/".$_SESSION["mwclang"]."rules.txt")) $rules = file_get_contents("lang/".$_SESSION["mwclang"]."/".$_SESSION["mwclang"]."rules.txt");
  else $rules = file_get_contents("lang/".$config["def_lang"]."/".$config["def_lang"]."rules.txt");
 }
 else $rules = file_get_contents("lang/".$config["def_lang"]."/".$config["def_lang"]."rules.txt");

 $refer=$_GET["f"];
 $content->set('|rules|', $rules);
 $temp = $content->out_content("theme/".$config["theme"]."/them/reg_form.html",1);	
		
if($referal_system == 1)
{
 $content->set('|refer|', $refer);
 $temp.=$content->out_content("theme/".$config["theme"]."/them/reg_refer.html",1);	
}

$content->set('|session_name|', session_name());
$content->set('|session_id|', session_id());
$temp.=$content->out_content("theme/".$config["theme"]."/them/reg_f.html",1);

if($_REQUEST['okreg'])
{
 $Error="non";
 (strlen($_POST['captcha'])>3 || strlen($_POST['captcha'])<8) ? $captchaimg =substr($_POST['captcha'],0,7) : $Error="Captcha is incorrect!";
	
 if (strlen($_POST['ps_loginname'])<3 || strlen($_POST['ps_loginname'])>10) $Error="Login error";
 elseif($Error=="non") $loginname = validate(substr($_POST['ps_loginname'],0,10));
	
 if (strlen($_POST['ps_name'])<3 || strlen($_POST['ps_name'])>10) $Error="Name error";
 elseif($Error=="non") $name = validate(substr($_POST['ps_name'],0,10));	

	
 if (strlen($_POST['ps_password'])<3 || strlen($_POST['ps_password'])>10) $Error="Password error";
 elseif($Error=="non") $password = validate(substr($_POST['ps_password'],0,10));

	
 if (strlen($_POST['ps_repassword'])<3 || strlen($_POST['ps_password'])>10) $Error="rePassowrd error";
 elseif($Error=="non") $repassword = validate(substr($_POST['ps_repassword'],0,10));	

		
 if (strlen($_POST['ps_email'])<3 || strlen($_POST['ps_email'])>30) $Error="e-mail error";
 elseif($Error=="non") $email = checkwordm(substr($_POST['ps_email'],0,30));	
 
 if (strlen($_POST['secword'])<3 || strlen($_POST['secword'])>10) $Error="word error";
 elseif($Error=="non") $secretword = validate(substr($_POST['secword'],0,10));	

 if(isset($_SESSION['captcha_keystring']))
 {
   if($_SESSION['captcha_keystring'] != $captchaimg) $Error="Captcha is incorrect!"; 
   unset($_SESSION['captcha_keystring']);
 }else $Error="Captcha is incorrect!";
		
 if (strlen($_POST["refferal"])>2)
 {
  $refer = validate(substr($_POST["refferal"],0,10)); 
 }
 else $refer="non";
		
 $ar_adminss=explode(",",$adminss);
 if(in_array($loginname,$ar_adminss)) $temp.= "<div class='warnms' align='center'>Login already exists</div>";
 else
 {
  if ($Error=="non")
  {
   $chk_mail = $db->numrows($db->query("SELECT mail_addr FROM MEMB_INFO WHERE mail_addr='".$email."'"));
   $chk_login = $db->numrows($db->query("SELECT memb___id FROM MEMB_INFO WHERE memb___id='".$loginname."'"));
		
   if($chk_login > 0)$Error="Login already exists";
   if($chk_mail > 0)$Error="Mail already exists";
   if($password != $repassword)$Error="password & repassword not match";
					
   if ($Error=="non")
   {				  
    if($config["md5use"]=="off"){$adpwd="'".$password."'";}
    elseif($config["md5use"]=="on"){$adpwd="[dbo].[fn_md5]('".$password."','".$loginname."')";}
    else die("wrong parametr md5");

    if ($db->query("INSERT INTO MEMB_INFO (memb___id,memb__pwd,memb_name,sno__numb,bloc_code,ctl1_code, mail_addr,fpas_answ,recpwd,rdate)VALUES('".$loginname."',".$adpwd.",'".$name."','1','0','1','".$email."','".$secretword."','".$password."','".time()."')"))
    {
     if ($referal_system == 1 && $refer!="non" ) 
     {
	$row = $db->fetchrow($db->query("SELECT AccountID, cLevel, ".$config["res_colum"]." FROM Character WHERE Name='".$refer."'"));/*проверяем, что за перс пригласил*/
	if (empty($row[0])) $temp.= "<br><span style='text-align:center;color:red;font-size:12px;vertical-align:middle;font-weight:bold;'>".refsys_charmsg."</span><br>";/*если такого персонажа не существует*/	
	elseif(($row[1] >= $minlvl)||($row[2]>0))
	{
	 $query = $db->query("SELECT refer,memb_guid FROM memb_info WHERE memb___id='".$row[0]."'");
	 $accref = $row[0];
	 $row = $db->fetchrow($query);
	 $temp1 = explode(",",$row[0]);
	 $temp1 = array_count_values($temp1);
	 if ($temp1[$loginname]<1)
	 {					
	  if ($row[0] == NULL) 
	  {
	   $query = $db->query("UPDATE memb_info SET refer='".$loginname."', name_refer='".$refer."' WHERE memb___id='".$accref."' UPDATE memb_info SET ref_acc='".$row[1]."' WHERE memb___id='".$loginname."'"); 
	  }
	  else
	  {		
	   $row[0]=$row[0].",".$loginname;
	   $query = $db->query("UPDATE memb_info SET refer='".$row[0]."' WHERE memb___id='".$accref."' UPDATE memb_info SET ref_acc='".$row[1]."' WHERE memb___id='".$loginname."'"); 					
	  }
	  WriteLogs ("Referal_","$accref пригласил $loginname");
         }
	}
	else $temp.= "<br><span style='text-align:center;color:red;font-size:15px;vertical-align:middle;font-weight:bold;'>$refer ".refsys_warningchr."</span>";		
     }
     $temp.= "<script>alert('Success!\\r\\n Login: ".$loginname."\\r\\n Password: ".$password."\\r\\n e-mail: ".$email."\\r\\n Secret word: ".$secretword."');</script>";
    }
    else header("Location: ".$config["siteaddress"]."/?p=not&error=18");
   } 
   else $temp.= "<div class='warnms' align='center'>".$Error."</div>";
  }
  else  $temp.= "<div class='warnms' align='center'>".$Error."</div>"; 
  }
 }

?>