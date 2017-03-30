<?php session_start();
define ('insite', 1); 
@include "opt.php";
$version="1.5.1";
require "_sysvol/them.php";
if(!$_SESSION["mwcsaddr"])
{
 $sname = explode("/", $_SERVER["SCRIPT_NAME"]);
 if (substr($sname[1],-4)!=".php")
  $_SESSION["mwcsaddr"] = "http://".$_SERVER["HTTP_HOST"]."/".$sname[1];
  else
  $_SESSION["mwcsaddr"]="http://".$_SERVER["HTTP_HOST"];
}

if (!$_GET["p"]) $_GET["p"]=0; 

$getprrem = $_GET["p"]; 

if(!$_SESSION["mwclang"])
{
 switch($_GET["l"])
 {
  case 1: $_SESSION["mwclang"]="rus"; break;
  case 2: $_SESSION["mwclang"]="eng"; break;
  default:  $_SESSION["mwclang"]="rus";
 }
}
if ($_SESSION["mwclang"])
 include "lang/".$_SESSION["mwclang"]."/".$_SESSION["mwclang"]."_install.php";


require "_sysvol/security.php";
require "_sysvol/fsql.php";

if(strlen($config["ctype"])>2)
{
 $db = new Connect ($config["ctype"], $config["db_host"], $config["db_name"], $config["db_user"], $config["db_upwd"],"SQL Server",0); 
 $count = $db->numrows("SELECT * FROM MWC_admin");
 $isreinsstall = $db->fetchrow("SELECT value FROM MWC WHERE parametr='reinstall'");
}
else
{
 $count=0;
 $isreinsstall[0]==0;
}
if ($_REQUEST["finish"])
 {
  $_SESSION["adm"]=1;
  $db = new Connect ($config["ctype"], $config["db_host"], $config["db_name"], $config["db_user"], $config["db_upwd"],"SQL Server",0); 
  $db->query("UPDATE MWC SET value = '1' WHERE parametr='reinstall'");
  $db->close();
  unset($_SESSION["ulogin"],$_SESSION["upwd"],$_SESSION["udb"], $_SESSION["uhost"],$_SESSION["utype"],$_SESSION["md5"],$_SESSION["user"],$_SESSION["pwd"]);
  rename("install.php", "_dat/install.php");
  header("location: ".$_SESSION["mwcsaddr"]);
 }
 
