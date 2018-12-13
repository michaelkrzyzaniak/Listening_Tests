<?php

  $DB_PATH = "database/answers.db";
  $AUDIO_LOCATION = "audio/";
  $VIDEO_LOCATION = "video/";
  $COOKIE_NAME = "user_id";
  
  $MAX_NUM_RESPONSES = 30;
  
  date_default_timezone_set("UTC");
  
  require_once("include/word_cloud.php");
  
  /*----------------------------------------------------------*/
  function get_param($name, $is_float=false, $default=NULL)
  {
    $param = $default;
  
    if(isset($_POST[$name]))
      $param = $_POST[$name];
      
    if($param != NULL)
      {
        if($is_float)
          {
            $param = filter_var($param, FILTER_VALIDATE_FLOAT);
          }
        else
          {
            $param = unobfuscate($param);
            if($param == "NULL")
              $param = NULL;
            else if($param == "")
              $param = $default;
            //done in unobfuscate
            //$param = filter_var($param, FILTER_SANITIZE_STRING);
          }
      }
  
    return $param;
  }

  /*----------------------------------------------------------*/
  function open_db()
  {
    global $DB_PATH;
    $db = new SQLite3($DB_PATH);
    initalize_db($db);
    return $db;
  }  

  /*----------------------------------------------------------*/
  function close_db($db)
  {
    $db->close;
  }  

  /*----------------------------------------------------------*/
  function initalize_db($db)
  {
    $str  = "CREATE TABLE IF NOT EXISTS 'questionnaire'(";
    $str .= "id INTEGER PRIMARY KEY AUTOINCREMENT,";
    $str .= "cookie_id TEXT,";
    $str .= "speaker_setup TEXT,";
    $str .= "is_sound_designer TEXT,";
    $str .= "is_hearing_impared TEXT,";
    $str .= "has_musical_training TEXT,";
    $str .= "is_noisy_environment TEXT,";
    $str .= "start_epoch TEXT,";
    $str .= "end_epoch TEXT,";
    $str .= "duration TEXT"; //comma
    $str .= ");";
    $db->exec($str);
    /* make sure there is only one questionairre response per cookie id */
    $db->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_title_cookie_id ON questionnaire (cookie_id);");
  
    $str  = "CREATE TABLE IF NOT EXISTS 'confusion_matrix'(";
    $str .= "id INTEGER PRIMARY KEY AUTOINCREMENT,";
    $str .= "cookie_id TEXT,";
    $str .= "audio_class TEXT,";
    $str .= "synth_method TEXT,";
    $str .= "audio_basename TEXT,";
    $str .= "audio_path TEXT,";
    $str .= "available_audio_classes TEXT,";
    $str .= "available_synth_methods TEXT,";
    $str .= "available_audio_files TEXT,";
    $str .= "start_epoch TEXT,";
    $str .= "end_epoch TEXT,";
    $str .= "duration TEXT,";
    $str .= "user_response TEXT"; //comma
    $str .= ");";
    $db->exec($str);

    $str  = "CREATE TABLE IF NOT EXISTS 'discrimination'(";
    $str .= "id INTEGER PRIMARY KEY AUTOINCREMENT,";
    $str .= "cookie_id TEXT,";
    $str .= "audio_class TEXT,";
    $str .= "synth_method TEXT,";
    $str .= "audio_basename TEXT,";
    $str .= "audio_path TEXT,";
    $str .= "start_epoch TEXT,";
    $str .= "end_epoch TEXT,";
    $str .= "duration TEXT,";
    $str .= "user_response TEXT"; //comma
    $str .= ");";
    $db->exec($str);

    $str  = "CREATE TABLE IF NOT EXISTS 'ranking'(";
    $str .= "id INTEGER PRIMARY KEY AUTOINCREMENT,";
    $str .= "cookie_id TEXT,";
    $str .= "response_id TEXT,";
    $str .= "audio_class TEXT,";
    $str .= "synth_method TEXT,";
    $str .= "audio_basename TEXT,";
    $str .= "audio_path TEXT,";
    $str .= "available_synth_methods TEXT,";
    $str .= "start_epoch TEXT,";
    $str .= "end_epoch TEXT,";
    $str .= "duration TEXT,";
    $str .= "user_raw_response  TEXT,";
    $str .= "user_response TEXT"; //comma
    $str .= ");";
    $db->exec($str);

    $str  = "CREATE TABLE IF NOT EXISTS 'word_cloud'(";
    $str .= "id INTEGER PRIMARY KEY AUTOINCREMENT,";
    $str .= "cookie_id TEXT,";
    $str .= "audio_class TEXT,";
    $str .= "synth_method TEXT,";
    $str .= "audio_basename TEXT,";
    $str .= "audio_path TEXT,";
    $str .= "start_epoch TEXT,";
    $str .= "end_epoch TEXT,";
    $str .= "duration TEXT,";
    $str .= "user_response TEXT"; //comma
    $str .= ");";
    $db->exec($str);

    $str  = "CREATE TABLE IF NOT EXISTS 'video_coherence'(";
    $str .= "id INTEGER PRIMARY KEY AUTOINCREMENT,";
    $str .= "cookie_id TEXT,";
    $str .= "video_class TEXT,";
    $str .= "winner_audio_class TEXT,";
    $str .= "loser_audio_class TEXT,";
    $str .= "winner_synth_method TEXT,";
    $str .= "loser_synth_method TEXT,";
    $str .= "video_path TEXT,";
    $str .= "winner_audio_path TEXT,";
    $str .= "loser_audio_path TEXT,";
    $str .= "video_basename TEXT,";
    $str .= "winner_audio_basename TEXT,";
    $str .= "loser_audio_basename TEXT,";
    $str .= "start_epoch TEXT,";
    $str .= "end_epoch TEXT,";
    $str .= "duration TEXT,";
    $str .= "user_response TEXT"; //comma
    $str .= ");";
    $db->exec($str);
  }

  /*----------------------------------------------------------*/
  function save_confusion_matrix_response($db)
  {
    global $_COOKIE;
    global $COOKIE_NAME;
    $str  = "INSERT INTO confusion_matrix (";
    $str .= "cookie_id,";
    $str .= "audio_class,";
    $str .= "synth_method,";
    $str .= "audio_basename,";
    $str .= "audio_path,";
    $str .= "available_audio_classes,";
    $str .= "available_synth_methods,";
    $str .= "available_audio_files,";
    $str .= "start_epoch,";
    $str .= "end_epoch,";
    $str .= "duration,";
    $str .= "user_response"; //comma
    $str .= ") VALUES (";

    $str .= ":cookie_id,";
    $str .= ":audio_class,";
    $str .= ":synth_method,";
    $str .= ":audio_basename,";
    $str .= ":audio_path,";
    $str .= ":available_audio_classes,";
    $str .= ":available_synth_methods,";
    $str .= ":available_audio_files,";
    $str .= ":start_epoch,";
    $str .= ":end_epoch,";
    $str .= ":duration,";
    $str .= ":user_response"; //comma
    $str .= ");";
  
    $stmt = $db->prepare($str);

    $start_epoch =  get_param("start_epoch");
    $end_epoch   =  strval(time());
    $duration    = get_duration($start_epoch, $end_epoch);
  
    $stmt->bindValue(':cookie_id'              , $_COOKIE[$COOKIE_NAME], SQLITE3_TEXT);
    $stmt->bindValue(':audio_class'            , get_param("audio_class"), SQLITE3_TEXT);
    $stmt->bindValue(':synth_method'           , get_param("synth_method"), SQLITE3_TEXT);
    $stmt->bindValue(':audio_basename'         , get_param("audio_basename"), SQLITE3_TEXT);
    $stmt->bindValue(':audio_path'             , get_param("audio_path"), SQLITE3_TEXT);
    $stmt->bindValue(':available_audio_classes', get_param("available_audio_classes"), SQLITE3_TEXT);
    $stmt->bindValue(':available_synth_methods', get_param("available_synth_methods"), SQLITE3_TEXT);
    $stmt->bindValue(':available_audio_files'  , get_param("available_audio_files"), SQLITE3_TEXT);
    $stmt->bindValue(':start_epoch'            , $start_epoch, SQLITE3_TEXT);
    $stmt->bindValue(':end_epoch'              , $end_epoch, SQLITE3_TEXT);
    $stmt->bindValue(':duration'               , $duration, SQLITE3_TEXT);
    $stmt->bindValue(':user_response'          , get_param("user_response"), SQLITE3_TEXT);

    $stmt->execute();
  }
  
  /*----------------------------------------------------------*/
  function save_discrimination_response($db)
  {
    global $_COOKIE;
    global $COOKIE_NAME;
    $str  = "INSERT INTO discrimination (";
    $str .= "cookie_id,";
    $str .= "audio_class,";
    $str .= "synth_method,";
    $str .= "audio_basename,";
    $str .= "audio_path,";
    $str .= "start_epoch,";
    $str .= "end_epoch,";
    $str .= "duration,";
    $str .= "user_response"; //comma
    $str .= ") VALUES (";

    $str .= ":cookie_id,";
    $str .= ":audio_class,";
    $str .= ":synth_method,";
    $str .= ":audio_basename,";
    $str .= ":audio_path,";
    $str .= ":start_epoch,";
    $str .= ":end_epoch,";
    $str .= ":duration,";
    $str .= ":user_response"; //comma
    $str .= ");";
  
    $stmt = $db->prepare($str);

    $start_epoch = get_param("start_epoch");
    $end_epoch   = strval(time());
    $duration    = get_duration($start_epoch, $end_epoch);
  
    $stmt->bindValue(':cookie_id'              , $_COOKIE[$COOKIE_NAME], SQLITE3_TEXT);
    $stmt->bindValue(':audio_class'            , get_param("audio_class"), SQLITE3_TEXT);
    $stmt->bindValue(':synth_method'           , get_param("synth_method"), SQLITE3_TEXT);
    $stmt->bindValue(':audio_basename'         , get_param("audio_basename"), SQLITE3_TEXT);
    $stmt->bindValue(':audio_path'             , get_param("audio_path"), SQLITE3_TEXT);
    $stmt->bindValue(':start_epoch'            , $start_epoch, SQLITE3_TEXT);
    $stmt->bindValue(':end_epoch'              , $end_epoch, SQLITE3_TEXT);
    $stmt->bindValue(':duration'               , $duration, SQLITE3_TEXT);
    $stmt->bindValue(':user_response'          , get_param("user_response"), SQLITE3_TEXT);

    $stmt->execute();
  }
 
  /*----------------------------------------------------------*/
  function save_ranking_response($db)
  {
    global $_POST;
    global $_COOKIE;
    global $COOKIE_NAME;
    $str  = "INSERT INTO ranking (";
    $str .= "cookie_id,";
    $str .= "response_id,";
    $str .= "audio_class,";
    $str .= "synth_method,";
    $str .= "audio_basename,";
    $str .= "audio_path,";
    $str .= "available_synth_methods,";
    $str .= "start_epoch,";
    $str .= "end_epoch,";
    $str .= "duration,";
    $str .= "user_raw_response,";
    $str .= "user_response"; //comma
    $str .= ") VALUES (";

    $str .= ":cookie_id,";
    $str .= ":response_id,";
    $str .= ":audio_class,";
    $str .= ":synth_method,";
    $str .= ":audio_basename,";
    $str .= ":audio_path,";
    $str .= ":available_synth_methods,";
    $str .= ":start_epoch,";
    $str .= ":end_epoch,";
    $str .= ":duration,";
    $str .= ":user_raw_response,";
    $str .= ":user_response"; //comma
    $str .= ");";
  
    $stmt = $db->prepare($str);

    $start_epoch = get_param("start_epoch");
    $end_epoch   = strval(time());
    $duration    = get_duration($start_epoch, $end_epoch);
    $response_id = $_COOKIE[$COOKIE_NAME] . "_" . uniqid();
    $responses   = $_POST['user_responses'];
    $basenames   = $_POST['audio_basenames'];
    $audio_paths = $_POST['audio_file_paths'];
    $rank        = 0;
    asort($responses);
  
    $stmt->bindValue(':cookie_id'              , $_COOKIE[$COOKIE_NAME], SQLITE3_TEXT);
    $stmt->bindValue(':response_id'            , $response_id, SQLITE3_TEXT);
    $stmt->bindValue(':audio_class'            , get_param("audio_class"), SQLITE3_TEXT);
    $stmt->bindValue(':available_synth_methods', get_param("available_synth_methods"), SQLITE3_TEXT);
    $stmt->bindValue(':start_epoch'            , $start_epoch, SQLITE3_TEXT);
    $stmt->bindValue(':end_epoch'              , $end_epoch, SQLITE3_TEXT);
    $stmt->bindValue(':duration'               , $duration, SQLITE3_TEXT);

    foreach($responses as $synth_method => $raw_response)
      {
        $stmt->bindValue(':synth_method'           , unobfuscate($synth_method), SQLITE3_TEXT);
        $stmt->bindValue(':audio_basename'         , unobfuscate($basenames[$synth_method]), SQLITE3_TEXT);
        $stmt->bindValue(':audio_path'             , unobfuscate($audio_paths[$synth_method]), SQLITE3_TEXT);
        $stmt->bindValue(':user_raw_response'      , $raw_response, SQLITE3_TEXT);
        $stmt->bindValue(':user_response'          , $rank, SQLITE3_TEXT);
        ++$rank;
      
        $stmt->execute();
      }
  }

  /*----------------------------------------------------------*/
  function save_word_cloud_response($db)
  {
    global $_COOKIE;
    global $COOKIE_NAME;
    global $_POST;
  
    $str  = "INSERT INTO word_cloud (";
    $str .= "cookie_id,";
    $str .= "audio_class,";
    $str .= "synth_method,";
    $str .= "audio_basename,";
    $str .= "audio_path,";
    $str .= "start_epoch,";
    $str .= "end_epoch,";
    $str .= "duration,";
    $str .= "user_response"; //comma
    $str .= ") VALUES (";

    $str .= ":cookie_id,";
    $str .= ":audio_class,";
    $str .= ":synth_method,";
    $str .= ":audio_basename,";
    $str .= ":audio_path,";
    $str .= ":start_epoch,";
    $str .= ":end_epoch,";
    $str .= ":duration,";
    $str .= ":user_response"; //comma
    $str .= ");";
  
    $stmt = $db->prepare($str);

    $start_epoch = get_param("start_epoch");
    $end_epoch   = strval(time());
    $duration    = get_duration($start_epoch, $end_epoch);
    $user_response = filter_var($_POST["user_response"], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
    $user_response = trim($user_response);
    $user_response = strtolower($user_response);
  
    if($user_response == "") return;
  
    $stmt->bindValue(':cookie_id'              , $_COOKIE[$COOKIE_NAME], SQLITE3_TEXT);
    $stmt->bindValue(':audio_class'            , get_param("audio_class"), SQLITE3_TEXT);
    $stmt->bindValue(':synth_method'           , get_param("synth_method"), SQLITE3_TEXT);
    $stmt->bindValue(':audio_basename'         , get_param("audio_basename"), SQLITE3_TEXT);
    $stmt->bindValue(':audio_path'             , get_param("audio_path"), SQLITE3_TEXT);
    $stmt->bindValue(':start_epoch'            , $start_epoch, SQLITE3_TEXT);
    $stmt->bindValue(':end_epoch'              , $end_epoch, SQLITE3_TEXT);
    $stmt->bindValue(':duration'               , $duration, SQLITE3_TEXT);
    $stmt->bindValue(':user_response'          , $user_response, SQLITE3_TEXT);
  
    $stmt->execute();
  }

  /*----------------------------------------------------------*/
  function save_video_coherence_response($db)
  {
    global $_COOKIE;
    global $COOKIE_NAME;
    $str  = "INSERT INTO video_coherence (";
    $str .= "cookie_id,";
    $str .= "video_class,";
    $str .= "winner_audio_class,";
    $str .= "loser_audio_class,";
    $str .= "winner_synth_method,";
    $str .= "loser_synth_method,";
    $str .= "video_path,";
    $str .= "winner_audio_path,";
    $str .= "loser_audio_path,";
    $str .= "video_basename,";
    $str .= "winner_audio_basename,";
    $str .= "loser_audio_basename,";
    $str .= "start_epoch,";
    $str .= "end_epoch,";
    $str .= "duration,";
    $str .= "user_response"; //comma
    $str .= ") VALUES (";

    $str .= ":cookie_id,";
    $str .= ":video_class,";
    $str .= ":winner_audio_class,";
    $str .= ":loser_audio_class,";
    $str .= ":winner_synth_method,";
    $str .= ":loser_synth_method,";
    $str .= ":video_path,";
    $str .= ":winner_audio_path,";
    $str .= ":loser_audio_path,";
    $str .= ":video_basename,";
    $str .= ":winner_audio_basename,";
    $str .= ":loser_audio_basename,";
    $str .= ":start_epoch,";
    $str .= ":end_epoch,";
    $str .= ":duration,";
    $str .= ":user_response"; //comma
    $str .= ");";
  
    $stmt = $db->prepare($str);

    $user_response         = get_param("user_response");
    $response_was_1        = $user_response == "Audio 1";
    $winner_audio_class    = ($response_was_1) ? get_param("audio_class_1")    : get_param("audio_class_2");
    $loser_audio_class     = ($response_was_1) ? get_param("audio_class_2")    : get_param("audio_class_1");
    $winner_synth_method   = ($response_was_1) ? get_param("synth_method_1")   : get_param("synth_method_2");
    $loser_synth_method    = ($response_was_1) ? get_param("synth_method_2")   : get_param("synth_method_1");
    $winner_audio_path     = ($response_was_1) ? get_param("audio_path_1")     : get_param("audio_path_2");
    $loser_audio_path      = ($response_was_1) ? get_param("audio_path_2")     : get_param("audio_path_1");
    $winner_audio_basename = ($response_was_1) ? get_param("audio_basename_1") : get_param("audio_basename_2");
    $loser_audio_basename  = ($response_was_1) ? get_param("audio_basename_2") : get_param("audio_basename_1");
    $start_epoch           = get_param("start_epoch");
    $end_epoch             = strval(time());
    $duration              = get_duration($start_epoch, $end_epoch);
  
    $stmt->bindValue(':cookie_id'              , $_COOKIE[$COOKIE_NAME]     , SQLITE3_TEXT);
    $stmt->bindValue(':video_class'            , get_param("video_class")   , SQLITE3_TEXT);
    $stmt->bindValue(':winner_audio_class'     , $winner_audio_class        , SQLITE3_TEXT);
    $stmt->bindValue(':loser_audio_class'      , $loser_audio_class         , SQLITE3_TEXT);
    $stmt->bindValue(':winner_synth_method'    , $winner_synth_method       , SQLITE3_TEXT);
    $stmt->bindValue(':loser_synth_method'     , $loser_synth_method        , SQLITE3_TEXT);
    $stmt->bindValue(':video_path'             , get_param("video_path")    , SQLITE3_TEXT);
    $stmt->bindValue(':winner_audio_path'      , $winner_audio_path         , SQLITE3_TEXT);
    $stmt->bindValue(':loser_audio_path'       , $loser_audio_path          , SQLITE3_TEXT);
    $stmt->bindValue(':video_basename'         , get_param("video_basename"), SQLITE3_TEXT);
    $stmt->bindValue(':winner_audio_basename'  , $winner_audio_basename     , SQLITE3_TEXT);
    $stmt->bindValue(':loser_audio_basename'   , $loser_audio_basename      , SQLITE3_TEXT);
    $stmt->bindValue(':start_epoch'            , $start_epoch               , SQLITE3_TEXT);
    $stmt->bindValue(':end_epoch'              , $end_epoch                 , SQLITE3_TEXT);
    $stmt->bindValue(':duration'               , $duration                  , SQLITE3_TEXT);
    $stmt->bindValue(':user_response'          , get_param("user_response") , SQLITE3_TEXT);

    $stmt->execute();
  }
 
   /*----------------------------------------------------------*/
  function save_questionnaire($db)
  {
    global $_COOKIE;
    global $COOKIE_NAME;
    /* The same user shouldn't end up back on the questionanaire page */
    /* unless they really go hawire on the back button */
    /* In which case we'll just keep the most recent response */
    $str  = "INSERT OR REPLACE INTO questionnaire (";
    $str .= "cookie_id,";
    $str .= "speaker_setup,";
    $str .= "is_sound_designer,";
    $str .= "is_hearing_impared,";
    $str .= "has_musical_training,";
    $str .= "is_noisy_environment,";
    $str .= "start_epoch,";
    $str .= "end_epoch,";
    $str .= "duration"; //comma
    $str .= ") VALUES (";

    $str .= ":cookie_id,";
    $str .= ":speaker_setup,";
    $str .= ":is_sound_designer,";
    $str .= ":is_hearing_impared,";
    $str .= ":has_musical_training,";
    $str .= ":is_noisy_environment,";
    $str .= ":start_epoch,";
    $str .= ":end_epoch,";
    $str .= ":duration"; //comma
    $str .= ");";

    $stmt = $db->prepare($str);

    $speaker_setup         = get_param("speaker_setup");
    $valid_speaker_setups = ["earbuds", "headphones", "phone_tablet_internal_speakers", "laptop_desktop_internal_speakers", "exernal_speakers", "car_stereo", "hifi_speakers", "studio_monitors"];
    if(!in_array($speaker_setup, $valid_speaker_setups))
      $speaker_setup = "invalid_speaker_setup";
  
    $is_sound_designer     = isset($_POST['is_sound_designer'])    ? "true" : "false";
    $is_hearing_impared    = isset($_POST['is_hearing_impared'])   ? "true" : "false";
    $has_musical_training  = isset($_POST['has_musical_training']) ? "true" : "false";
    $is_noisy_environment  = isset($_POST['is_noisy_environment']) ? "true" : "false";
  
    $start_epoch           = get_param("start_epoch");
    $end_epoch             = strval(time());
    $duration              = get_duration($start_epoch, $end_epoch);
  
    $stmt->bindValue(':cookie_id'              , $_COOKIE[$COOKIE_NAME]     , SQLITE3_TEXT);
    $stmt->bindValue(':speaker_setup'          , $speaker_setup             , SQLITE3_TEXT);
    $stmt->bindValue(':is_sound_designer'      , $is_sound_designer         , SQLITE3_TEXT);
    $stmt->bindValue(':is_hearing_impared'     , $is_hearing_impared        , SQLITE3_TEXT);
    $stmt->bindValue(':has_musical_training'   , $has_musical_training      , SQLITE3_TEXT);
    $stmt->bindValue(':is_noisy_environment'   , $is_noisy_environment      , SQLITE3_TEXT);
    $stmt->bindValue(':start_epoch'            , $start_epoch               , SQLITE3_TEXT);
    $stmt->bindValue(':end_epoch'              , $end_epoch                 , SQLITE3_TEXT);
    $stmt->bindValue(':duration'               , $duration                  , SQLITE3_TEXT);

    $stmt->execute();
  
  }

  /*----------------------------------------------------------*/
  //this is 1-indxed, e.g. it is the current question number, not the number of previous responses
  function user_has_completed_questionnaire($db)
  {
    global $_COOKIE;
    global $COOKIE_NAME;

    $result += $db->querySingle("SELECT id FROM questionnaire WHERE cookie_id='$_COOKIE[$COOKIE_NAME]';");
  
    return $result > 0;
  }

  /*----------------------------------------------------------*/
  //this is 1-indxed, e.g. it is the current question number, not the number of previous responses
  function get_user_response_count($db, $table_name /*ignored*/)
  {
    global $_COOKIE;
    global $COOKIE_NAME;
  
    $result = 0;
  
    //for a single test
    //if($table_name == "ranking")
    //  $result = $db->querySingle("SELECT COUNT (DISTINCT response_id) as count FROM $table_name WHERE cookie_id='$_COOKIE[$COOKIE_NAME]';");
    //else
    //  $result = $db->querySingle("SELECT COUNT(*) as count FROM $table_name WHERE cookie_id='$_COOKIE[$COOKIE_NAME]';");
  
    //for all tests
  
    $result += $db->querySingle("SELECT COUNT (DISTINCT response_id) as count FROM ranking WHERE cookie_id='$_COOKIE[$COOKIE_NAME]';");
    $result += $db->querySingle("SELECT COUNT(*) as count FROM confusion_matrix WHERE cookie_id='$_COOKIE[$COOKIE_NAME]';");
    $result += $db->querySingle("SELECT COUNT(*) as count FROM discrimination WHERE cookie_id='$_COOKIE[$COOKIE_NAME]';");
    $result += $db->querySingle("SELECT COUNT(*) as count FROM video_coherence WHERE cookie_id='$_COOKIE[$COOKIE_NAME]';");
    $result += $db->querySingle("SELECT COUNT(*) as count FROM word_cloud WHERE cookie_id='$_COOKIE[$COOKIE_NAME]';");
    return $result + 1;
  }
  
  /*----------------------------------------------------------*/
  function print_user_response_count($response_count, $max_responses)
  {
    echo "<div class='response_count' style='margin:auto; text-align:center; font-size:0.7em;'>";
    echo  "<small>$response_count out of $max_responses</small>";
    echo "</div>";
  }

  /*----------------------------------------------------------*/
  function redirect_to_another_test($response_count, $max_responses)
  {
    $pages = array();
    $pages[] = "classification.php";
    $pages[] = "discrimination.php";
    $pages[] = "ranking.php";
    $pages[] = "video_coherence.php";
    $pages[] = "word_cloud.php";
  
    $target = "session_finished.php";
  
    $current = basename($_SERVER['PHP_SELF']);
  
    if($response_count < $max_responses)
      do{
          $i = rand(0, count($pages) - 1);
          $target = $pages[$i];
         }while($current == $target);
  
    header("Location: $target");
  }
  
  /*----------------------------------------------------------*/
  function make_cookie_if_necessary()
  {
    global $_COOKIE;
    global $COOKIE_NAME;
  
    if(!isset($_COOKIE[$COOKIE_NAME]))
      {
        $cookie_value = uniqid();
        setcookie($COOKIE_NAME, $cookie_value, time() + (86400 * 30), "/");
        $_COOKIE[$COOKIE_NAME] = $cookie_value;
      }
  }

  /*----------------------------------------------------------*/
  function delete_cookie()
  {
    global $COOKIE_NAME;
    setcookie($COOKIE_NAME, "", time() - 3600, "/");
  }
  
  /*----------------------------------------------------------*/
  function get_trial_id()
  {
    global $_COOKIE;
    global $COOKIE_NAME;
  
    return strval(time()) + $_COOKIE[$COOKIE_NAME];
  
  }
  
  /*----------------------------------------------------------*/
  function make_audio_player($name, $control_type, $file_path)
  {
    $file_basename = obfuscate(basename($file_path));
    $file_path = obfuscate($file_path);
    $name = obfuscate($name);
    $javascript_name = preg_replace("/[^A-Za-z ]/", '', $name);
    echo "<audio id='$name' style='display:none;' controls volume='1'>";
    echo "  <source src='' type='audio/wav'>";
    echo "</audio>";
    echo "<script>";
    echo "  var $javascript_name = new Audio_File_Player('$file_path', '$name', true, false);";
    echo "  $javascript_name.set_gain(1);";
    echo "</script>";
    //global_audio_did_play js variable is defined in print_submit_button below
    //(cannot submit form unless audio was played).
    //This dosen't work so well for sliders, but we can still tell if they adjusted the sliders
    //and we know how long they spent on the page.
    if($control_type == "buttons")
      {
        echo "<button type='button' onclick='$javascript_name.play(); global_audio_did_play=true;'>Play Audio</button>";
        echo "<button type='button' onclick='$javascript_name.stop();'>Stop Audio</button>";
      }
    else if($control_type == "slider")
      {
        echo "<input class='unselected' type='range' min='0' max='1' step='0.01' value='0.5' name='user_responses[$name]' onmouseover='$javascript_name.play(); global_audio_did_play=true;  this.className=\"selected\"' onmouseout='$javascript_name.stop();this.className=\"unselected\"' ontouchstart='this.onmouseover()' ontouchend='this.onmouseout()'>";
        echo "<input type='hidden' name='audio_file_paths[$name]' value='$file_path'>";
        echo "<input type='hidden' name='audio_basenames[$name]' value='$file_basename'>";
      }

    else if($control_type == "none")
      {
      
      }
    return $javascript_name;
  }

  /*----------------------------------------------------------*/
  function make_video_player_with_2_sources($name, $audio_file_path_1, $audio_file_path_2, $video_file_path)
  {
    $v_path       = obfuscate($video_file_path);
    $v_name       = obfuscate($name . "v");
    $a_name_1     = obfuscate($name . "a1");
    $a_name_2     = obfuscate($name . "a2");

    $a_js_name_1 = make_audio_player($a_name_1, 'none', $audio_file_path_1);
    $a_js_name_2 = make_audio_player($a_name_2, 'none', $audio_file_path_2);
    $v_js_name   = preg_replace("/[^A-Za-z ]/", '', $v_name);
  
  
    //echo "<div style='border:1px solid black; margin:auto; width:400px; border-radius:20px; background-color:#EEE'>";
    echo "<div style='width:400px; margin:auto;'>";
    $jses = array("play_one_audio_and_stop_the_other($a_js_name_1, $a_js_name_2, $v_js_name)", "play_one_audio_and_stop_the_other($a_js_name_2, $a_js_name_1, $v_js_name)");
    //the save_video_coherence_response assuemes the first response will be called "Audio 1" exactly.
    print_radio_buttons(array("Audio 1", "Audio 2"), "user_response", "2", $jses);
    echo "</div>";
    echo "<button type='button' onclick='$a_js_name_2.stop(); $a_js_name_1.stop(); $v_js_name.stop();'>Stop</button>";
    //echo "</div>";
  
    //we are using images instead of video. The difference is handled in Video_File_Player.js
    //js variable global_audio_did_play defined in the php print_submit_button function below
    //echo "<video id='$v_name' width='100%' height='auto'></video>";
    echo "<div id='$v_name' width='100%' height='auto'></div>";
    echo "<script>";
    echo "  var $v_js_name = new Video_File_Player('$v_path', '$v_name');";
    echo "  function play_one_audio_and_stop_the_other(play, stop, video)";
    echo "  {";
    echo "    if(!play.playing)";
    echo "      video.play();";
    echo "     stop.stop();";
    echo "     play.play();";
    echo "     global_audio_did_play = true;";
    echo "  }";
    echo "</script>";
  }

  /*----------------------------------------------------------*/
  function get_duration($start, $end)
  {
    return strval((int)$end - (int)$start);
  }

  /*----------------------------------------------------------*/
  function obfuscate($unobfuscated)
  {
    return base64_encode($unobfuscated);
  }

  /*----------------------------------------------------------*/
  function unobfuscate($obfuscated)
  {
    $un = base64_decode($obfuscated, $strict = true);
    return filter_var($un, FILTER_SANITIZE_STRING);
  }

  /*----------------------------------------------------------*/
  function get_directory_listing($dir, $files_type, $glob="*")
  {
    $filter = ($files_type == "directories") ? "is_dir" : "is_file";
  
    if(substr($dir, -1) != "/")
      $dir .= "/";
    $paths = array_filter(glob($dir . $glob), $filter);
    $listing = array();
    foreach($paths as $path)
      $listing[] = basename($path);
    return $listing;
  }

  /*----------------------------------------------------------*/
  function random_element_in_array($a)
  {
    if(count($a) <= 0)
      return "";
  
    $r = array_rand($a);
    return $a[$r];
  }
  
  /*----------------------------------------------------------*/
  function array_to_string($a)
  {
    return implode("|", $a);
  }

  /*----------------------------------------------------------*/
  function string_to_array($s)
  {
    return explode("|", $s);
  }

  /*----------------------------------------------------------*/
  function get_audio_classes()
  {
    global $AUDIO_LOCATION;
    return get_directory_listing($AUDIO_LOCATION, "directories");
  }

  /*----------------------------------------------------------*/
  function get_video_classes()
  {
    global $VIDEO_LOCATION;
    return get_directory_listing($VIDEO_LOCATION, "directories");
  }

  /*----------------------------------------------------------*/
  function get_audio_classes_string()
  {
    return array_to_string(get_audio_classes());
  }

  /*----------------------------------------------------------*/
  function get_video_classes_string()
  {
    return array_to_string(get_video_classes());
  }

  /*----------------------------------------------------------*/
  function get_random_audio_class()
  {
    return random_element_in_array(get_audio_classes());
  }

  /*----------------------------------------------------------*/
  function get_random_video_class()
  {
    return random_element_in_array(get_video_classes());
  }

  /*----------------------------------------------------------*/
  function get_synth_methods($audio_class_name)
  {
    global $AUDIO_LOCATION;
    return get_directory_listing($AUDIO_LOCATION . $audio_class_name, "directories");
  }

  /*----------------------------------------------------------*/
  function get_synth_methods_string($audio_class_name)
  {
    return array_to_string(get_synth_methods($audio_class_name));
  }
  
  /*----------------------------------------------------------*/
  function get_random_synth_method($audio_class_name)
  {
    return random_element_in_array(get_synth_methods($audio_class_name));
  }

  /*----------------------------------------------------------*/
  function get_audio_files($audio_class_name, $synth_method)
  {
    global $AUDIO_LOCATION;
    return get_directory_listing($AUDIO_LOCATION . $audio_class_name . "/" . $synth_method, "files", "*.wav");
  }

  /*----------------------------------------------------------*/
  function get_video_files($video_class_name)
  {
    global $VIDEO_LOCATION;
    return get_directory_listing($VIDEO_LOCATION . $video_class_name, "files");
  }

  /*----------------------------------------------------------*/
  function get_audio_files_string($audio_class_name, $synth_method)
  {
    return array_to_string(get_audio_files($audio_class_name, $synth_method));
  }

  /*----------------------------------------------------------*/
  function get_video_files_string($video_class_name)
  {
    return array_to_string(get_video_files($video_class_name));
  }
  
  /*----------------------------------------------------------*/
  function get_random_audio_file($audio_class_name, $synth_method)
  {
    return random_element_in_array(get_audio_files($audio_class_name, $synth_method));
  }

  /*----------------------------------------------------------*/
  function get_random_video_file($video_class_name)
  {
    return random_element_in_array(get_video_files($video_class_name));
  }

  /*----------------------------------------------------------*/
  function get_audio_file_path($audio_class_name, $synth_method, $file_name)
  {
    global $AUDIO_LOCATION;
    return $AUDIO_LOCATION . $audio_class_name . "/" . $synth_method . "/" . $file_name;
  }

  /*----------------------------------------------------------*/
  function get_video_file_path($video_class_name, $file_name)
  {
    global $VIDEO_LOCATION;
    return $VIDEO_LOCATION . $video_class_name . "/" . $file_name;
  }

   /*----------------------------------------------------------*/
  function get_unique_audio_classes_synth_methods_audio_paths($db, $table)
  {
    $result = array();
    $audio_classes = $db->query("SELECT DISTINCT audio_class FROM $table");
    while(($audio_class = $audio_classes->fetchArray(SQLITE3_ASSOC)) != FALSE)
      $result['audio_class'][] = $audio_class['audio_class'];
    $synth_methods = $db->query("SELECT DISTINCT synth_method FROM $table");
    while(($synth_method = $synth_methods->fetchArray(SQLITE3_ASSOC)) != FALSE)
      $result['synth_method'][] = $synth_method['synth_method'];
    $audio_paths = $db->query("SELECT DISTINCT audio_path FROM $table");
    while(($audio_path = $audio_paths->fetchArray(SQLITE3_ASSOC)) != FALSE)
      $result['audio_path'][] = $audio_path['audio_path'];
  
    return $result;
  }

  /*----------------------------------------------------------*/
  function print_audio_classes_synth_methods_audio_paths_selects_form($a)
  {
    $action = $_SERVER['PHP_SELF'];
    echo "<form method='post' action='$action'>";
  
    foreach($a as $select=>$options)
      {
        array_unshift($options, "NULL");
        print_html_select($options, $select, "");
      }
    echo "</form>";
  }

  /*----------------------------------------------------------*/
  function print_radio_buttons($options, $name="user_response", $columns=1, $onclick=NULL)
  {
    if($columns == 2)
      $columns = "two_columns";
    else
      $columns = "one_column";
  
    echo "<div class='radio_container $columns'><ul>";
    $i = 0;
    foreach($options as $option)
      {
        $value = obfuscate($option);
        $click = "";
        if($onclick != NULL)
          if($onclick[$i])
             $click = " onclick='" . $onclick[$i] . "'";
        ++$i;
        echo "<li$click>";
        echo "<input type='radio' id='$option' name='$name' required='required' value='$value'>";
        echo "<label for='$option'>$option</label>";
        echo "<div class='check'></div>";
        echo "</li>";
      }
    echo "</ul></div>";
  }

  /*----------------------------------------------------------*/
  function print_audio_classes_radio_buttons()
  {
    $audio_classes = get_audio_classes();
  
    echo "<div style='width:600px; margin:auto;'>";
    print_radio_buttons($audio_classes, "user_response", "2");
    echo "</div>";
  }

  /*----------------------------------------------------------*/
  function print_real_or_synthesized_radio_buttons()
  {
    $options = array();
    $options[] = "Real";
    $options[] = "Synthesized";
  
    echo "<div style='width:300px; margin:auto;'>";
    print_radio_buttons($options);
    echo "</div>";
  }
  
  /*----------------------------------------------------------*/
  function print_submit_button()
  {
    // the global_audio_did_play will be set to true in the script defined in
    // make_audio_player above
    // or make_video_player_with_2_sources above
    echo "<script>";
    echo "var global_audio_did_play = false;\r\n";
    echo "function check_audio_did_play(e)\r\n";
    echo "{\r\n";
    echo "if(!global_audio_did_play){\r\n";
    echo "  alert('Please listen to the audio before continuing');\r\n";
    echo "  e = e || window.event;\r\n";
    echo "  e.preventDefault();\r\n";
    echo "  return false\r\n";
    echo "}";
    echo "else {return true;}";
    echo "}";
    echo "</script>";
    echo "<button type='submit' onclick='check_audio_did_play(event)'>Save and Continue</button>";
  }
  
  /*----------------------------------------------------------*/
  /* indices may be 'audio_path' | 'synth_method' | 'audio_class' | cookie_id. $second_index may be NULL */
  function get_responses($db, $table, $first_index, $second_index)
  {
    $result=array();
    $rows  = $db->query("SELECT * FROM $table");
  
    while(($row = $rows->fetchArray(SQLITE3_ASSOC)) != FALSE)
      {
        if($second_index === NULL)
          {
            $a = "Summary";
            $b = $row[$first_index];
          }
        else
          {
            $a = $row[$first_index];
            $b = $row[$second_index];
          }
        $a = ($second_index === NULL) ? "Summary" :  $row[$first_index];
        $b = ($second_index === NULL) ? $row[$first_index] : $row[$second_index];
        $c = $row['user_response'];
      
        if(!isset($result[$a])) $result[$a] = array();
        if(!isset($result[$a][$b])) $result[$a][$b] = array();

        if(!isset($result[$a][$b]))
          $result[$a][$b][$c] = 1;
        else
          ++$result[$a][$b][$c];
      }
    return $result;
  }
  
  /*----------------------------------------------------------*/
  function print_responses($responses)
  {
    foreach($responses as $header => $table_rows)
      {
        echo "  <table class='confusion_matrix'>";
        echo "    <caption>$header</caption>";
        echo "    <thead>";
        echo "      <tr>";
        echo "      <th style='text-align:right; font-weight:normal'><small><i>User Responses</i></small>&nbsp;&#8594;</th>";
        $column_names = get_unique_array_keys_2d($table_rows);
        foreach($column_names as $column_name)
          echo "      <th>$column_name</th>";
        echo "      </tr>";
        echo "    </thead>";
        echo "    <tbody>";
      
        foreach($table_rows as $row_name => $table_row)
          {
            echo "<tr>";
            echo "<td>$row_name</td>";
            foreach($column_names as $column_name)
              {
                if(!isset($table_row[$column_name]))
                  echo "<td>0</td>";
                else
                  echo "<td>$table_row[$column_name]</td>";
              }
            echo "</tr>";
          }
      
        echo "    </tbody>";
        echo "  </table>";
      }
  }
  
  /*----------------------------------------------------------*/
  function print_word_cloud_responses($db, $audio_class, $synth_method, $audio_path)
  {
    $words = array();
    $and = false;
    $str = "SELECT user_response FROM word_cloud";
    if($audio_class)
      {
        $str .= ($and) ? " AND" : " WHERE";
        $str .= " audio_class=:audio_class";
        $and = true;
      }
    if($synth_method)
      {
        $str .= ($and) ? " AND" : " WHERE";
        $str .= " synth_method=:synth_method";
        $and = true;
      }
    if($audio_path)
      {
        $str .= ($and) ? " AND" : " WHERE";
        $str .= " audio_path=:audio_path";
        $and = true;
      }
  
    $str .= ";";
    $stmt = $db->prepare($str);
  
    if($audio_class) $stmt->bindValue(':audio_class', $audio_class, SQLITE3_TEXT);
    if($synth_method) $stmt->bindValue(':synth_method', $synth_method, SQLITE3_TEXT);
    if($audio_path) $stmt->bindValue(':audio_path', $audio_path, SQLITE3_TEXT);
  
    $rows = $stmt->execute();
    while(($row = $rows->fetchArray(SQLITE3_ASSOC)) != FALSE)
      $words[] = $row['user_response'];
  
    make_word_cloud($words);
  }
  
  /*----------------------------------------------------------*/
  function get_unique_array_keys_2d($array)
  {
    $result = array();
    foreach($array as $a)
      $result = array_merge($result, array_keys($a));
    return array_unique($result);
  }
 
  /*----------------------------------------------------------*/
  function shuffle_and_preserve_keys($list)
  {
    if (!is_array($list)) return $list;

    $keys = array_keys($list);
    shuffle($keys);
    $random = array();
    foreach ($keys as $key)
      $random[$key] = $list[$key];
  
    return $random;
  }

  /*----------------------------------------------------------*/
  function print_html_select($options, $label, $default)
  {
    $selected = get_param($label, false, $default);
    echo "<div class='select_with_label'>";
    echo "<label for='$label'>$label</label>";
    echo "<select name='$label' onchange='this.form.submit()'>";
    foreach($options as $option)
      {
        $s = ($option == $selected) ? " selected" : "";
        $obfuscated_option = obfuscate($option);
        echo "<option value='$obfuscated_option'$s>$option</option>";
      }
    echo "</select>";
    echo "</div>";
  }

  /*----------------------------------------------------------*/
  function print_response_management_form($default_1, $default_2)
  {
    $action = $_SERVER['PHP_SELF'];;
    echo "<form method='post' action='$action'>";
    $first_options = array("synth_method", "audio_class", "audio_path", "cookie_id", "duration");
    print_html_select($first_options, "first_index", $default_1);
    $second_options = array("NULL", "synth_method", "audio_class", "audio_path", "cookie_id", "duration");
    print_html_select($second_options, "second_index", $default_2);
    echo "</form>";
  }

?>
