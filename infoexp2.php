<?PHP
require_once("./include/membersite_config.php");

if(!$fgmembersite->CheckLogin()) {
    $fgmembersite->RedirectToURL("login.php");
    exit;
}
?>

<?PHP
require_once("./include/disp_question.php");
require_once("./include/disp_iquestion.php");
require_once("./include/disp_acts.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
      <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
      <title>Individual Decision-Making Experiment</title>
      <link rel="STYLESHEET" type="text/css" href="style/fg_membersite.css">
      <script type='text/javascript' src='scripts/gen_validatorv31.js'></script>
</head>
<body>

<div id='fg_membersite_content'>

<div id="top">
<h1>Individual Decision-Making Experiment</h1>
</div>

<div id="content">
<?PHP
// get the user id
$user_id = $fgmembersite->UserID();
// get the user data
$fgmembersite->DBLogin();
$result = mysql_query("SELECT * FROM fgusers3 WHERE id_user=$user_id") or die(mysql_error());
$user_data = mysql_fetch_array($result);

// reset if requested
$myreset = empty($_POST['MYRESET']) ? 0 : $_POST['MYRESET'];
if ($myreset) {
   $result = mysql_query("DELETE FROM responses WHERE user_id=$user_id") or die(mysql_error());
   $result = mysql_query("UPDATE fgusers3 SET nq=0 WHERE id_user =$user_id") or die(mysql_error());
   $result = mysql_query("UPDATE fgusers3 SET flag_inst=0 WHERE id_user =$user_id") or die(mysql_error());
   $result = mysql_query("UPDATE fgusers3 SET qpay=NULL WHERE id_user =$user_id") or die(mysql_error());
   $result = mysql_query("UPDATE fgusers3 SET qpay2=NULL WHERE id_user =$user_id") or die(mysql_error());
}

// check for old responses
$result = mysql_query("SELECT * FROM responses WHERE user_id=$user_id") or die(mysql_error());
$num_q = mysql_num_rows($result); // store the number of questions for later
// if responses have not yet been set up, set them up now
if (!$num_q) {
   // retrieve the blocks in a random order
   $result = mysql_query("SELECT * FROM blocks ORDER BY rand()") or die(mysql_error());
   // now store the (empty) responses to each question
   $qcount = 1;
   $bcount = 1;
   while($block_data = mysql_fetch_array($result)) {
      $qid = $block_data["qid"];
      // loop through each question in the block
      for ($j = 0; $j < $block_data['num_q']; $j++) {
         // insert an empty response into the table for this question
         mysql_query("INSERT INTO responses (user_id, qn, qid, bn, bid) VALUES ($user_id, ".
            $qcount++.", $qid, $bcount, ".$block_data["id"].")") or die(mysql_error());
      }
      $bcount++;
   }
   $result = mysql_query("SHOW COLUMNS FROM q_current");   
   if(!$result || mysql_num_rows($result) <= 0) {
      $result = mysql_query("CREATE TABLE q_current (qn INT UNSIGNED NOT NULL DEFAULT 1, INDEX(qn))") or die(mysql_error());
   }
   // now add the last question
   $result = mysql_query("INSERT INTO responses (user_id, qn, qid, bn, bid) VALUES ($user_id, ".
            $qcount.", 901, $bcount, 99)") or die(mysql_error());
   
   $num_q = $qcount;
}

// get the last question the user answered
$qn = empty($_POST['qn']) ? 0 : $_POST['qn'];
// store the data from the last question answered
$chosen_act = 0;
if ($qn>0) {
   $result = mysql_query("SELECT * FROM responses WHERE user_id=$user_id && qn=$qn LIMIT 1") or die(mysql_error());
   $rdata = mysql_fetch_array($result);
   // if the data has not already been submitted to the database, store it
   if (empty($rdata['t_submit'])) {
      // make sure there is a response, and store it
      $chosen_act = $_POST["chosen_act"];
      $n_empty = empty($chosen_act) + empty($_POST["true_state"]);
      for ($j=1; $j<=10; $j++) {
         $n_empty = $n_empty + !isset($_POST["alt".$j."_choice"]);
      }
      if (!$n_empty) {
         $result = mysql_query("UPDATE responses SET chosen_act='$chosen_act' WHERE user_id=$user_id && qn=$qn") or die(mysql_error());       
         $result = mysql_query("UPDATE responses SET true_state='".$_POST["true_state"]."' WHERE user_id=$user_id && qn=$qn") or die(mysql_error());
         // store the radio buttons if they are there
         for ($j=1; $j<=10; $j++) {
            $result = mysql_query("UPDATE responses SET alt".$j."_choice ='".$_POST["alt".$j."_choice"]."' WHERE user_id=$user_id && qn=$qn") or die(mysql_error());
         }
         // store the load and submit times last, after everything else has been submitted successfully
         $result = mysql_query("UPDATE responses SET t_load=COALESCE(t_load, ".$_POST['t_load'].") WHERE user_id=$user_id && qn=$qn") or die(mysql_error());
         $result = mysql_query("UPDATE responses SET t_submit=COALESCE(t_submit, ".$_POST['t_submit'].") WHERE user_id=$user_id && qn=$qn") or die(mysql_error());
         // if we are at the end of the block, flag that the instructions for the next question have not yet been read
         $result = mysql_query("SELECT * FROM responses WHERE user_id=$user_id && qn=".($qn+1)." LIMIT 1") or die(mysql_error());
         $rdata_next = mysql_fetch_array($result);
         if ($rdata_next["bid"]!=$rdata["bid"]) {
            $result = mysql_query("UPDATE fgusers3 SET flag_inst='0' WHERE id_user=$user_id") or die(mysql_error());
         }
      }
   }
}


// flag if the user has read the instructions for this question
if ($_POST["flag_inst"]) {
  $result = mysql_query("UPDATE fgusers3 SET flag_inst='1' WHERE id_user=$user_id") or die(mysql_error());
}

// store the number of questions answered
$qn = $myreset ? 0 : max(0, $user_data['nq']+(!$n_empty)==$qn ? $user_data['nq']+(!$n_empty) : $user_data['nq']);
$result = mysql_query("UPDATE fgusers3 SET nq=$qn WHERE id_user=$user_id") or die(mysql_error());
// advance to the next question
$qn = $qn+1;

if ($qn<=$num_q) {
   // display the question
   $result = mysql_query("SELECT flag_inst FROM fgusers3 WHERE id_user=$user_id") or die(mysql_error());
   $idata = mysql_fetch_array($result);
   $flag_inst = $idata["flag_inst"];
   if (!$flag_inst) {
      disp_iquestion($user_id, $qn);
   } else {
      if ($n_empty && !$_POST["flag_inst"]) {
         echo "<p><b><font color='red'>Please select an option.</font></b></p>\n";
      }
      disp_question($user_id, $qn);
   }
} else {
   echo "<form action='infoexp2.php' method='post' name='infoexp2'>\n";
   // randomly select a question for payment
   $result = mysql_query("SELECT qpay FROM fgusers3 WHERE id_user=$user_id") or die(mysql_error());
   $pdata = mysql_fetch_array($result);
   $qpay = $pdata["qpay"];
   if (!$qpay) {
      $qpay = mt_rand(1,$num_q-2);
      $result = mysql_query("UPDATE fgusers3 SET qpay=$qpay WHERE id_user=$user_id") or die(mysql_error());
   }
   $result = mysql_query("SELECT qpay2 FROM fgusers3 WHERE id_user=$user_id") or die(mysql_error());
   $pdata = mysql_fetch_array($result);
   $qpay2 = $pdata["qpay2"];
   if (!$qpay2) {
      $qpay2 = $num_q-1+mt_rand(1,1); 
      $result = mysql_query("UPDATE fgusers3 SET qpay2=$qpay2 WHERE id_user=$user_id") or die(mysql_error());
   }
   $result = mysql_query("SELECT * FROM responses WHERE user_id=$user_id && qn=$qpay LIMIT 1") or die(mysql_error());
   $rdata = mysql_fetch_array($result);
   $true_state = $rdata['true_state'];
   $result = mysql_query("SELECT * FROM questions WHERE id=".$rdata['qid']." LIMIT 1") or die(mysql_error());
   $qdata = mysql_fetch_array($result);
   $result = mysql_query("SELECT * FROM states WHERE id=".$rdata['true_state']." LIMIT 1") or die(mysql_error());
   $sdata = mysql_fetch_array($result);
   $result = mysql_query("SELECT * FROM acts WHERE id=".$rdata['chosen_act']." LIMIT 1") or die(mysql_error());
   $adata = mysql_fetch_array($result);
   
   // display the payment info
   $my_payment = $adata["payoff$true_state"];
   echo "<h2>Payment</h2>\n";
   echo "<p>Question $qpay was randomly selected for payment. In that round, you chose the following option:</p>\n";
   disp_acts($qdata,$rdata["chosen_act"]);
   echo "<p>There were ".$sdata["nred"]." red dots on the screen, so you won &#36;".sprintf("%0.02f",$my_payment)." in this question.</p>\n";

   $result = mysql_query("SELECT * FROM responses WHERE user_id=$user_id && qn=$qpay2 LIMIT 1") or die(mysql_error());
   $rdata = mysql_fetch_array($result);
   $true_state = $rdata['true_state'];
   $chosen_act = $rdata['chosen_act'];
   $lottery_choice = $rdata["alt".$chosen_act."_choice"];
   echo "<p>The lottery in line ".$chosen_act." of question $qpay2 was also randomly selected for payment. In that round, you chose ";
   if ($qpay2 == $num_q) {
      if (!$lottery_choice) {
         $payoff2 = ($true_state<=$chosen_act) ? (6) : (4.80);
         $my_payment = $my_payment+$payoff2;
         echo "$chosen_act/10 of &#36;6, or ".(10-$chosen_act)."/10 of &#36;4.80. You won &#36;".sprintf("%0.02f",$payoff2)." in this lottery.";
      } else {
         $payoff2 = ($true_state<=$chosen_act) ? (11.55) : (0.30);
         $my_payment = $my_payment+$payoff2;
         echo "$chosen_act/10 of &#36;11.55, or ".(10-$chosen_act)."/10 of &#36;0.30. You won &#36;".sprintf("%0.02f",$payoff2)." in this lottery.";
      }
   } else {
      if (!$lottery_choice) { // option A
         $payoff2 = ($true_state<=$chosen_act) ? (10) : (0);
         $my_payment = $my_payment+$payoff2;
         echo "option A: &#36;10 if there are 49 red dots, or &#36;0 if there are 51 red dots. There were ".(($payoff2>0)? (49) : (51))." red dots, so you won &#36;".sprintf("%0.02f",$payoff2)." in this question.";
      } else {
         $payoff2 = ($true_state<=$chosen_act) ? (0) : (10);
         $my_payment = $my_payment+$payoff2;
         echo "option B: &#36;0 if there are 49 red dots, or &#36;10 if there are 51 red dots. There were ".(($payoff2>0)? (51) : (49))." red dots, so you won &#36;".sprintf("%0.02f",$payoff2)." in this question.";
      }
   }

   echo "</p><p>You will receive a total of &#36;".sprintf("%0.02f",$my_payment)." in addition to your show up fee.</p>";

   // display the farewell message
   echo "<p>You have now completed the experiment. Thank you for your participation.</p>\n";
   // echo "<p>Please click <a href='javaScript:document.infoexp2.submit()'>here</a> if you would like to reset and start again.</p>\n";
   echo "<input type='hidden' name='MYRESET' value='1' />\n";
   echo "</form>\n";
}
?>
</div>

<div id="footer">
<div id="rightnav">
<?PHP
if ($qn<=$num_q) {
   echo "<a href='javaScript:submitform()'>Next</a>&nbsp;&#8594;\n";
} else {
   echo "&nbsp;";
}
?>
</div>
</div>

</div>

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

</body>
</html>
