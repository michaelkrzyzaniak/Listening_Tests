# Listening_Tests
Listening Tests for the Ambisynth Project

Php website for listening tests for the Ambisynth project. Ambisynth is about soundscape synthesis.

More info about ambisynth is at <a href="https://ambisynth.blogspot.com">ambisynth.blogspot.com</a><br>
Screen Shots of this code up and running is at <a href="https://ambisynth.blogspot.com/2018/08/listening-tests.html">ambisynth.blogspot.com/2018/08/listening-tests.html</a><br>

The idea is that you might have several audio classes (train, cafe, beach, etc), several synthesis methods for each audio class (real recordings, granular synth, wavenet, etc), and several audio files for each synthesis method for each class.
These tests will hlep you figure out perceptually how convincing the different synthesis methods are.

The website infers the audio class and synth method from the directory structure. Audio should be stored in

/audio
  ->cafe
    ->real_recording
      ->file_1.wav
      ->file_2.wav
    ->wavenet
      ->file_1.wav
      ->file_2.wav
    ->granular_synth
      ->file_1.wav
      ->file_2.wav
  ->beach
    ->real_recording
      ->file_1.wav
      ->file_2.wav
    ->wavenet
      ->file_1.wav
      ->file_2.wav
    ->granular_synth
      ->file_1.wav
      ->file_2.wav

Some of the tests involve video. Video does not have different synthesis methods, so the directory structure should be:
 /video
  ->cafe
    ->file_1.mp4
    ->file_2.mp4
  ->cafe
    ->file_1.mp4
    ->file_2.mp4

Every video class must have a corresponding audio class. Within each test, the class will be chosen with an flat probability, then the synth mehtod with a flat probilility, then the audio file. 

There is an index.html page that links to each individual test and the results page for the tests. Test subjects are not intended to see this page, it is just for the convienence of the researchers.

Test subjects could start on any test, and will be redirected randomly from test to test until they answered a certain number of questions. The site uses cookies to identify users, and the cookies are cleared at the end of the test. If the researcher is testing several subjects on the same computer, they might want to manually visit session_finished.php between users to doubly ensure the cookie has been cleared.

The website will create an sqlite database and save the users responses there.

