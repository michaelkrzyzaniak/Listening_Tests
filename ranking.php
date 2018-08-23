<?php
  require_once("include/scripts.php");
  require_once("include/header_footer.php");
  
  make_cookie_if_necessary();
  
  $db = open_db();

  switch(get_param("action"))
    {
      case "submitted_answer":
        save_ranking_response($db);
      default: break;
    }
  

  
  $MAX_NUM_RESPONSES = 30;
  $RESPONSE_COUNT    = get_user_response_count($db, "ranking");
  redirict_if_session_finished($RESPONSE_COUNT, $MAX_NUM_RESPONSES, "session_finished.php");
  close_db($db);

  $available_synth_methods = get_synth_methods_string($audio_class);
  
  $trial_id        = get_trial_id();
  $audio_class     = get_random_audio_class();
  $synth_methods   = get_synth_methods($audio_class);
  $audio_basenames = array();

  foreach($synth_methods as $synth_method)
    $audio_basenames[$synth_method] = get_random_audio_file($audio_class, $synth_method);
  
  $audio_basenames = shuffle_and_preserve_keys($audio_basenames);
  
  print_top_of_page("Ambisynth Listening Test 3");
  print_user_response_count($RESPONSE_COUNT, $MAX_NUM_RESPONSES);
  
  echo "<br>Move the sliders to rate how glitchy or realistic the following audio files sound.<br><br>";
  
  $loc = $_SERVER['PHP_SELF'];
  echo "<form method='post' action='$loc'>";

  echo "<span style='float:left'><b>SYNTHETIC</b></span><span style='float:right'><b>REALISTIC</b></span>";
  foreach($audio_basenames as $synth_method => $audio_basename)
    {
      $audio_path = get_audio_file_path($audio_class, $synth_method, $audio_basename);
      make_audio_player($synth_method, "slider", $audio_path);
    }
?>


  <input type="hidden" name="action" value="<?php echo obfuscate('submitted_answer') ?>">
  <input type="hidden" name="audio_class" value="<?php echo obfuscate($audio_class) ?>">
  <input type="hidden" name="synth_method" value="<?php echo obfuscate($synth_method) ?>">
  <input type="hidden" name="audio_basename" value="<?php echo obfuscate($audio_basename) ?>">
  <input type="hidden" name="available_synth_methods" value="<?php echo obfuscate($available_synth_methods) ?>">
  <input type="hidden" name="start_epoch" value="<?php echo obfuscate(strval(time())) ?>">
  <button type="submit">Save and Continue</button>
</form>

<?php print_botom_of_page(); ?>
