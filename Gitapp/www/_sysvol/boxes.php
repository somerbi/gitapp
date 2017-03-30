<?php

/**
* ������� ����������� ������
* @name - �������� ��������
* @parameters array - [id][val]
* @cssclass - ��� ����� ��� ��������
* @selid - �������� ��������� �������
* @empty - true/false ���������� ��� �� ���������� ������ �������
* ���������� ������ � HTML �����
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
