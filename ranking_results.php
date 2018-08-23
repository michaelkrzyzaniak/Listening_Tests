<?php
  require_once("include/scripts.php");
  require_once("include/header_footer.php");
  $first_index  = get_param('first_index', false, 'synth_method');
  $second_index = get_param('second_index', false, NULL);
  $db = open_db();
  print_top_of_page("Ranking Results");
  print_response_management_form($first_index, $second_index);
  $responses = get_responses($db, "ranking", $first_index, $second_index);
  print_responses($responses);
  close_db($db);
  print_botom_of_page();
?>
