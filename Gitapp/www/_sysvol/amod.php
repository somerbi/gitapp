<?php if (!defined('inpanel'))die();
/**
* Парсинг рсс у ucoz форума
*@path = путь до форума
*/
function addnews($path="http://www.p4f.ru/forum/0-0-0-37")
{
 global $content;
 $temp = @file_get_contents($path);
 if($temp)
 {
 require "_sysvol/xml.php";
 $tt = new Xml();
 $text = $tt->start($temp);
 ob_start();
 foreach ($text["rss"]["channel"]["item"] as $id=>$v)
 {
     $content->set('|title|',"<a href='".$v["link"]."'>".iconv("UTF-8","Windows-1251",$v["title"])."</a>");
     $content->set('|time|',$v["pubDate"]);
     $content->set('|des|',iconv("UTF-8","Windows-1251",$v["description"]));
     $content->out_content("_sysvol/_a/theme/news.html");
 }   
 $temp = ob_get_contents();
 ob_end_clean();
 return $temp;
 }
 else return "<div align='center'>Can't open site</div>";
}
/**
* конструктор меню администратора
*/
function getadmenu()
{
 global $config;
 if(!$_SESSION["mwclang"])
   $_SESSION["mwclang"] = $config["def_lang"]="rus";
	  
 $loadfile = @file("_dat/amenu.dat");
 $nowitime = time();
 $cachtime = @filemtime("_dat/menus/".$_SESSION["mwclang"]."_admmenu"); 

 if (empty($loadfile) or !$loadfile) echo "error menu loading!";
 else
 {
  if(!$cachtime || ($nowitime-$cachtime > 3600))
  {
   ob_start();
   include "lang/".$_SESSION["mwclang"]."/".$_SESSION["mwclang"]."_titles.php";
   global $content;
   foreach ($loadfile as $m)
   {
    $showarr = explode("::",$m);
    $showarr[1]=trim($showarr[1]);
    $content->set('|modulename|', $showarr[0]);
    $content->set('|modulecapt|', $lang[$showarr[1]]);
    $content->out_content("_sysvol/_a/theme/admmenu.html");
   }
    $content->set('|modulecapt|', $content->lng["admm_onsite"]);
    $content->out_content("_sysvol/_a/theme/admmenu_u.html");
   $bufer = ob_get_contents();
   write_catch("_dat/menus/".$_SESSION["mwclang"]."_admmenu",$bufer);
   ob_end_clean(); 
   return $bufer;	
  }
  else 
    return file_get_contents ("_dat/menus/".$_SESSION["mwclang"]."_admmenu");
 }
}
/*
* показать модули админа
*/
function a_modul($mname)
{
 if( strlen($mname)>1)
 {
  $apages = preg_replace("/[^a-zA-Z0-9_-]/i", "", substr($mname,0,10));
  if(get_accesslvl()>=checacc($apages))
  {
   if(is_file("_sysvol/_a/".$apages.".php"))
   {
    require "_sysvol/_a/".$apages.".php";
    return $temp;
   }
   else return "wrong page!";
  }
  else
   return "<div align='center'>you haven't access</div>";
 }
 return "wrong page!";
	
}
/*
* инфо о(для) админе
*/
function ainfo()
{
 global $db;
 global $content;
 $alert=0;
 
 $letters = $db->numrows("SELECT id FROM MWC_messages");
 ($letters<1) ? $letters=0 : true;
 $content->set('|lnum|', $letters);
 $content->set('|admin|', $_SESSION["sadmin"]);
 $content->set('|gradm|', get_group());
 
 $warn = array("Inject","MaybeP","Connec","ALARMA","Pages_");
 $Lhandle = opendir("logZ");

 $id=0;
 if (get_accesslvl()>=checacc("lreader"))
 {
  while ($file = readdir($Lhandle))
  {
   if (in_array(substr($file,0,6),$warn))
   {
    $alert=1;
    break;
   }
  }
 }
 else
  $alert=0;
  
 if($alert>0)
 {
  $content->set('|alert_msg|', "<img src=\"imgs/x.gif\" border=\"0\" alt=\"сообщения\" style=\"position:relative;top:4px;left:0px;\">".$content->lng["adm_warn"]);
 $content->set('|vari|', 1);
 }
 else
 {
  $content->set('|alert_msg|', "");
  $content->set('|vari|', 0);
 }
 
 return $content->out_content("_sysvol/_a/theme/ainfo.html",1);
}

/**
* узнать максимальный доступ у админа
*/
function get_accesslvl()
{
 if ($_SESSION["sadmin"])
 {
  global $db;
  $level = $db->fetchrow("SELECT [access] FROM MWC_admin WHERE name='".$_SESSION["sadmin"]."'");
  return $level[0];
 }
 return 0;
}
/**
* отображение группы по провам доступа в админку (для админов и гм)
*/
function get_group()
{
  if($_SESSION["sadmin"])
  {
   global $content;
   $acclevel = get_accesslvl();
   if ($acclevel>0 && $acclevel<=10) return $content->lng["adm_gr1"]; //gm
   elseif ($acclevel>10 && $acclevel<=20) return $content->lng["adm_gr2"]; //moders
   elseif ($acclevel>20 && $acclevel<=30) return $content->lng["adm_gr3"]; //adm helpers
   elseif ($acclevel>30 && $acclevel<=50) return $content->lng["adm_gr4"]; //adm 
   elseif ($acclevel==100) return $content->lng["adm_gr5"]; //main adm 
   else die("access error!");
  }
}

/**
* проверка на доступ
* @mname - навание модуля
* возвращает уровень тоступа
*/
function checacc($mname)
{
 $adb = @file("_dat/maccess.db");
 $level = 100;
 foreach($adb as $id=>$v)
 {
  $tmp = explode("::",$v);
  if($tmp[0] == $mname)
  {
   $level = (int)$tmp[1];
   break;
  }
 }
 return $level;
}

/**
* названия страничек
*/
function show_t($tt,$t=1)
{
 define ('insite', 1);
 include "lang/".$_SESSION["mwclang"]."/".$_SESSION["mwclang"]."_titles.php";
if ($t==1)
 echo  $lang["title_".trim($tt)];
else
 return $lang["title_".trim($tt)];
}