<?php

function make_word_cloud($words)
{?>

<style>
#angles line, #angles path, #angles circle { stroke: #666; }
#angles text { fill: #333; font-size:8pt;}
#angles path.drag { fill: #666; cursor: move; }
#angles { text-align: center; margin: 0 auto; width: 350px; }

</style>

<div id="vis"></div>

<form id="form">

<p style="position: absolute; right: 0; top: 0" id="status"></p>

<div style="text-align: center">
  <div id="presets"></div>
  <div id="custom-area">
    <p>
    <p><textarea id="text" style="display:none">
<?php
  foreach($words as $word)
    echo $word . " ";
?>
    </textarea>
    <!-- button id="go" type="submit">Go!</button -->
  </div>
</div>

<hr>

<div style="display:inline-block; margin:10px;">
  <p><label>Spiral:</label>
    <label for="archimedean"><input type="radio" name="spiral" id="archimedean" value="archimedean" checked="checked"> Archimedean</label>
    <label for="rectangular"><input type="radio" name="spiral" id="rectangular" value="rectangular"> Rectangular</label>
    </div>
<div style="display:inline-block; margin:10px;">
  <p><label for="scale">Scale:</label>
    <label for="scale-log"><input type="radio" name="scale" id="scale-log" value="log" checked="checked"> log n</label>
    <label for="scale-sqrt"><input type="radio" name="scale" id="scale-sqrt" value="sqrt"> √n</label>
    <label for="scale-linear"><input type="radio" name="scale" id="scale-linear" value="linear"> n</label>
</div>
<label for='font'>Font:</label> <input type='text' id='font' value='Impact'>

<div id='angles'>
<label for='angle-count'>num orientations</label> <input type='number' id='angle-count' value='5' min='1'>
<label for='angle-from'>from  °</label> <input type='number' id='angle-from' value='-60' min='-90' max='90'>
<label for='angle-to'>to  °</label> <input type='number' id='angle-to' value='60' min='-90' max='90'>
</div>
<br>
<label for='max'>Number of words:</label> <input type='number' value='250' min='1' id='max'>
<label for='per-line'><input type='checkbox' id='per-line'> One word per line</label>

<button id="download-svg">Download SVG</button>
</form>
<p>Copyright &copy; <a href="http://www.jasondavies.com/">Jason Davies</a>. The generated word clouds may be used for any purpose.</p>


<script src="include/word_cloud/d3.min.js"></script>
<script src="include/word_cloud/cloud.min.js"></script>

<?php
}
?>
