<?php
  require_once("include/scripts.php");
  require_once("include/header_footer.php");
  delete_cookie();
   
  print_top_of_page("Thank You!");
  echo "The session is finished and your answers have been saved.<br />";
  echo "<a href='classification.php'>Start Over</a>";
  
  print_botom_of_page();
?>
