<?php
  require_once("include/scripts.php");
  require_once("include/header_footer.php");
  //parse_response_management_form("synth_method", "audio_class");
  
  $db = open_db();
  print_top_of_page("Word Cloud Results");
  $a = get_unique_audio_classes_synth_methods_audio_paths($db, "word_cloud");
  print_audio_classes_synth_methods_audio_paths_selects_form($a);
  print_word_cloud_responses($db, get_param("audio_class"), get_param("synth_method"), get_param("audio_path"));
  close_db($db);
  print_botom_of_page();
?>
