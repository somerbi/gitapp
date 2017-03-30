<?php if (!defined('insite')) die("no access"); 
global $db;
global $content;
global $config;
ob_start();

$accname = substr($_SESSION["user"],0,10);
/*
if ($accname)
{
 $query_a = $db->fetchrow($db->query("SELECT ref_acc FROM memb_info WHERE memb___id='".$accname."'"));
 if (!empty($query_a[0]))
 {
  $row1 = $db->fetchrow($db->query("SELECT memb___id, refer, ref_quan, ok_ref FROM memb_info WHERE memb_guid='".$query_a[0]."'"));
  if ($row1)
  {
   $query1 = $db->fetchrow($db->query("SELECT max(cLevel), max(".$config["res_colum"].") FROM Character WHERE AccountID='".$accname."' and 1=1"));
   if (($query1[0] >= $nedlvl)||($query1[1]>0))
   {	
    $query_a[1] = add_ninv($row1[1],$accname);
    if ($query_a[1]==0)
    {
     $row = $db->query("UPDATE memb_info SET ref_quan = '".($row1[2] + 1)."', ok_ref='".($row1[3] + 1)."', refer=NULL WHERE memb___id='".$row1[0]."'  UPDATE memb_info SET ref_acc=NULL, ok_inv=1 WHERE memb___id='".$accname."'");
    }
    else
    {
     $row = $db->query("UPDATE memb_info SET ref_quan = '".($row1[2] + 1)."', ok_ref='".($row1[3] + 1)."', refer='".$query_a[1]."' WHERE memb___id='".$row1[0]."' UPDATE memb_info SET ref_acc=NULL, ok_inv=1 WHERE memb___id='".$accname."'");	
    }
    WriteLogs ("Referal_","Выполнены условия! $row1[0] получает приз пригласившего, $accname - приглашенного");
   }
  }
 }
}else echo refsys_warningerr;*/

$about = $db->fetchrow($db->query("SELECT memb_name,mail_addr,fpas_answ,rdate FROM memb_info WHERE memb___id='".$accname."'"));

$content->set('|name|', $about[0]);
$content->set('|mail|', $about[1]);
$content->set('|answer|', $about[2]);
$content->set('|date|', @date("Y-m-d, G:i",$about[3]));
$content->out_content("theme/".$config["theme"]."/them/usercp_h.html");


$s4u=$db->query("SELECT Name,class,clevel FROM character WHERE AccountID='".$accname."'");
$colvo =$db->numrows($db->query("SELECT Name FROM character WHERE AccountID='".$accname."'"));
	
if ($colvo>0)
{
 $content->out_content("theme/".$config["theme"]."/them/usercp_c_h.html");
 for ($j=0;$j<$colvo;$j++)
 {
  $resultc = $db->fetchrow($s4u);
  $content->set('|classpicture|', classpicture($resultc[1]));
  $content->set('|Name|', $resultc[0]);
  $content->set('|Class|', classname($resultc[1]));
  $content->set('|Level|', $resultc[2]);
  $content->set('|getcharmenu|', getcharmenu(0,$resultc[0]));
  $content->out_content("theme/".$config["theme"]."/them/usercp_c_c.html");
 }
 $content->out_content("theme/".$config["theme"]."/them/usercp_c_f.html");		
}

