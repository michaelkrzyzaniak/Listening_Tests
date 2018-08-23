<?php
  require_once("include/scripts.php");
  require_once("include/header_footer.php");
  
  make_cookie_if_necessary();
  
  $db = open_db();

  switch(get_param("action"))
    {
      case "submitted_answer":
        save_word_cloud_response($db);
      default: break;
    }
  
  $MAX_NUM_RESPONSES = 30;
  $RESPONSE_COUNT    = get_user_response_count($db, "word_cloud");
  redirict_if_session_finished($RESPONSE_COUNT, $MAX_NUM_RESPONSES, "session_finished.php");
  close_db($db);

  $audio_class    = get_random_audio_class();
  $synth_method   = get_random_synth_method($audio_class);
  $audio_basename = get_random_audio_file($audio_class, $synth_method);
  
  $available_audio_classes = get_audio_classes_string();
  $available_synth_methods = get_synth_methods_string($audio_class);
  $available_audio_files   = get_audio_files_string($audio_class, $synth_method);
  
  $audio_path = get_audio_file_path($audio_class, $synth_method, $audio_basename);
  
  print_top_of_page("Ambisynth Listening Test 4");
  print_user_response_count($RESPONSE_COUNT, $MAX_NUM_RESPONSES);
  echo "<br>Listen to the audio then write the first thing that comes to mind.";
?>

<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
  <?php make_audio_player("audio_player", "buttons", $audio_path); ?>
  <input type="hidden" name="action" value="<?php echo obfuscate('submitted_answer') ?>">
  <input type="hidden" name="audio_class" value="<?php echo obfuscate($audio_class) ?>">
  <input type="hidden" name="synth_method" value="<?php echo obfuscate($synth_method) ?>">
  <input type="hidden" name="audio_basename" value="<?php echo obfuscate($audio_basename) ?>">
  <input type="hidden" name="audio_path" value="<?php echo obfuscate($audio_path) ?>">
  <input type="hidden" name="start_epoch" value="<?php echo obfuscate(strval(time())) ?>">
  <input type="text" name="user_response" required autofocus></input>
  <button type="submit">Save and Continue</button>
</form>

<?php print_botom_of_page(); ?>
