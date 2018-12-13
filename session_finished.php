<?php
  require_once("include/scripts.php");
  require_once("include/header_footer.php");
  delete_cookie();
   
  print_top_of_page("Thank You!");
  echo "<h1>Thank You!</h1>";
  echo "<br>";
  echo "You are done, and your answers have been saved.<br />";
  echo "Your participation is invaluable to the researchers.<br />";
  echo "We appreciate your help.<br /><br />";
  echo "<a href='https://www.google.com/search?q=flowers&tbm=isch'>Here, have some flowers.</a>";
  echo "<br>";
  echo "<a href='questionnaire.php'>Or take the test again</a>";
  
  print_botom_of_page();
?>