/*
* меню "услуги"
*/
/*
if ($premium["on"]==1)
{
 $result = $db->fetchrow($db->query("Select opt_inv,act_time FROM memb_info WHERE memb___id='".$accname."'"));
 if($_REQUEST["opt_ok"])
 {
  $che_k = substr($_POST["opt1_sel"],0,1);
  if ($result[0]==0 && $che_k==1 && (know_kredits() >=$premium["opt_inv_pr"]))
  {
   $db->query("UPDATE MEMB_INFO SET opt_inv='".$che_k."', act_time='".time()."' Where memb___id='".$accname."'");
   $db->query("UPDATE ".$config["cr_table"]." SET ".$config["cr_column"]."=".$config["cr_column"]."-".$premium["opt_inv_pr"]." Where ".$config["cr_acc"]."='".$accname."'");
   WriteLogs ("Options_",$accname." скрыл инвентарь");
   $result[0] = $che_k;
  }
  elseif ($result[0]==1 && $che_k==0)
  {
   $db->query("UPDATE MEMB_INFO SET opt_inv='0', act_time='0' Where memb___id='".$accname."'");
   WriteLogs ("Options_",$accname." включил  инвентарь");
   $result[0]=0;
  }
 }
 $view=build_box("opt1_sel",array(0=>"OFF",1=>"On"),"texbx",$result[0])." ".usercp_o_des." ".$premium["opt_inv_pr"];			

 if(know_kredits() >=$premium["opt_inv_pr"])
 {
  $viewB="<center><input type='submit' value='Ok' name='opt_ok' class='t-button'></center>";
 }
 //options header
 $content->set('|usercp_o_head|', usercp_o_head);
 $content->out_content("theme/".$config["theme"]."/them/usercp_opt_h.html");
 
 //center
 $content->set('|date|', $view);
 $content->out_content("theme/".$config["theme"]."/them/usercp_opt_c.html");
 
 $content->set('|date|', $viewB);
 $content->out_content("theme/".$config["theme"]."/them/usercp_opt_c.html");
 //footer
 $content->out_content("theme/".$config["theme"]."/them/usercp_opt_f.html");
}*/
/*ref*/	
/*
if($referal_system == 1)
{
 if(!$accname)echo "<div class='warnms'>".refsys_warningerr."</div>";
 else
 {
  $content->set('|refsys_refheader|', refsys_refheader);
  $content->out_content("theme/".$config["theme"]."/them/usercp_ref_h.html");
  
  $query_a = $db->fetchrow($db->query("SELECT ok_inv,ok_ref,ref_quan,ref_acc, memb_guid FROM memb_info WHERE memb___id='".$accname."'"));
  $row = $db->numrows($db->query("SELECT ref_acc FROM memb_info WHERE ref_acc='".$query_a[4]."'"));
  if ($row==0) $row=" none";
  if (($query_a[0]>0)||($query_a[1]>0))
  {
   $temparr=show_chars($accname);
	
   if (!$_REQUEST["okbtn"])
   {
    $content->set('|refsys_refaccept|', refsys_refaccept);
    $content->out_content("theme/".$config["theme"]."/them/usercp_ref_c.html");
    
    for($i=0; $i < count($temparr); $i++)
    {
     $content->set('|name|', $temparr[$i]);
     $content->out_content("theme/".$config["theme"]."/them/usercp_ref_list.html");
    }
    $content->set('|refsys_refbtok|', refsys_refbtok);
    $content->out_content("theme/".$config["theme"]."/them/usercp_ref_f.html");
   }
   else
   {
    if (chck_online($accname) == 0)
    {
      $tempChName= validate(substr($_POST["cahrs"],0,10));
      if ($query_a[0]==1)
      {
	$bonuspoins = ($bonuspoins/2);
	switch($typeprice)
	{
	 case 1:
	 if($db->query("UPDATE memb_info SET bankZ=bankZ+".$zeninv." WHERE memb___id='".$accname."'"))
	   $db->query("UPDATE memb_info SET ok_inv=NULL WHERE memb___id='".$accname."'");
	 break;
	 case 3:
	 if($db->query("UPDATE memb_info SET bankZ=bankZ+".$zeninv." WHERE memb___id='".$accname."'"))
	 {
          $db->query("UPDATE ".$config["cr_table"]." SET ".$config["cr_column"]."=".$config["cr_column"]."+".round($bonusref/2)." WHERE ".$config["cr_acc"]."='".$accname."'");
	  $db->query("UPDATE memb_info SET ok_inv=NULL WHERE memb___id='".$accname."'");
	 }
	 break;
	 case 4:
	 if($db->query("UPDATE memb_info SET bankZ=bankZ+".$zeninv." WHERE memb___id='".$accname."'"))
	 {
	  $db->query("UPDATE character SET LevelUpPoint=LevelUpPoint+".$bonuspoins." WHERE Name='".$tempChName."'");
	  $db->query("UPDATE memb_info SET ok_inv=NULL WHERE memb___id='".$accname."'");
	 }
	 break;
         case 5: 
	 if($db->query("UPDATE memb_info SET bankZ=bankZ+".$zeninv." WHERE memb___id='".$accname."'"))
	 {
	  $db->query(" UPDATE character SET LevelUpPoint=LevelUpPoint+".$bonuspoins." WHERE Name='".$tempChName."'");
	  $db->query(" UPDATE memb_info SET ok_inv=NULL WHERE memb___id='".$accname."'");
	 }
	 break;
	 default : echo refsys_referrmsgver;
	}
	WriteLogs ("Referal_","выдан приз приглашенному, AccountID='".$accname."");
     }
     if ($query_a[1]>0)	
     {
      if ($query_a[1]>1)
      {
       switch($typeprice)
       {
	case 1:
	 if($db->query("UPDATE memb_info SET bankZ=bankZ+".$zenref." WHERE memb___id='".$accname."'")) $db->query("UPDATE memb_info SET ok_ref=ok_ref-1 WHERE memb___id='".$accname."'");break;
	case 3:
	 if($db->query("UPDATE memb_info SET bankZ=bankZ+".$zenref." WHERE memb___id='".$accname."'"))
	 {
	  $db->query("UPDATE ".$config["cr_table"]." SET ".$config["cr_column"]."=".$config["cr_column"]."+".$bonusref." WHERE ".$config["cr_acc"]."='".$accname."'");
	  $db->query("UPDATE memb_info SET ok_ref=ok_ref-1 WHERE memb___id='".$accname."'");
	 }
	break;
	case 4:
	 if($db->query("UPDATE character SET LevelUpPoint=LevelUpPoint+".$bonuspoins." WHERE Name='".$tempChName."'")) $db->query("UPDATE memb_info SET ok_ref=ok_ref-1 WHERE memb___id='".$accname."'");break;
	case 5:
	 if($db->query("UPDATE memb_info SET bankZ=bankZ+".$zenref." WHERE memb___id='".$accname."'"))
	 {
	  $db->query("UPDATE character SET LevelUpPoint=LevelUpPoint+".$bonuspoins." WHERE Name='".$tempChName."'");
	  $db->query("UPDATE memb_info SET ok_ref=ok_ref-1 WHERE memb___id='".$accname."'");
	 }
	 break;
         default : $temp.= refsys_referrmsgver;
       }
      }
      elseif($query_a[1]==1)
      {
       switch($typeprice)
       {
	case 1:	if($db->query("UPDATE memb_info SET bankZ=bankZ+".$zenref." WHERE memb___id='".$accname."'"))  $db->query("UPDATE memb_info SET ok_ref=NULL WHERE memb___id='".$accname."'");break;
	case 3: 
	 if($db->query("UPDATE memb_info SET bankZ=bankZ+".$zenref." WHERE memb___id='".$accname."'")) 
	 {
	  $db->query("UPDATE ".$config["cr_table"]." SET ".$config["cr_column"]."=".$config["cr_column"]."+".$bonusref." WHERE ".$config["cr_acc"]."='".$accname."'");
	  $db->query("UPDATE memb_info SET ok_ref=NULL WHERE memb___id='".$accname."'");
	 }
	 break;
	case 4: if($db->query("UPDATE character SET LevelUpPoint=LevelUpPoint+".$bonuspoins." WHERE Name='".$tempChName."'")) $db->query("UPDATE memb_info SET ok_ref=NULL WHERE memb___id='".$accname."'");break;
	case 5:
	 if($db->query("UPDATE memb_info SET bankZ=bankZ+".$zenref." WHERE memb___id='".$accname."'"))
	 {
	  $db->query("UPDATE character SET LevelUpPoint=LevelUpPoint+".$bonuspoins." WHERE Name='".$tempChName."'");
	  $db->query("UPDATE memb_info SET ok_ref=NULL WHERE memb___id='".$accname."'");
	 }
        break;
	default : $temp.= refsys_referrmsgver;
       }						
      }
      WriteLogs ("Referal_","выдан приз приглсившему, AccountID='".$accname."");
     }
     echo "<div style='font-weight:bold;font-style:italic;font-size:14px;color:green;text-align:center;'>".refsys_referokpricemsg."<br></div>";	
   }
   else echo "<div style='color:red;font-size:12px;font-style:italic;font-weigth:bold;text-align:center;'>".refsys_refernotokpricemsg."</div>";
  }
  }
  elseif (($row<=0)||($row==NULL))echo "<div style='color:brown;font-size:12px;font-weigth:bold;text-align:center;' align=center>".refsys_refermsg1."<br></div>";
  if ($query[2]>0) echo "<br><div style='font-size:10px;font-weight:bold;text-align:left'>".refsys_refermsg2." $query[2] ".refsys_refermsg3."</div>";
  echo "<div style='font-size:12px;font-style:italic;font-weigth:bold;text-align:left;'> ".refsys_refactiveref." $row </div>";
  $content->out_content("theme/".$config["theme"]."/them/usercp_ref_f_f.html");
 }
}*//*end ref*/
$temp = ob_get_contents();
ob_end_clean();