<?php  if (!defined('insite')) die(" no access");

class content
{
 /**
 *constatns
 **/
 var $vars = array(); /*массив значений на которые будем заменять*/
 var $content = ''; /* страница, с которой будем работать*/
 var $debug; /*дебаг: показывать или нет пустые переменные*/
 public $lng = array();
 /**
 *@file - файл, с массивом значений
 *@language - язык
 *@dbg - режим лдебага
 */
 function content($file="none",$language="rus",$dbg=0)
 {
   if($file == "none") $file = "site";
   $this->debug = $dbg;
   $this->add_dict($language,$file);
 }
 /**
 * lnge - название языка
 * file - название файлы
 **/
 public function add_dict($lnge,$file)
 {
  if (file_exists("./lang/".$lnge."/".$lnge."_".$file.".php"))
  {
   require "./lang/".$lnge."/".$lnge."_".$file.".php";
   $this->lng+=$lang;
   foreach ($lang as $d=>$v)
   {
     $this->vars["|".$d."|"] = $v;
   }
  } 
  else 
  {
   echo "no file /lang/".$lnge."/".$lnge."_".$file.".php found!";
   return false;
  }
 }
 
 public function set($name, $val) 
 {
   $this->vars[$name] = $val;
 }

 function out_content($tpl,$type=0) 
 {
   $this->content = @file_get_contents($tpl);
   if ($this->content == false) $this->if_error($tpl);
   else
   {
     foreach($this->vars as $key => $val)
     {
       $this->content = str_replace($key, $val, $this->content);
     }
     
     if ($this->debug==0)
      $this->content = preg_replace("/[\|]+[A-Za-z0-9_]{1,25}[\|]+/", " ", $this->content);
     
     if($type==0) echo $this->content;
     else return $this->content;
   }
 }

 function if_error($filename)
 {
   echo "<div align='center' style='color:red;font-weight:bold;'>files in $filename not found! </div>";
 }
}
