<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
/* You should enable error reporting for mysqli before attempting to make a connection */
$myserver='';
$myuser='';
$mypassword="";
$mydatabase='';
$maintenance=0;
$telegramtoken="";
$adminchannel="";
//devel hacking part:


//i use it for allow only some ip who know allowip password;
//and use for set a not necesary captcha bit
$uri_head=$_SERVER['SERVER_PORT']==80?'http://':'https://';
$uri_head.=$_SERVER['HTTP_HOST'];
$my=mysqli_connect($myserver,$myuser,$mypassword,'adminer') or die( header("Location:https://letmegooglethat.com/?q=$uri_head+database+server+is+not+ available"));
$allowip='a';
$allowip=$my->query("SELECT `value` from `key` where `ID`='1'")->fetch_row()[0];
$args=explode("/",$_SERVER['REQUEST_URI']);
if ($args[1]=='disableip'){
	$my->query("delete from allowedip where `IP`!=hex(inet6_aton('127.0.0.1'))");//delete all but not localhost
	$length=substr(str_shuffle("123456789"),0,2);
	for ($i=0;$i<6;$i++)$random.=str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
	$newkey=substr($random, 0, $length);
	$telegramme="$uri_head\nnew key\n$newkey";
	$u="UPDATE `key` SET`value` = '$newkey' WHERE `ID`='1'";
	$my->query($u)or die("error updating key");
	file_get_contents("https://api.telegram.org/bot$telegramtoken/sendMessage?chat_id=-$adminchannel&text=".urlencode($telegramme) );
	$my->close();
	session_destroy();
	header("Location:$uri_head");
	die;
}

$q=$my->query("select INET6_NTOA(UNHEX(`IP`)) from `allowedip` where `IP`=hex(inet6_aton('{$_SERVER['REMOTE_ADDR']}'))")->fetch_row()[0];
if($q==$_SERVER['REMOTE_ADDR'])$my->close();
else {
	$telegramme="$uri_head\n we have a new visit";
	file_get_contents("https://api.telegram.org/bot$telegramtoken/sendMessage?chat_id=-$adminchannel&text=".urlencode($telegramme) );
	//search button part:
	if ($_REQUEST['search']==$allowip){
		$_REQUEST['allowip']=$allowip;
    		unset($_REQUEST['search']);
	}
	if(isset($_REQUEST['search'])){$my->close();header("Location: https://gogoprivate.com/search/index.html?q=#gsc.q={$_REQUEST['search']}");}
	//end button part
	
	if($_REQUEST['allowip']==$allowip) {
		$ID=$my->query("select COALESCE(MAX(ID), 0)+1 from allowedip ")->fetch_row()[0];
		$insert="INSERT INTO `allowedip` (`ID`,`IP`,`dati`) VALUES ('{$ID}', hex(inet6_aton('{$_SERVER['REMOTE_ADDR']}')),now())";
		$my->query($insert)or die($insert.$my->error);
	}
	if(isset($_REQUEST['allowip'])&&($_REQUEST['allowip']==$allowip)){$my->close();header("Location: {$uri_head}/new/ip/allowed");die;}
	if($_REQUEST['allowip']!=$allowip) {
		session_start();
		$w="<!DOCTYPE html><html lang=\"en\"><head><meta charset=\"utf-8\"><link rel=\"icon\" type=\"image/png\" href=\"/favicon.png\"/>
			<title>Nothing here!</title></head>
			<style type=\"text/css\">
				html
				{ background: url(/Після_грози.jpg) no-repeat center center fixed;
				-webkit-background-size: cover;
				-moz-background-size: cover;
				-o-background-size: cover;
				background-size: cover;
				}E_ALL & ~E_NOTICE & ~E_WARNING
			</style>
			<body>
			";
	
			$w.="<div style=\"position:absolute;
				top:50%;
				left:50%;
				width:300px;
				margin-left: -150px;
				text-align:center;
			\">"
				;$w.="
				<form action=\"/\"method = \"POST\">
				<input type=\"search\" name=\"search\"
					size=15 id=\"search\"
					placeholder=\"There is nothing  &#128269;\"
					autofocus
					autocomplete=\"off\"
					onChange=\"this.form.submit()\"><input type=\"submit\"value=\"websearch\">
				<form/>";
			$w.="
			</div>
			";
			$w.="<div style=\"position: fixed;
					bottom: 10px;
					right: 23px;
					background-color: black;
					text-decoration: none;
					opacity:0.75;
					color: #ffeedd;\">
			<a href=\"https://commons.wikimedia.org/wiki/User:Swift11\"
				style=\"
					text-decoration: none;
					\">
			Polonina of shepherds after a thunderstorm. Carpathian Biosphere Reserve, Ukraine. Михайло Пецкович  CC BY-SA 4.0</a>
			</div>
			</body>
			<html>";
		echo  preg_replace('/\s\s+/', ' ',str_replace(Array("\n","\t"),"",$w));
		//echo "$w";
		$my->close();
		session_unset();die;
	}
}
unset($_REQUEST['allowip']);

?>
