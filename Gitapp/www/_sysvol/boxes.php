<?php

/**
* стройка выпадаюзего списка
* @name - название элемента
* @parameters array - [id][val]
* @cssclass - ксс класс для элемента
* @selid - отметить выбранный элемент
* @empty - true/false показывает или не показывает пустую строчку
* возвращает строку с HTML кодом
**/
function build_box($name,$parameters,$cssclass,$selid=-1,$empty=false)
{
 $output = "<select class='".$cssclass."' name='".$name."' id='".$name."'>";
 foreach ($parameters as $id=>$val)
 {
  $output.= "<option value='".$id."'";
  if ($id==$selid) $output.= " selected ";
  $output.= ">".$val."</option>";
 }
 if ($empty==true)$output.= "<option >None</option>";
 $output.= "</select>";
 return $output;
}
