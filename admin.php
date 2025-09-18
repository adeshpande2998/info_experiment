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
      <title>Admin</title>
      <link rel="STYLESHEET" type="text/css" href="style/fg_membersite.css">
</head>
<body>
<div id='fg_membersite_content'>

<div id="top">
<h1>Individual Decision-Making Experiment</h1>
</div>

<div id="content">
<?php
// get the user id
$user_id = $fgmembersite->UserID();
if (1 == $user_id) {
?>
<h2>Admin</h2>
<?php
   $fgmembersite->DBLogin();
   // reset all users
   if ('RESET_ALL'==$_GET["op"]) {
      $result = mysql_query("DELETE FROM responses") or die(mysql_error());
      $result = mysql_query("DELETE FROM fgusers3 WHERE NOT id_user = 1") or die(mysql_error());
      $uresult = mysql_query("SELECT * FROM fgusers3") or die(mysql_error());
      while($user_data = mysql_fetch_array($uresult)) {
      	 $reset_id = $user_data["id_user"];
         $result = mysql_query("UPDATE fgusers3 SET nq=0 WHERE id_user =$reset_id") or die(mysql_error());
         $result = mysql_query("UPDATE fgusers3 SET flag_inst=0 WHERE id_user =$reset_id") or die(mysql_error());
         $result = mysql_query("UPDATE fgusers3 SET qpay=NULL WHERE id_user =$reset_id") or die(mysql_error());
         $result = mysql_query("UPDATE fgusers3 SET qpay2=NULL WHERE id_user =$reset_id") or die(mysql_error());
      }
      for ($i=1; $i<=35; $i++) {
      	 $name = sprintf('13%02d', $i);
         $result = mysql_query('insert into fgusers3(
                id_user,
         	name,
                username,
                password,
                confirmcode
                )
                values
                (
                "' . $fgmembersite->SanitizeForSQL("$name") . '",
                "' . $fgmembersite->SanitizeForSQL("User ".$name) . '",
                "' . $fgmembersite->SanitizeForSQL("ID".$name) . '",
                "' . md5("$name") . '",
                "y"
                )') or die(mysql_error());
      }
      echo "<p><font color=red><b>All users reset.</b></font><br /></p>\n";
      echo "<ul><li><a href='admin.php'>Return to Admin menu</a></li></ul>\n"; 
   } elseif ('RESET'==$_GET["op"]) {
      $reset_id = $_GET["id"];
      if ($reset_id) {
         $result = mysql_query("DELETE FROM responses WHERE user_id=$reset_id") or die(mysql_error());
         $result = mysql_query("UPDATE fgusers3 SET nq=0 WHERE id_user =$reset_id") or die(mysql_error());
         $result = mysql_query("UPDATE fgusers3 SET flag_inst=0 WHERE id_user =$reset_id") or die(mysql_error());
         $result = mysql_query("UPDATE fgusers3 SET qpay=NULL WHERE id_user =$reset_id") or die(mysql_error());
         $result = mysql_query("UPDATE fgusers3 SET qpay2=NULL WHERE id_user =$reset_id") or die(mysql_error());
      	 echo "<p><font color=red><b>Successfully reset user $reset_id.</b></font><br /></p>\n";
      } else {
      	 echo "<p><font color=red><b>Please specify a user to reset.</b></font><br /></p>\n";
      }
      echo "<ul><li><a href='admin.php'>Return to Admin menu</a></li></ul>\n";
   } else {
   echo "<p><b>Welcome administrator!</b></p>\n";
   echo "<ul><li><a href='admin.php?op=RESET_ALL'>Reset All Users</a></li></ul>\n";
   echo "<table class='options'>\n";
   echo "   <tr><th>user id</th><th># quest done</th><th># quest pay</th><th>payoff</th><th></th></tr>\n";
   $uresult = mysql_query("SELECT * FROM fgusers3 ORDER BY id_user") or die(mysql_error());
   while($user_data = mysql_fetch_array($uresult)) {
      $qpay = $user_data["qpay"];
      $qpay2 = $user_data["qpay2"];
         $result = mysql_query("SELECT * FROM responses WHERE user_id=".$user_data["id_user"]) or die(mysql_error());
         $num_q = mysql_num_rows($result);
      echo "   <tr>";
      echo "<td>".$user_data["id_user"]."</td>";
      echo "<td>".$user_data["nq"]." of $num_q</td>";
      if ($qpay) {
         $result = mysql_query("SELECT * FROM responses WHERE user_id=".$user_data["id_user"]) or die(mysql_error());
         $num_q = mysql_num_rows($result);
         $result = mysql_query("SELECT * FROM responses WHERE user_id=".$user_data["id_user"]." && qn=$qpay LIMIT 1") or die(mysql_error());
         $rdata = mysql_fetch_array($result);
         $true_state = $rdata['true_state'];
         $chosen_act = $rdata['chosen_act'];
         $result = mysql_query("SELECT * FROM questions WHERE id=".$rdata['qid']." LIMIT 1") or die(mysql_error());
         $qdata = mysql_fetch_array($result);
         $result = mysql_query("SELECT * FROM states WHERE id=".$rdata['true_state']." LIMIT 1") or die(mysql_error());
         $sdata = mysql_fetch_array($result);
         $result = mysql_query("SELECT * FROM acts WHERE id=".$rdata['chosen_act']." LIMIT 1") or die(mysql_error());
         $adata = mysql_fetch_array($result);
         $my_payment = $adata["payoff$true_state"];

         $result = mysql_query("SELECT * FROM responses WHERE user_id=".$user_data["id_user"]." && qn=$qpay2 LIMIT 1") or die(mysql_error());
         $rdata = mysql_fetch_array($result);
         $true_state = $rdata['true_state'];
         $chosen_act = $rdata['chosen_act'];
        $lottery_choice = $rdata["alt".$chosen_act."_choice"];

   if ($qpay2 == $num_q) {
      if (!$lottery_choice) {
         $payoff2 = ($true_state<=$chosen_act) ? (6) : (4.80);
         $my_payment = $my_payment+$payoff2;
      } else {
         $payoff2 = ($true_state<=$chosen_act) ? (11.55) : (0.30);
         $my_payment = $my_payment+$payoff2;
      }
   } else {
      if (!$lottery_choice) { // option A
         $payoff2 = ($true_state<=$chosen_act) ? (10) : (0);
         $my_payment = $my_payment+$payoff2;
      } else {
         $payoff2 = ($true_state<=$chosen_act) ? (0) : (10);
         $my_payment = $my_payment+$payoff2;
      }
   }

         echo "<td>$qpay, $qpay2.$chosen_act</td>";
         echo "<td>\$".sprintf("%02.2f",$my_payment)."</td>";
      } else {
         echo "<td></td>";
         echo "<td></td>";
      }
      echo "<td><a href='admin.php?op=RESET&id=".$user_data["id_user"]."'>Reset</a></td>\n";
      echo "</tr>\n";
   }
   echo "</table>\n";
   }
?>
<?php
} else {
?>
<p><font color='red'><b>This is a restricted access page!</b></font></p>
<?php
}
?>
</div>

<div id="footer">
<div id="leftnav">
&#8592;&nbsp;<a href='login-home.php'>Home</a>
</div>
</div>

</div>
</body>
</html>
