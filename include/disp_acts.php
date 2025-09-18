<?php
function disp_acts($qdata,$act_id_disp) {
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
   echo "<th></th><th>Option</th>";
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
            echo "<td><input type='radio' value='$act_id' name='chosen_act' id='choice_$k'".
               (($act_id_disp == $act_id)?(" checked"):(""))." /></td><td>&#".(65+$k).";</td>";
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
}

