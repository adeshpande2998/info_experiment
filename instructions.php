<?PHP
require_once("./include/membersite_config.php");

if(!$fgmembersite->CheckLogin()) {
    $fgmembersite->RedirectToURL("login.php");
    exit;
}

$page = $_GET["p"];
if (empty($page)) $page = 0;
if ($page<1 || $page>8) {
    $fgmembersite->RedirectToURL("login-home.php");
    exit;
}
?>

<?PHP
require_once("./include/disp_instructions.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
      <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
      <title>Instructions</title>
      <link rel="STYLESHEET" type="text/css" href="style/fg_membersite.css">
      <?php if ($page==5) { ?> <script type='text/javascript' src='scripts/gen_validatorv31.js'></script> <?php } ?>
</head>
<body>
<div id='fg_membersite_content'>

<div id="top">
<h1>Individual Decision-Making Experiment</h1>
</div>

<div id="content">
<h2>Instructions</h2>
<?PHP
// log in to the database
$fgmembersite->DBLogin();
disp_instructions($page, $fgmembersite->UserID());
?>
</div>
<div id="footer">

<div id="leftnav">
&#8592;&nbsp;<?PHP if ($page==1) echo "<a href='login-home.php'>Home</a>"; else echo "<a href='instructions.php?p=", $page-1, "'>Previous</a>"; ?>
</div>

<div id="rightnav">
<?PHP
if ($page<8) {
    if ($page!=5) {
        echo "<a href='instructions.php?p=", $page+1, "'>Next</a>";
    }  else {
        echo "<a href='javaScript:submitform()'>Next</a>";
    }
} else {
    echo "<a href='login-home.php'>Home</a>";
}?>&nbsp;&#8594;
</div>

</div>

</div>

<?php
if ($page==5) {
?>
<script type='text/javascript'>
// <![CDATA[
   function submitform() {
      //this check triggers the validations
      if(document.infoexp2.onsubmit()) {
         var endTime = new Date();
         document.getElementById('t_submit').value=endTime.getTime();
         document.infoexp2.submit();
      }
   }
// ]]>
</script>

<script type='text/javascript'>
// <![CDATA[
   var startTime = new Date();
   document.getElementById('t_load').value=startTime.getTime();
// ]]>
</script>
<?php
}
?>
</body>
</html>

