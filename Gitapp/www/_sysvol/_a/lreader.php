<?php 
if (!defined('inpanel')) die("no access");
global $config;
ob_start();
global $content;

if($_REQUEST["dellog"] && $_REQUEST["hvd"]) //������� ���
{
 if(substr($_REQUEST["hvd"],0,3)=="Adm")
 { 
  WriteLogs("Adm_",$_SESSION["sadmin"]." ��������� ������� ".htmlspecialchars($_REQUEST["hvd"])." ��� ���� ������ ������� ����� ����!");
 }
 else
 {
  @unlink("logZ/".$_REQUEST["hvd"]);
  WriteLogs("Adm_",$_SESSION["sadmin"]." ������ ".htmlspecialchars($_REQUEST["hvd"]));
 }
  header("Location: ".$config["siteaddress"]."/control.php?page=lreader");
}
if($_REQUEST["downl"] && $_REQUEST["hvd"])
{
 if (file_exists("logZ/".$_REQUEST["hvd"]))
 {
     header("Cache-Control: public");
     header("Content-Description: File Transfer");
     header("Content-Disposition: attachment; filename=".$_REQUEST["hvd"]);
     header("Content-Type: text/plain");
     header("Content-Transfer-Encoding: binary");
     readfile("logZ/".$_REQUEST["hvd"]);
 }
}
$shoZ = "<select name=\"chosen\" class=\"texbx\" style='width:120px;height:20px;' onchange=\"document.onsee.submit()\"><option>. . . </option>";
$Lhandle = opendir("logZ");
$id=0;
while ($file = readdir($Lhandle))
{
if ($file != "." && $file != ".." && $file !=".htaccess" ) 
 {
  if ($_POST["chosen"]==$id) $seld = "selected";
  else $seld="";
  $opnfilz[$id] = $file;
  $shoZ.= "<option value='".$id."' ".$seld.">".$file."</option>";
 }
$id++;
}
$shoZ.= "</select>";
$content->set('|lreader_list|',  $shoZ);
$content->out_content("_sysvol/_a/theme/logreader_h.html");
$content->set('|button|', '');
$content->set('|dbutton|', '');

if ($_POST["chosen"]) 
{
 if (file_exists("logZ/".$opnfilz[$_POST["chosen"]]) && $opnfilz[$_POST["chosen"]]!="")
 {
  $tar = @file("logZ/".$opnfilz[$_POST["chosen"]]);
  $n=1;
  $temp="";
  foreach ($tar as $i=>$v)
  {
   if (strlen(trim(slogZ($v)))>0)
   {
    if ($n==1) $temp.= "<tr style='background-color:#DDFFEA'><td>";
    else $temp.="<br>";
 
    $temp.= slogZ($v);
    if ($n==5)
    { 
     $temp.= "</td></tr>";
     $n=0;
    }
    $n++;
   }
  }
  $content->set('|content|',  $temp);
  $content->set('|button|', '<input type="submit" class="button" value="Delete" name="dellog">');
  $content->set('|dbutton|', '<input type="submit" class="button" value="Download" name="downl">');
  $content->set('|hidlog|', $opnfilz[$_POST["chosen"]]);
  $content->out_content("_sysvol/_a/theme/logreader_c.html");
 }
 else
 {
  $content->set('|content|',  "");
  $content->set('|button|', '');
  $content->set('|dbutton|', '');
  $content->set('|hidlog|', "");
  $content->out_content("_sysvol/_a/theme/logreader_c.html");
 }
}
function slogZ($text) 
{
 $code = array
 (
 "/\[(.*)\](.*?)\[(.*)\][\r]*[\n]*/" => "<span style='font-weight:bold;font-size:14px;color:black;'>$1</span> - (<span style='font-size:14px;color:red;'>$3</span>)",
 "/Message:(.*?)[\r]*[\n]*/" => "<span style='font-weight:bold;font-size:14px;color:red;'><![CDATA[$1]]></span>",
 "/�����: \'(.*?)\'[\r]*[\n]*/" => " ��������� GET: <span style='font-size:14px;color:green;'><![CDATA[$1]]></span>",
 "/�����: \'(.*?)\'[\r]*[\n]*/" => "������ ����� <span style='font-weight:bold;font-size:14px;color:black;'><a href='$1'>$1</a>",
 "/�������: \'(.*?)\'[\r]*[\n]*/" => "<span style='font-size:10px;'>$1</span>"
  );
 $text = preg_replace(array_keys($code), array_values($code), $text);
 return $text;
}
$content->out_content("_sysvol/_a/theme/logreader_f.html");
$temp = ob_get_contents();
ob_end_clean(); 
