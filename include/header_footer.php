<?php
  function print_top_of_page($page_name)
   {?>
    <!doctype html>
    <html lang="en">
      <head>
        <meta charset="UTF-8">
        <title>Ambisynth Listening Test 1 | CVSSP</title>
        <meta name="author" content="michael krzyzaniak" />
        <meta name="description" content="Ambisynth Listening Test 1">
        <meta name="keywords" content="Michael Krzyzaniak">
        
        <link rel="stylesheet" type="text/css" href="include/styles.css">
        <script src="include/Audio_File_Player.js"></script>
        <script src="include/Video_File_Player.js"></script>
       </head>
      <body>
        <div class="page_div">

<!--
          <a href="index.php"><img class="logo" src="../include/logo.gif" alt="Surrey Logo"></a>
          <h1><?php echo $page_name ?></h1>
          <small>Made at 
            <a href="http://surrey.ac.uk">University of Surrey</a>&apos;s 
            <a href="http://cvssp.org">Centre for Vision, Speech, and Signal Processing</a> in Summer of 2018.
          </small>
          <br /><br />
-->
<!--span style="position:absolute;"><a href="index.php" style="text-decoration:none">&emsp;</a></span-->
          <div class="interface_div">
    <?php
   }


  function print_botom_of_page()
   {?>
          </div><!--interface_div-->
        </div><!--page_div-->
      </body>
    </html>
    <?php
   }
?>