if ($count==0 or $isreinsstall[0]==0)
{
  
 $_SESSION["install"]=true;
 $content = new content("install",$_SESSION["mwclang"]);
 $content->set('|siteaddress|', $_SESSION["mwcsaddr"]); 
 $step = array(
 0=>$lang["inst_step0"],
 1=>$lang["inst_step1"],
 2=>$lang["inst_step2"],
 3=>$lang["inst_step3"],
 4=>$lang["inst_step4"]);


ob_start();
 $content->set('|instep|', $step[$getprrem]);
  $on_screen = "<table border='0' class='posit' width='80%'>";

if ($_REQUEST["addadm"])//создание админа
{
 $adminl = substr(checkword($_POST["adml"]),0,10);
 $admind = substr(checkword($_POST["admp"]),0,10);
 $admnn = substr(checkword($_POST["anick"]),0,10);

 if (strlen($adminl)>3 && strlen($admind)>3 && strlen($admnn)>3)
 {
 if ($count==0)
 {
  $admind = md5($admind);
  $step[0] = $lang["inst_step3"];
  $db->query("INSERT INTO dbo.MWC_admin ([name],[pwd],[nick],[access]) VALUES('".$adminl."','".$admind."','".$admnn."','100')") or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>Can't create admin account</td></tr>";
  $on_screen.="<tr><td style='font-weight:bold;color:green;'>".$lang["inst_cadm"]."</td></tr>";
 }
  else $on_screen.="<tr><td style='font-weight:bold;color:red;'>".$lang["inst_eadm"]."</td></tr>";
  $content->set('|button|', "<a href=\"?p=4\"><img style=\"margin-right:20px; margin-top:5px; float:right;\" src=\"imgs/inst/stp_next.png\" alt=\"Далее\"></a>");
 }
 else
 {
  $on_screen.="<tr><td style='font-weight:bold;color:red;'>".$lang["inst_sym"]."</td></tr>";
  $content->set('|button|', "<a href='javascript:history.back();'><img style=\"margin-right:20px; margin-top:5px; float:right;\" src=\"imgs/inst/stp_back.png\" alt=\"Далее\"></a>");
 }
}
else
{

 switch($getprrem)
 {
  case 0:
   $on_screen.="<tr><td align='center'><a href='?p=1&l=1'><img src='imgs/rus.png' border='0'></a><a href='?p=1&l=2'><img src='imgs/eng.png' border='0'></a></td></tr>";
   $content->set('|button|', ""); 
  break;
  
  case 1:
   $on_screen.= "
   <tr><td>".$lang["inst_ctype"]."</td><td><select name='contype' id='contype'><option value='1'>ODBC</option><option value='2'>MSSQL</option></select></td></tr>
   <tr><td>".$lang["inst_dblogin"]."</td><td><input type='text' value='".$_SESSION["ulogin"]."' name='dbusr' id='dbusr'></td></tr>
   <tr><td>".$lang["inst_dbpwd"]."</td><td><input type='text'  name='dbpwd' id='dbpwd'></td></tr>
   <tr><td>".$lang["inst_dbbd"]."</td><td><input type='text' value='".$_SESSION["udb"]."' name='dbdb' id='dbdb'></td><tr>
   <tr><td>".$lang["inst_dbhost"]."</td><td><input type='text' name='dbhost' value='".$_SESSION["uhost"]."' id='dbhost'></td><tr>
   <tr><td><input type='button' OnClick='cccc()' value='".$lang["inst_checkc"]."'></td><td> <span id='checkc'></span></td></tr>";
    $content->set('|button|', ""); 
  break;
  
  case 2:
  if (!$_SESSION["ulogin"] or !$_SESSION["upwd"] or !$_SESSION["udb"] or !$_SESSION["uhost"] or !$_SESSION["utype"]) die("reinstall please.");
  
  $db = new Connect ($_SESSION["utype"], $_SESSION["uhost"], $_SESSION["udb"], $_SESSION["ulogin"], $_SESSION["upwd"],"SQL Server",0); 
   $result = $db->fetchrow($db->query("SELECT data_type FROM information_schema.columns WHERE table_name='MEMB_INFO' AND column_name='memb__pwd'"));
   $goon = 0;
  switch($result[0])
   {
    case "varbinary":  
     $on_screen.= "<tr><td style='font-weight:bold;color:green;'>".$lang["inst_md5"]."</td></tr>";  
     $_SESSION["md5"]="on";
     $goon=1; 
     $db->query("CREATE FUNCTION [dbo].[fn_md5] (@data VARCHAR(10), @data2 VARCHAR(10))
                        RETURNS BINARY(16) AS
                        BEGIN
                        DECLARE @hash BINARY(16)
                        EXEC master.dbo.XP_MD5_EncodeKeyVal @data, @data2, @hash OUT
                        RETURN @hash
                        END") or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>Can't add md5 function</td></tr>";
			$db->query("Use master") or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>Can't use master!</td></tr>";
     $db->query("exec sp_addextendedproc 'XP_MD5_EncodeKeyVal', 'WZ_MD5_MOD.dll'")or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>Can't exec md5 dll!</td></tr>";
    break;
    case "varchar":  $on_screen.= "<tr><td style='font-weight:bold;color:red;'>".$lang["inst_nomd5"]."</td></tr>"; $goon=1;  $_SESSION["md5"]="off";break;
    case "nvarchar":  $on_screen.= "<tr><td style='font-weight:bold;color:red;'>".$lang["inst_nomd5"]."</td></tr>"; $goon=1;  $_SESSION["md5"]="off";break;
   // default:  $on_screen.= "<tr><td style='font-weight:bold;color:red;'>unknown type!(".$result[0].") Check configs!</td></tr>";  $content->set('|button|', ""); $goon=0;
    default:  $on_screen.= "<tr><td style='font-weight:bold;color:red;'>unknown type!(".$result[0].") Check configs! it maybe some errors</td></tr>"; $goon=1;  $_SESSION["md5"]="off";
   }
   if ($goon==1)
   {
     $db->query("IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[MWC_admin]') AND type in (N'U')) DROP TABLE [dbo].[MWC_admin]");
     $db->query("if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[web_shop]') and OBJECTPROPERTY(id, N'IsUserTable') = 1) drop table [dbo].[web_shop] ");
     $db->query("if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[MWC]') and OBJECTPROPERTY(id, N'IsUserTable') = 1) drop table [dbo].[MWC]"); 	
     $db->query("IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[mwc_vote_top]') AND type in (N'U')) DROP TABLE [dbo].[mwc_vote_top]");
     $db->query("IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[mwc_vote_list]') AND type in (N'U')) DROP TABLE [dbo].[mwc_vote_list]"); 
     $db->query("IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[MWC_messages]') AND type in (N'U')) DROP TABLE [dbo].[mwc_vote_list]"); 
     $db->query("IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[alotery]') AND type in (N'U')) DROP TABLE [dbo].[mwc_vote_list]"); 
     $db->query("IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[Ltickets]') AND type in (N'U')) DROP TABLE [dbo].[mwc_vote_list]"); 
     $db->query("IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[MWC_questsystem]') AND type in (N'U')) DROP TABLE [dbo].[mwc_vote_list]"); 
     $db->query("IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[MWC_questwinners]') AND type in (N'U')) DROP TABLE [dbo].[mwc_vote_list]"); 
     $db->query("IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[MWC_chat]') AND type in (N'U')) DROP TABLE [dbo].[mwc_vote_list]"); 

     $db->query("alter table [character] add [Resets] int not null default('0')") or  $on_screen.="<tr><td style='font-weight:bold;color:red;'>Resets not been established, it may already have in the database</td></tr>"; 
     $db->query("alter table [character] add [gr_res] int not null default('0')") or  $on_screen.= "<tr><td style='font-weight:bold;color:red;'>gr_res not been established, it may already have in the database</td></tr>";
     $db->query("alter table [memb_info] add [recpwd] varchar(12) null") or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>recpwd not been established, it may already have in the database</td></tr>";
     $db->query("alter table [memb_info] add [isadmin] int null") or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>sadmin not been established, it may already have in the database</td></tr>";
     $db->query("alter table [memb_info] add [bankZ] bigint not null default(0)") or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>bankZ not been established, it may already have in the database</td></tr>";
     $db->query("alter table [memb_info] add [mwcban_time] varchar(17) not null default('0')") or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>ban_time not been established, it may already have in the database</td></tr>";
     $db->query("alter table [memb_info] add [ban_des] varchar(255) null ") or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>ban_des not been established, it may already have in the database</td></tr>";
     $db->query("alter table [memb_info] add [refer] varchar(255) null ") or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>refer not been established, it may already have in the database</td></tr>";
     $db->query("alter table [memb_info] add [name_refer] varchar(12) null") or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>name_refer not been established, it may already have in the database</td></tr>";
     $db->query("alter table [memb_info] add [ref_quan] int null") or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>ref_quan not been established, it may already have in the database</td></tr>";
     $db->query("alter table [memb_info] add [ok_ref] int null") or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>ok_ref not been established, it may already have in the database</td></tr>";
     $db->query("alter table [memb_info] add [ref_acc] int null") or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>ref_acc not been established, it may already have in the database</td></tr>";
     $db->query("alter table [memb_info] add [ok_inv] int null") or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>ok_inv not been established, it may already have in the database</td></tr>";
     $db->query("alter table [memb_info] add [rdate] varchar(17) null")or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>rdate not been established, it may already have in the database</td></tr>";
     $db->query("alter table [memb_info] add [credits] [int] NOT NULL default('0')")or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>credits not been established, it may already have in the database</td></tr>";
     $db->query("alter table [memb_info] add [act_time] [varchar](17) NULL default('0')")or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>act_time not been established, it may already have in the database</td></tr>";
     $db->query("alter table [memb_info] add [opt_inv] [int] NULL default('0')")or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>opt_inv not been established, it may already have in the database</td></tr>";
     $db->query("alter table dbo.memb_stat add onlinetm int not null default 0")or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>onlinetm not been established, it may already have in the database</td></tr>";
    // $db->query("IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[WZ_DISCONNECT_MEMB]') AND type in (N'P', N'PC')) DROP PROCEDURE [dbo].[WZ_DISCONNECT_MEMB]")or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>Wcan't drop Z_DISCONNECT_MEMB procedure</td></tr>";
      /*$db->query("CREATE PROCEDURE [dbo].[WZ_DISCONNECT_MEMB]
@memb___id varchar(10)
 AS
Begin    
set nocount on
    Declare  @find_id varchar(10)    
    Declare @ConnectStat tinyint
    Set @ConnectStat = 0     
    Set @find_id = 'NOT'
    select @find_id = S.memb___id from MEMB_STAT S INNER JOIN MEMB_INFO I ON S.memb___id = I.memb___id 
           where I.memb___id = @memb___id
    if( @find_id <> 'NOT' )    
    begin        
        update MEMB_STAT set ConnectStat = @ConnectStat, DisConnectTM = getdate(), onlinetm = onlinetm+(DATEDIFF(mi,ConnectTM,getdate()))
         where memb___id = @memb___id
    end
end")or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>can't add new WZ_DISCONNECT_MEMB procedure</td></tr>";;
	 */$db->query("CREATE TABLE [dbo].[web_shop](
	[code] [int] IDENTITY(1,1) NOT NULL,
	[memb___id] [varchar](10) NULL,
	[class] [varchar](35) NULL,
	[price] [bigint] NULL,
	[cprice] [bigint] NULL,
	[sdate] [varchar](10) NULL,
	[item] [varchar](32) NULL,
	[igroup] [int] NULL,
	[iid] [int] NULL,
	[ilevel] [int] NULL,
	[iexc] [int] NULL,
	[ianc] [int] NULL,
	[ipvp] [int] NULL,
	[ihar] [int] NULL,
	[isock] [int] NULL,
	[typepay] [char](2) NULL,
	[typesave] [char](2) NULL,
	[item_name] [char](30) NULL,
	[was_dropd] [int]  NULL default('0')
        ) ON [PRIMARY]")or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>table web shop is not installed, perhaps it is already in the database</td></tr>";	
     $db->query("CREATE TABLE [dbo].[mwc_vote_top](
							[id] [int] IDENTITY (1, 1) NOT NULL,
							[memb___id] [varchar](10) NOT NULL,
							[top_id] [int] NULL,
							[ip] [varchar](15) NOT NULL,
							[last_voted] [varchar](12) NULL,
							[realvotes] [int] NULL default('0'),
							[clicks] [int] NULL
							) ON [PRIMARY]")or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>table mwc_vote_top is not installed, perhaps it is already in the database</td></tr>";
	$db->query("IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[MWC_chartoacc]') AND type in (N'P', N'PC'))DROP PROCEDURE [dbo].[MWC_chartoacc]");
$db->query("CREATE PROCEDURE [dbo].[MWC_chartoacc] @Name varchar(10) AS BEGIN
 SET NOCOUNT ON;
 declare @AccountID as varchar(10)
 Set @AccountID = '0'
 
 select @AccountID = AccountID FROM Character Where Name=@Name
 SELECT @AccountID
END ")or $on_screen.= "<tr><td style='font-weight:bold;color:red;'> PROCEDURE [dbo].[MWC_chartoacc] is not installed, perhaps it is already in the database</td></tr>";
 
     $db->query("CREATE TABLE [dbo].[mwc_vote_list](
			[id] [int] IDENTITY (1, 1) NOT NULL,
			[top_name] [varchar](15) NOT NULL,
			[top_addres] [varchar](100) NOT NULL,
			[top_pic] [varchar](100) NOT NULL,
			[flag] [int] NULL,
			[credits] [int] NULL
			) ON [PRIMARY]") or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>table mwc_vote_list is not installed, perhaps it is already in the database</td></tr>";
     $db->query("CREATE TABLE [dbo].[MWC](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[parametr] [varchar](50) NULL,
	[value] [int] default('0') NULL
       ) ON [PRIMARY]") or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>table MWC is not installed, perhaps it is already in the database</td></tr>";	
     $db->query("INSERT INTO MWC (parametr)VALUES('Ovalue')") or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>failed to insert in table MWC!</td></tr>";
     $db->query("INSERT INTO MWC (parametr)VALUES('reinstall')") or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>failed to insert in table MWC!</td></tr>";
    $db->query("CREATE TABLE [dbo].[MWC_admin](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [varchar](20) NULL,
	[pwd] [varchar](50) NULL,
	[lastdate] [datetime] NULL,
	[nick] [varchar](20) NULL,
	[access] [smallint] NULL
       ) ON [PRIMARY]") or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>table MWC_admin is not installed, perhaps it is already in the database</td></tr>";
     $db->query("CREATE TABLE [dbo].[MWC_messages](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[memb___id] [varchar](10) NULL,
	[message] [text] NULL,
	[Fromm] [varchar](10) NULL,
	[isread] [char](1) NULL
       ) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]")or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>table MWC_messages is not installed, perhaps it is already in the database</td></tr>";			

     $db->query("CREATE TABLE [dbo].[alotery](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[memb___id] [varchar](11) NOT NULL,
	[item] [varchar](32) NOT NULL,
	[prise] [bigint] NOT NULL,
	[isdrop] [int] default(0) NULL,
	[tickets] [int] default(0) NOT NULL,
	[time] [datetime] NOT NULL
      ) ON [PRIMARY]")or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>table alotery is not installed, perhaps it is already in the database</td></tr>";
	
     $db->query("CREATE TABLE [dbo].[Ltickets](
	[lot_id] [int] NOT NULL,
	[memb___id] [varchar](11) NOT NULL,
	[tickets] [int] default(0) NOT NULL
      ) ON [PRIMARY]")or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>table Ltickets is not installed, perhaps it is already in the database</td></tr>";

      $db->query("CREATE TABLE [dbo].[MWC_chat](
	[message] [text] NULL,
	[time] [varchar](20) NULL,
	[memb___id] [varchar](10) NULL,
	[nick] [varchar](20) NULL
      ) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]")or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>table MWC_chat is not installed, perhaps it is already in the database</td></tr>";

     $db->query("CREATE TABLE [dbo].[MWC_questsystem](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[i_di] [varchar](2) NOT NULL,
	[i_group] [varchar](2) NOT NULL,
	[i_opt] [varchar](2) NOT NULL,
	[i_exopt] [varchar](2) NOT NULL,
	[i_dur] [varchar](2) NOT NULL,
	[need_i] [varchar](320) NOT NULL,
	[memb___id] [varchar](11) NOT NULL,
	[start] [varchar](15) NOT NULL
      ) ON [PRIMARY]")or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>table MWC_questsystem is not installed, perhaps it is already in the database</td></tr>";	

     $db->query("CREATE TABLE [dbo].[MWC_questwinners](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[memb___id] [varchar](10) NOT NULL,
	[item] [varchar](32) NOT NULL
      ) ON [PRIMARY]") or $on_screen.= "<tr><td style='font-weight:bold;color:red;'>table MWC_questwinners is not installed, perhaps it is already in the database</td></tr>";	
    $in_write = '<?php if (!defined("insite")){die("no access to config file!");}'.chr(13).chr(10);
	$in_write.= '/**'.chr(13).chr(10).'* MuWebClone'.chr(13).chr(10).'* ver '.$version.chr(13).chr(10);
	$in_write.= '* Special thx to: Magistr,{8bit}DoS.Ninja, eg-network.ru & Deathless(MuAntrum(DEW)),Vaflan(MyMuWeb), Platinum(for tests), muhard.ru'.chr(13).chr(10);
	$in_write.= '**/'.chr(13).chr(10).'$config["db_host"]= "'.$_SESSION["uhost"].'";'.chr(13).chr(10);
	$in_write.= '$config["db_user"]= "'.$_SESSION["ulogin"].'";'.chr(13).chr(10);
	$in_write.= '$config["db_upwd"]= "'.$_SESSION["upwd"].'";'.chr(13).chr(10);
	$in_write.= '$config["ctype"]= "'.$_SESSION["utype"].'";'.chr(13).chr(10);
	$in_write.= '$config["db_name"]= "'.$_SESSION["udb"].'";'.chr(13).chr(10);
	$in_write.= '$config["siteaddress"]= "'.$_SESSION["mwcsaddr"].'";'.chr(13).chr(10);
	$in_write.= '$config["forum"]= "'.$_SESSION["mwcsaddr"].'/forum";'.chr(13).chr(10);
	$in_write.= '$config["def_lang"]= "'.$_SESSION["mwclang"].'";'.chr(13).chr(10);
	$in_write.= '$config["theme"]="castle";'.chr(13).chr(10);
    $in_write.= '$config["odbc_driver"]="SQL Server";'.chr(13).chr(10);
    $in_write.= '$config["debug"]=1;'.chr(13).chr(10);
    $in_write.= '$config["md5use"]="'.$_SESSION["md5"].'";'.chr(13).chr(10);
    $in_write.= '$config["description"]="";'.chr(13).chr(10);
    $in_write.= '$config["keywords"]="";'.chr(13).chr(10);
    $in_write.= '$config["under_rec"]=0;'.chr(13).chr(10);
    $in_write.= '$config["server_name"]="p4f";'.chr(13).chr(10);
    $in_write.= '$config["server_team"]="p4f";'.chr(13).chr(10);
    $in_write.= '$config["description"]="MuWebClone";'.chr(13).chr(10);
    $in_write.= '$config["keywords"]="MuWebClone";'.chr(13).chr(10);
    $in_write.= '$config["mainmod"]="qinfo,strongest,questtop";'.chr(13).chr(10);
    $in_write.= '$config["mainmod_def"]="qinfo,strongest,questtop,top5guild,baners";'.chr(13).chr(10);
    $in_write.= '$config["cr_table"]="MEMB_INFO";'.chr(13).chr(10);
    $in_write.= '$config["cr_column"]="credits";'.chr(13).chr(10);
    $in_write.= '$config["cr_acc"]="memb___id";'.chr(13).chr(10);
	
    $fhandler = fopen("opt.php","w");
    fwrite($fhandler,$in_write);
    fclose($fhandler);
    if($count>0) $content->set('|button|', "<a href=\"?p=4\"><img style=\"margin-right:20px; margin-top:5px; float:right;\" src=\"imgs/inst/stp_next.png\" alt=\"Далее\"></a>"); 
    else $content->set('|button|', "<a href=\"?p=3\"><img style=\"margin-right:20px; margin-top:5px; float:right;\" src=\"imgs/inst/stp_next.png\" alt=\"Далее\"></a>"); 
   }

  break;
  case 3:
   if ($count<1)
   {
    $on_screen.= "  
   <tr>
     <td>".$lang["inst_alogin"]."</td>
	 <td><input type='text' maxlength='10' name='adml' id='adml'></td>
   </tr> 
   <tr>
     <td>".$lang["inst_apwd"]."</td>
	 <td><input type='text' maxlength='10' name='admp' id='admp'></td>
   </tr> 
   <tr>
     <td>".$lang["inst_nick"]."</td>
	 <td><input type='text' maxlength='10' name='anick' id='anick'></td>
   </tr>";
   $content->set('|button|', "<input type='submit' class='sbutton' name='addadm' id='addadm' value='.'>");
   }
  break;
  case 4:

   $on_screen.="<tr><td>".$lang["inst_fmsg"]."</td></tr>";
   $db->query("UPDATE MWC SET value = '1' WHERE parametr='reinstall'");
   $content->set('|button|', "<input type='submit' class='sbutton' name='finish' id='finish' value='.'>");
  
  break;
 }
}
 $on_screen.="</table>";
 $content->set('|content|', $on_screen);
 $content->out_content("imgs/install.html");
}
else die("сайт уже установлен");
 ob_end_flush();
 /*//session_set_cookie_params(518400,"/",$_SERVER["HTTP_HOST"], false,true); */