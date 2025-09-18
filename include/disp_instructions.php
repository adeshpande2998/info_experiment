<?php
require_once("./include/disp_acts.php");

function disp_instructions($page, $user_id) {
    switch($page) {
        default:
        case 1:
            $result = mysql_query("SELECT * FROM blocks") or die(mysql_error());
            $nblock = mysql_num_rows($result);
            $bdata = mysql_fetch_array($result);
            echo "<p>This experiment is designed to study decision making, ".
            "and consists of ".($nblock+1)." sections. </p><p>The first 4 sections consist ".
            "of ".$bdata["num_q"]." questions each. At the end of the experiment, ".
            "one of these questions will be selected at random. ".
            "The amount of money that you get at the end of the experiment ".
            "will depend on your answer to this question. </p><p> The final section consists of a single question.  You will also have an opportunity to earn additional money in this section.  Instructions will be given once you get there.</p><p>Anything you earn ".
            "from this experiment will be added to your show-up ".
            "fee of $10.</p>\n";
            echo "<p>Please turn off cellular phones now.</p>\n";
            echo "<p>The entire session will take place through your computer ".
            "terminal. Please do not talk or in any way communicate with ".
            "other participants during the session.</p>\n";
            echo "<p>Please <b>do NOT use the forward and back buttons in ".
            "your browser</b> to navigate. Only use the links at the bottom ".
            "of each page to move forward or back.</p>\n";
            echo "<p>We will start with a brief instruction period. During ".
            "this instruction period, you will be given a description of the ".
            "main features of the session and will be shown how to use the ".
            "program. If you have any questions during this period, please ".
            "raise your hand.</p>\n";
            echo "<p>After you have completed the experiment, <b>please ".
            "remain quietly seated until <u>everyone</u> has completed the ".
            "experiment</b>.</p>\n";
            break;
        case 2:
            echo "<p>For each question you will be shown 100 dots on a ".
            "screen. Some of these dots will be red, while some will be blue. ".
            "Here is an example of such a screen:</p>\n";
            $dot_ary = array_fill(0,51,1); // 51 red dots
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
            echo "<p>The number of red dots will be determined at random. You ".
            "will be told how likely each number of red dots is. So, for ".
            "example you might be told that there is a 75% chance of there ".
            "being 49 red dots and a 25% chance of there being 51 red dots. ".
            "In this case there is a 3/4 chance that there will be 49 red ".
            "dots on the screen, and a 1/4 chance that there will be 51 red ".
            "dots. There will never be any other number of red dots on the ".
            "screen. The number of red dots in each question is determined ".
            "independently of the number of red dots that have appeared in ".
            "previous questions.</p>\n";
            break;
        case 3:
            echo "<p>You will be asked to make a choice between two or more ".
            "options. Each of these options will pay  out different amounts ".
            "of money, depending on how many red dots are on the screen.</p>\n";
            $result = mysql_query("SELECT * FROM questions WHERE id=99 LIMIT 1") or die(mysql_error());
            $qdata = mysql_fetch_array($result);
            disp_acts($qdata,0);
            echo "<p>In this case, if you chose option A (and this question ".
            "was the one selected for payment) then you would get $10 if ".
            "there were 49 red dots on the screen and $0 if there were 51 red ".
            "dots. If you chose option B you would get $10 if there were 51 ".
            "red dots on the screen and $0 if there were 49 red dots. If you ".
            "chose option C you would receive $5 regardless of the number of ".
            "red dots on the screen.</p>\n";
            echo "<p>You will now have the chance to try an example question. ".
            "You will not be paid depending on your answer to this question - ".
            "it is just for practice.</p>\n";
            break;
        case 4:
            // get the question
            $result = mysql_query("SELECT * FROM questions WHERE id=99 LIMIT 1") or die(mysql_error());
            $qdata = mysql_fetch_array($result);
            // display the question prompt
            echo "<h3>Example Question</h3>\n";
            echo "<p>You are about to see a screen with 100 dots on it. These dots will be either red or blue. The likelihood of the number of red dots is as follows:</p>\n";
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
            echo "<p>You will then be asked to choose between a number of alternatives. These alternatives will pay money depending on the number of dots on the screen.</p>\n";
            break;
        case 5:
            // get the question
            $result = mysql_query("SELECT * FROM questions WHERE id=99 LIMIT 1") or die(mysql_error());
            $qdata = mysql_fetch_array($result);
            echo "<form action='instructions.php?p=6' method='post' name='infoexp2'>\n";
            echo "<h3>Example Question</h3>\n";
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
            $true_state = 0;
            $my_rand = mt_rand(0,1000)/1000;
            $cum_prob = 0;
            for ($i=1; $i <= 13; $i++) {
            $prob = $qdata["p$i"];
            if ($prob>0.0) {
            $cum_prob += $prob;
            // echo "true_state: $true_state;  my_rand: $my_rand; cum_prob: $cum_prob<br />\n";
            if (!$true_state && $my_rand <= $cum_prob) {
            $true_state = $i;
            }
            }
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

            echo "<input type='hidden' name='true_state' value='". $true_state ."' />\n";
            echo "<input type='hidden' name='qn' value='0' />\n";
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
            break;
        case 6:
            $true_state = $_POST['true_state'];
            $chosen_act = $_POST['chosen_act'];
            echo "<h3>Payment</h3>\n";
            if (empty($true_state) || empty($chosen_act)) {
                echo "<p><b><font color='red'>Please return to the previous page and select an option.</font></b></p>\n";
            } else {
                $result = mysql_query("SELECT * FROM questions WHERE id=99 LIMIT 1") or die(mysql_error());
                $qdata = mysql_fetch_array($result);
                $result = mysql_query("SELECT * FROM states WHERE id=$true_state LIMIT 1") or die(mysql_error());
                $sdata = mysql_fetch_array($result);
                $result = mysql_query("SELECT * FROM acts WHERE id=$chosen_act LIMIT 1") or die(mysql_error());
                $adata = mysql_fetch_array($result);
   
                // display the payment info
                echo "<p>For this question, you chose the following option:</p>\n";
                disp_acts($qdata,$chosen_act);
                echo "<p>There were ".$sdata["nred"]." red dots on the screen.</p><p>If this were the question that had been selected for payment, you would have received \$".$adata["payoff$true_state"]." in addition to your show up fee.</p>\n";
            }
            break;
        case 7:
        case 8:

// if the user has not responded to any questions yet (which is likely), set them up with blocks and empty responses
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

	$result = mysql_query("SELECT * FROM blocks") or die(mysql_error());
            $nblock = mysql_num_rows($result);
            $bdata = mysql_fetch_array($result);
if ($page==7) {
            echo "<p>Here is a description of ".
		"the questions that you will face in the first $nblock ".
		"sections of the experiment.</p>\n";

$qresult = mysql_query("SELECT qid FROM responses WHERE user_id=$user_id AND qn IN(SELECT min(qn) FROM responses WHERE user_id=$user_id GROUP BY bid) ORDER BY bn") or die(mysql_error());
$bcount=1;
while($qiddata = mysql_fetch_array($qresult)) {
   if($bcount<=$nblock) {
      $qid = $qiddata["qid"];
            // get the question
            $result = mysql_query("SELECT * FROM questions WHERE id='$qid' LIMIT 1") or die(mysql_error());
            $qdata = mysql_fetch_array($result);

            echo "<h3>Block $bcount</h3>\n";
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
            echo "<p>You will be asked to choose between the following options:</p>\n";
            disp_acts($qdata,0);
            echo "<br />";
	    $bcount++;
   }
}

} else {
    echo "<p><b>REMEMBER: Each section consists of ".$bdata["num_q"]." questions, each with the same probabilities and available options. ".
       "You will be reminded in each question what the probabilities and available options are for that question.</b>";

            echo "<p>Again, please <b>do NOT use the forward and back buttons in ".
            "your browser</b> to navigate. Only use the links at the bottom ".
            "of each page to move forward or back.</p>\n";
            echo "<p>If you have any questions, please raise your hand now, ".
            	"otherwise click to the lower right to return to the Home ".
            	"Page and begin the experiment.</p>\n";
            break;
    }
}

}
?>
