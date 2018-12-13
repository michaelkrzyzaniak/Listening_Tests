<?php
  require_once("include/scripts.php");
  require_once("include/header_footer.php");
  
  make_cookie_if_necessary();
  
  $db = open_db();
  if(user_has_completed_questionnaire($db))
    redirect_to_another_test($RESPONSE_COUNT, $MAX_NUM_RESPONSES);

 $RESPONSE_COUNT    = get_user_response_count($db, "confusion_matrix");
 
  switch(get_param("action"))
    {
      case "submitted_answer":
        save_questionnaire($db);
        redirect_to_another_test($RESPONSE_COUNT, $MAX_NUM_RESPONSES);
      default: break;
    }
  
  close_db($db);
  
  print_top_of_page("Ambisynth Listening Test 1");
  //echo "<br>Listen to the audio then choose what type of location you think it sounds like.";
?>

<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
  <input type="hidden" name="start_epoch" value="<?php echo obfuscate(strval(time())) ?>">
  <input type="hidden" name="action" value="<?php echo obfuscate('submitted_answer') ?>">
  <h3>Which best describes your playback equipment?<h3>
  <div class='radio_container one_column'>
  <!-- these values are sanitized in php save_questionnaire, so don't edit them -->
    <ul>
      <li>
        <input type='radio' id='earbuds' name='speaker_setup' required='required' value='<?php echo obfuscate("earbuds") ?>'>
        <label for='earbuds'>earbuds</label>
        <div class='check'></div>
      </li>

      <li>
        <input type='radio' id='headphones' name='speaker_setup' required='required' value='<?php echo obfuscate("headphones") ?>'>
        <label for='headphones'>headphones</label>
        <div class='check'></div>
      </li>

      <li>
        <input type='radio' id='phone_tablet_internal_speakers' name='speaker_setup' required='required' value='<?php echo obfuscate("phone_tablet_internal_speakers") ?>'>
        <label for='phone_tablet_internal_speakers'>phone / tablet internal speakers</label>
        <div class='check'></div>
      </li>

      <li>
        <input type='radio' id='laptop_desktop_internal_speakers' name='speaker_setup' required='required' value='<?php echo obfuscate("laptop_desktop_internal_speakers") ?>'>
        <label for='laptop_desktop_internal_speakers'>laptop / computer internal speakers</label>
        <div class='check'></div>
      </li>

      <li>
        <input type='radio' id='exernal_speakers' name='speaker_setup' required='required' value='<?php echo obfuscate("exernal_speakers") ?>'>
        <label for='exernal_speakers'>external speakers, e.g. bluetooth or wired</label>
        <div class='check'></div>
      </li>

      <li>
        <input type='radio' id='car_stereo' name='speaker_setup' required='required' value='<?php echo obfuscate("car_stereo") ?>'>
        <label for='car_stereo'>car stereo</label>
        <div class='check'></div>
      </li>

      <li>
        <input type='radio' id='hifi_speakers' name='speaker_setup' required='required' value='<?php echo obfuscate("hifi_speakers") ?>'>
        <label for='hifi_speakers'>professional-grade hifi speakers</label>
        <div class='check'></div>
      </li>

      <li>
        <input type='radio' id='studio_monitors' name='speaker_setup' required='required' value='<?php echo obfuscate("studio_monitors") ?>'>
        <label for='studio_monitors'>professional studio monitors</label>
        <div class='check'></div>
      </li>
    </ul>
  </div>

  <h3>Select all that apply<h3>
  <div class='radio_container one_column'>
    <ul>
      <li>
        <input type='checkbox' id='is_sound_designer' name='is_sound_designer'>
        <label for='is_sound_designer'>I am a professional sound designer</label>
        <div class='check'></div>
      </li>

      <li>
        <input type='checkbox' id='is_hearing_impared' name='is_hearing_impared'>
        <label for='is_hearing_impared'>I am hearing-impared</label>
        <div class='check'></div>
      </li>

      <li>
        <input type='checkbox' id='has_musical_training' name='has_musical_training'>
        <label for='has_musical_training'>I have formal musical training</label>
        <div class='check'></div>
      </li>

      <li>
        <input type='checkbox' id='is_noisy_environment' name='is_noisy_environment'>
        <label for='is_noisy_environment'>I am in a noisy environment right now</label>
        <div class='check'></div>
      </li>
    </ul>
  </div>

  <?php print_submit_button() ?>
  <script>global_audio_did_play = true;</script>
</form>

<?php print_botom_of_page(); ?>
