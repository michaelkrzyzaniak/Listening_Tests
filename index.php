<?php
  require_once("include/scripts.php");
  require_once("include/header_footer.php");
   
  print_top_of_page("Index");
  $files = glob("*.php");
  foreach($files as $file)
    echo "<a href='$file'>$file</a><br>";
  print_botom_of_page();
?>
