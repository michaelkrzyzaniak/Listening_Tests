<?php
  require_once("include/scripts.php");
  require_once("include/header_footer.php");
  
  make_cookie_if_necessary();
  
  $db = open_db();

  switch(get_param("action"))
    {
      case "submitted_answer":
        save_video_coherence_response($db);
      default: break;
    }

  $MAX_NUM_RESPONSES = 30;
  $RESPONSE_COUNT    = get_user_response_count($db, "video_coherence");
  redirict_if_session_finished($RESPONSE_COUNT, $MAX_NUM_RESPONSES, "session_finished.php");

  close_db($db);

  $audio_basenames     = array();
  $audio_video_class   = get_random_video_class();
  $synth_methods       = get_synth_methods($audio_video_class);
  if(count($synth_methods) < 2){echo "There aren't enough audio files for the selected video class."; die();}
  
  foreach($synth_methods as $synth_method)
    $audio_basenames[$synth_method] = get_random_audio_file($audio_video_class, $synth_method);
  
  $audio_basenames     = shuffle_and_preserve_keys($audio_basenames);
  $audio_basenames     = array_slice($audio_basenames, 0, 2, true);
  $synth_methods       = array_keys($audio_basenames);
  $synth_method_1      = $synth_methods[0];
  $synth_method_2      = $synth_methods[1];
  $audio_basename_1    = $audio_basenames[$synth_method_1];
  $audio_basename_2    = $audio_basenames[$synth_method_2];
  $audio_path_1        = get_audio_file_path($audio_video_class, $synth_method_1, $audio_basename_1);
  $audio_path_2        = get_audio_file_path($audio_video_class, $synth_method_2, $audio_basename_2);
  $video_basename      = get_random_video_file($audio_video_class);
  $video_path          = get_video_file_path($audio_video_class, $video_basename);
  
  print_top_of_page("Ambisynth Listening and Watching Test");
  print_user_response_count($RESPONSE_COUNT, $MAX_NUM_RESPONSES);
  echo "<br>Watch the video with both audio tracks and select which one matches the video.";
?>

<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
  <input type="hidden" name="action"           value="<?php echo obfuscate('submitted_answer') ?>">
  <input type="hidden" name="video_class"      value="<?php echo obfuscate($audio_video_class) ?>">
  <input type="hidden" name="audio_class_1"    value="<?php echo obfuscate($audio_video_class) ?>">
  <input type="hidden" name="audio_class_2"    value="<?php echo obfuscate($audio_video_class) ?>">
  <input type="hidden" name="synth_method_1"   value="<?php echo obfuscate($synth_method_1) ?>">
  <input type="hidden" name="synth_method_2"   value="<?php echo obfuscate($synth_method_2) ?>">
  <input type="hidden" name="video_path"       value="<?php echo obfuscate($video_path) ?>">
  <input type="hidden" name="audio_path_1"     value="<?php echo obfuscate($audio_path_1) ?>">
  <input type="hidden" name="audio_path_2"     value="<?php echo obfuscate($audio_path_2) ?>">
  <input type="hidden" name="video_basename"   value="<?php echo obfuscate($video_basename) ?>">
  <input type="hidden" name="audio_basename_1" value="<?php echo obfuscate($audio_basename_1) ?>">
  <input type="hidden" name="audio_basename_2" value="<?php echo obfuscate($audio_basename_2) ?>">
  <input type="hidden" name="start_epoch"      value="<?php echo obfuscate(strval(time())) ?>">

  <?php make_video_player_with_2_sources("video_player", $audio_path_1, $audio_path_2, $video_path); ?>

  <button type="submit">Save and Continue</button>
</form>

<!-- style>
video{
 border: 1px solid black;
}
</style -->

<?php print_botom_of_page(); ?>
