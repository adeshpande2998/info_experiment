<?php
require_once("./include/disp_acts.php");

function disp_question($id, $qn) {
   // get the question
   $result = mysql_query("SELECT * FROM responses WHERE user_id=$id && qn=$qn LIMIT 1") or die(mysql_error());
   $rdata = mysql_fetch_array($result);
   $result = mysql_query("SELECT * FROM questions WHERE id=".$rdata['qid']." LIMIT 1") or die(mysql_error());
   $qdata = mysql_fetch_array($result);

if ($rdata['qid']<900) {
   echo  "<form action='infoexp2.php' method='post' name='infoexp2'>\n";
   echo "<h2>Question $qn</h2>\n";

   echo "<p>Remember:</p>\n";
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
   // determine the true state of the world
   $true_state = intval($rdata['true_state']);
   if (!$true_state) {
      $my_rand = mt_rand(0,1000)/1000;
      $cum_prob = 0;
      $last_state = 0;
      for ($i=1; $i <= 13; $i++) {
         $prob = $qdata["p$i"];
         if ($prob>0.0) {
            $cum_prob += $prob;
            $last_state = $i;
            if (!$true_state && $my_rand <= $cum_prob) {
               $true_state = $i;
            }
         }
      }
      if (!$true_state) {
         $true_state = $last_state;
      }
      mysql_query("UPDATE responses SET true_state='$true_state' WHERE user_id=$id && qn=$qn") or die(mysql_error());
      $rdata['true_state'] = $true_state;
   }

   // populate the array of dots
   $result = mysql_query("SELECT * FROM states WHERE id=$true_state LIMIT 1") or die(mysql_error());
   $sdata = mysql_fetch_array($result);
   $nred = $sdata["nred"];
   $dot_ary = array_fill(0,$nred,1);
   $dot_ary = array_pad($dot_ary,100,0);
   shuffle($dot_ary) or die("unable to randomize dot array");

   // display the table of dots
   echo "<table class='dotary'>\n";
   for ($i = 0; $i < 10; $i++) {
      echo "<tr>";
      for ($j = 0; $j < 10; $j++) {
         if ($dot_ary[10*$i+$j]) {
            echo "<td><img class='dot' alt='R' src='images/red.png' /></td>";
         } else {
            echo "<td><img class='dot' alt='B' src='images/blue.png' /></td>";
         }
      }
      echo "</tr>\n";
   }
   echo "</table>\n";

   echo "<p>Please select from the following options:</p>\n";

   disp_acts($qdata,0);

   for ($j=1; $j<=10; $j++) {
      echo "<input type='hidden' name='alt".$j."_choice' value='NULL' />\n";
   }
   echo "<input type='hidden' name='qn' value='". $qn ."' />\n";
   echo "<input type='hidden' name='t_load' id='t_load' value='' />\n";
   echo "<input type='hidden' name='t_submit' id='t_submit' value='' />\n";
   echo "</form>\n";

   echo "<!-- client-side Form Validations:\n";
   echo "Uses the excellent form validation script from JavaScript-coder.com-->\n";
   echo "<script type='text/javascript'>\n";
   echo "// <![CDATA[\n";
   echo "    var frmvalidator  = new Validator(\"infoexp2\");\n";
   echo "    frmvalidator.addValidation(\"chosen_act\",\"selone_radio\",\"Please select an option.\");\n";
   echo "// ]]>\n";
   echo "</script>\n\n\n";
} elseif ($rdata['qid']==901) {
   echo  "<form action='infoexp2.php' method='post' name='infoexp2'>\n";
   echo "<h2>Question $qn</h2>\n";
   // display the grid
      echo "<p>Which would you choose? (please select an option on each line)</p>\n";
      echo "<table class='radioary'>\n";
      for ($j = 1; $j <= 10; $j++) {
         echo "<tr><td><input type='radio' value='0' name='alt".$j."_choice' id='alt".$j."_L'>".
            "$j/10 of &#36;6, or ".(10-$j)."/10 of &#36;4.80</td><td class='radiomid'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;OR&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>" .
            "<td><input type='radio' value='1' name='alt".$j."_choice' id='alt".$j."_R'>" .
            "$j/10 of &#36;11.55, or ".(10-$j)."/10 of &#36;0.30</td></tr>\n";
      }

      // echo "<tr><td><input type='button' onclick='javaScript:autocomplete0()' value='Auto-complete Left' /></td><td></td>".
         "<td><input type='button' onclick='javaScript:autocomplete1()' value='Auto-complete Right' /></td></tr>\n";
      echo "</table>\n";

   $chosen_line = intval($rdata['chosen_act']);
   if (!$chosen_line) {
      $chosen_line = mt_rand(1,10);
      mysql_query("UPDATE responses SET chosen_act='$chosen_line' WHERE user_id=$id && qn=$qn") or die(mysql_error());
      $rdata['chosen_act'] = $chosen_line;
   }
   $true_state = intval($rdata['true_state']);
   if (!$true_state) {
      $true_state = mt_rand(1,10);
      mysql_query("UPDATE responses SET true_state='$true_state' WHERE user_id=$id && qn=$qn") or die(mysql_error());
      $rdata['true_state'] = $true_state;
   }
   echo "<input type='hidden' name='qn' value='". $qn ."' />\n";
   echo "<input type='hidden' name='t_load' id='t_load' value='' />\n";
   echo "<input type='hidden' name='t_submit' id='t_submit' value='' />\n";
   echo "</form>\n";

   echo "<!-- client-side Form Validations:\n";
   echo "Uses the excellent form validation script from JavaScript-coder.com-->\n";
   echo "<script type='text/javascript'>\n";
   echo "// <![CDATA[\n";
   echo "    var frmvalidator  = new Validator(\"infoexp2\");\n";
      for ($j = 1; $j <= 10; $j++) {
         echo "    frmvalidator.addValidation(\"alt".$j."_choice\",\"selone_radio\",\"Please select an option on each line.\");\n";
      }
   echo "// ]]>\n";
   echo "</script>\n\n\n";

   echo "<script type='text/javascript'>\n";
   echo "// <![CDATA[\n";
      for ($alt=0; $alt<=1; $alt++) {
         echo "   function autocomplete$alt() {\n";
         for ($j = 1; $j <=10; $j++) {
            $rname = "alt".$j."_". ($alt ? "R" : "L");
            $rname_other = "alt".$j."_". ($alt ? "L" : "R");
            echo "      if (document.getElementById('".$rname_other."').checked==false) { ";
            echo "document.getElementById('".$rname."').checked=true;";
            echo " }\n";
         }
         echo "   }\n";
      }

   echo "// ]]>\n";
   echo "</script>\n\n";
}  elseif ($rdata['qid']==902)  {
   echo  "<form action='infoexp2.php' method='post' name='infoexp2'>\n";
   echo "<h2>Question $qn</h2>\n";
   // display the grid
      $result = mysql_query("SELECT * FROM questions WHERE id=999 LIMIT 1") or die(mysql_error());
      $qdata = mysql_fetch_array($result);

   $act_id_disp = 0;
   // store the states that have nonzero probabilities
   $state_tbl = array_fill(0,13,0);
   $j = 0;
   for ($i = 1; $i <= 13; $i++) {
      if ($qdata["p$i"]) {
         $state_tbl[$j]=$i;
         $j++;
      }
   }
   $nstate = $j; // store the number of states with non-zero payoffs
   // now build the table of acts to choose from
   echo "<table class='options'>\n";
   echo "   <tr>";
   echo "<th>Option</th>";
   for ($i = 0; $i < $nstate; $i++) {
      $result = mysql_query("SELECT * FROM states WHERE id=".$state_tbl[$i]." LIMIT 1") or die(mysql_error());
      $sdata = mysql_fetch_array($result);
      echo "<th>Pay if there are ".$sdata["nred"]." red dots</th>";
   }
   echo "</tr>\n";
   $k=0; // number of rows in table
   for ($i = 0; $i < 10; $i++) {
      $act_id = $qdata["a$i"];
      if (!is_null($act_id)) {
      	 if (!$act_id_disp || $act_id_disp == $act_id) {
      	    echo "   <tr".(($k%2)?(" class='alt'"):("")).">";
            echo "<td>&#".(65+$k).";</td>";
            $result = mysql_query("SELECT * FROM acts WHERE id=$act_id LIMIT 1") or die(mysql_error());
            $adata = mysql_fetch_array($result);
            for ($j = 0; $j < $nstate; $j++) {
               echo "<td>".$adata["payoff".$state_tbl[$j]]."</td>";
            }
            echo "</tr>\n";
         }
         $k++;
      }
   }
   echo "</table>\n";

      echo "<p>Which would you choose? (please select an option on each line)</p>\n";
      echo "<table class='options'>\n";
      echo "<tr><th>Probability of 49 red dots</th><th>Choose Option A</th><th>Choose Option B</th></tr>\n";
      for ($j = 1; $j <= 10; $j++) {
         echo "<tr><td>".(10*$j)."&#37;</td><td align='center'><input type='radio' value='0' name='alt".$j."_choice' id='alt".$j."_L'></td>" .
            "<td align='center'><input type='radio' value='1' name='alt".$j."_choice' id='alt".$j."_R'></td></tr>\n";
      }

      // echo "<tr><td></td><td><input type='button' onclick='javaScript:autocomplete0()' value='Auto-complete Left' /></td>".
         "<td><input type='button' onclick='javaScript:autocomplete1()' value='Auto-complete Right' /></td></tr>\n";
      echo "</table>\n";

   $chosen_line = intval($rdata['chosen_act']);
   if (!$chosen_line) {
      $chosen_line = mt_rand(1,10);
      mysql_query("UPDATE responses SET chosen_act='$chosen_line' WHERE user_id=$id && qn=$qn") or die(mysql_error());
      $rdata['chosen_act'] = $chosen_line;
   }
   $true_state = intval($rdata['true_state']);
   if (!$true_state) {
      $true_state = mt_rand(1,10);
      mysql_query("UPDATE responses SET true_state='$true_state' WHERE user_id=$id && qn=$qn") or die(mysql_error());
      $rdata['true_state'] = $true_state;
   }
   echo "<input type='hidden' name='qn' value='". $qn ."' />\n";
   echo "<input type='hidden' name='t_load' id='t_load' value='' />\n";
   echo "<input type='hidden' name='t_submit' id='t_submit' value='' />\n";
   echo "</form>\n";

   echo "<!-- client-side Form Validations:\n";
   echo "Uses the excellent form validation script from JavaScript-coder.com-->\n";
   echo "<script type='text/javascript'>\n";
   echo "// <![CDATA[\n";
   echo "    var frmvalidator  = new Validator(\"infoexp2\");\n";
      for ($j = 1; $j <= 10; $j++) {
         echo "    frmvalidator.addValidation(\"alt".$j."_choice\",\"selone_radio\",\"Please select an option on each line.\");\n";
      }
   echo "// ]]>\n";
   echo "</script>\n\n\n";

   echo "<script type='text/javascript'>\n";
   echo "// <![CDATA[\n";
      for ($alt=0; $alt<=1; $alt++) {
         echo "   function autocomplete$alt() {\n";
         for ($j = 1; $j <=10; $j++) {
            $rname = "alt".$j."_". ($alt ? "R" : "L");
            $rname_other = "alt".$j."_". ($alt ? "L" : "R");
            echo "      if (document.getElementById('".$rname_other."').checked==false) { ";
            echo "document.getElementById('".$rname."').checked=true;";
            echo " }\n";
         }
         echo "   }\n";
      }

   echo "// ]]>\n";
   echo "</script>\n\n";
}
}
?>

