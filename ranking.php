<?php
  require_once("include/scripts.php");
  require_once("include/header_footer.php");
  
  make_cookie_if_necessary();
  
  $db = open_db();
  $RESPONSE_COUNT    = get_user_response_count($db, "ranking");
  
  switch(get_param("action"))
    {
      case "submitted_answer":
        save_ranking_response($db);
        redirect_to_another_test($RESPONSE_COUNT, $MAX_NUM_RESPONSES);
      default: break;
    }
  
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
  
  echo "<br>Move the sliders to rate how synthesized or realistic the audio files sound. Touch or hover over the sliders to hear the audio.<br><br>";
  
  $loc = $_SERVER['PHP_SELF'];
  echo "<form method='post' action='$loc'>";

  echo "<span style='float:left'><b>SYNTHESIZED</b></span><span style='float:right'><b>REALISTIC</b></span>";
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
  <?php print_submit_button() ?>
</form>

<?php print_botom_of_page(); ?>
