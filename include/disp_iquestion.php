<?php
function disp_iquestion($id, $qn) {
   // get the question
   $result = mysql_query("SELECT * FROM responses WHERE user_id=$id && qn=$qn LIMIT 1") or die(mysql_error());
   $rdata = mysql_fetch_array($result);
   $result = mysql_query("SELECT * FROM questions WHERE id=".$rdata['qid']." LIMIT 1") or die(mysql_error());
   $qdata = mysql_fetch_array($result);
   echo "<form action='infoexp2.php' method='post' name='infoexp2'>\n";
   // display the question prompt
   echo "<h2>Block ".$rdata["bn"]."</h2>\n";
   if ($rdata["qid"]<=900) {
   $result = mysql_query("SELECT * FROM responses WHERE user_id=$id && bid=".$rdata["bid"]) or die(mysql_error());
   echo "<p>This block consists of ".mysql_num_rows($result)." questions. In each question, you will see a screen with 100 dots on it. These dots will be either red or blue. The likelihood of the number of red dots is as follows:</p>\n";
   echo "<ul>";
   for ($i=1; $i <= 13; $i++) {
      $prob = $qdata["p$i"];
      if ($prob>0.0) {
      	 // get the state data
      	 $result = mysql_query("SELECT * FROM states WHERE id=$i LIMIT 1") or die(mysql_error());
      	 $sdata = mysql_fetch_array($result);
         echo "<li>With ".($prob*100)."% probability there will be ".$sdata["nred"]." red dots</li>";
      }
   }
   echo "</ul>\n";
   echo "<p>In each question you will be asked to choose between the following alternatives:</p>\n";
   disp_acts($qdata,0);
   echo "<p>These alternatives will pay money depending on the number of dots on the screen.</p>\n";
   } else {
      echo "<p>In this part of the experiment you will be asked to make a number of choices between two lotteries. You will be shown a screen which will have 10 pairs of lotteries, one on each line. You will be asked to make one choice on each line. At the end of the experiment, one line will be chosen at random by the computer, and you will get to play the lottery you selected on that line.</p>\n";
      echo "<p>You will be asked to choose between lotteries of the following type:</p>\n";
      echo "<table class='radioary'>\n";
         echo "<tr><td><input type='radio' value='0' name='alt1_choice' id='alt1_L'>1/10 of &#36;6, or 9/10 of &#36;4.80</td><td class='radiomid'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;OR&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>" .
            "<td><input type='radio' value='1' name='alt1_choice' id='alt1_R'>" .
            "1/10 of &#36;11.55, or 9/10 of &#36;0.30</td></tr>\n";
      echo "</table>\n";
      echo "<p>If you chose the option on the left (and this line is the one chosen for payment) then ".
         "you would receive &#36;6 with probability 1/10 or &#36;4.80 with probability 9/10.  If you chose ".
         "the option on the right, you would receive &#36;11.55 with probability 1/10 or &#36;0.30 with ".
         "probability 9/10.</p>\n";
   }

   echo "<input type='hidden' name='flag_inst' value='1' />\n";
   echo "<input type='hidden' name='qn' value='". $qn ."' />\n";
   echo "<input type='hidden' name='t_load' id='t_load' value='' />\n";
   echo "<input type='hidden' name='t_submit' id='t_submit' value='' />\n";
   echo "</form>\n";

   echo "<!-- client-side Form Validations:\n";
   echo "Uses the excellent form validation script from JavaScript-coder.com-->\n";
   echo "<script type='text/javascript'>\n";
   echo "// <![CDATA[\n";
   echo "    var frmvalidator  = new Validator(\"infoexp2\");\n";
   echo "// ]]>\n";
   echo "</script>\n\n\n";
}
?>
