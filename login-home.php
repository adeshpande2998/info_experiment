<?PHP
require_once("./include/membersite_config.php");

if(!$fgmembersite->CheckLogin())
{
    $fgmembersite->RedirectToURL("login.php");
    exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
      <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
      <title>Home Page</title>
      <link rel="STYLESHEET" type="text/css" href="style/fg_membersite.css">
</head>
<body>
<div id='fg_membersite_content'>

<div id="top">
<h1>Individual Decision-Making Experiment</h1>
</div>

<div id="content">
<h2>Home Page</h2>
<p><b>Welcome <?= $fgmembersite->UserFullName(); ?>!</b></p>
<ul>
<li><a href='instructions.php?p=1'>Read Instructions</a></li>
<li><a href='infoexp2.php' target='_blank'>Begin Experiment</a></li>
<?php
// get the user id
$user_id = $fgmembersite->UserID();
if (1 == $user_id) {
    echo "<li><a href='admin.php'>Admin</a></li>\n";
}
?>
<!-- <li><a href='change-pwd.php'>Change password</a></li> -->
<li><a href='logout.php'>Logout</a></li>
</ul>
</div>

<div id="footer">
&nbsp;
</div>

</div>
</body>
</html>
