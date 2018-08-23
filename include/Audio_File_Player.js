var global_audio_context = null;

if('webkitAudioContext' in window)
  global_audio_context = new webkitAudioContext();
else if('AudioContext' in window)
  global_audio_context = new AudioContext();

/*------------------------------------------------------------*/
function Audio_File_Player(filename, audio_element_id, should_loop, should_autoplay)
{
  filename = atob(filename);
  this.audio_nodes    = {};
  this.audio_buffer   = null;
  this.audio_element  = null;
  this.audio_source   = null;
  this.source_type    = "";
  this.filename       = filename;
  //this.gain           = 1.0;
  //this.target_gain    = 1.0;
  //this.gain_filter_coeff = 0.001;
  //this.one_minus_gain_filter_coeff = 1 - this.gain_filter_coeff;
  
  this.tried_to_play_before_load = false;
  this.should_auto_play = should_autoplay;
  //this.cordova_media  = null;
  this.loop           = should_loop;
  this.playing        = false;
  
  if(audio_element_id !== undefined)
    this.audio_element = document.getElementById(audio_element_id);
  
  this.init(filename, this.audio_element);
}

/*------------------------------------------------------------*/
Audio_File_Player.prototype.init = function(filename, audio_element)
{
  //var browser = navigator.userAgent.toLowerCase();
  //browser = (browser.match(/msie|firefox|chrome|safari|opera/g) || "other")[0];
  //console.log("Browser", browser);
  
  //this.create_cordova_plugin_source(filename);
  
  //if(browser == 'safari')
  if(global_audio_context !== null)
    {
      this.audio_nodes.gain       = global_audio_context.createGain();
      //this.audio_nodes.gain       = global_audio_context.createScriptProcessor(1024, 1, 1);;
      this.audio_nodes.gain.connect(global_audio_context.destination);
      this.audio_nodes.gain.gain.value   =  1;
      //this.audio_nodes.gain.onaudioprocess = this.process_gain.bind(this);
      if(filename !== "") //used for concatenation
        this.create_buffer_source(filename);
      //this.create_media_element_source(filename, audio_element);
    }
  else
    this.create_no_web_audio_source(filename, audio_element);
};

/*------------------------------------------------------------*/
Audio_File_Player.new_by_concatenation = function(array, should_loop)
{
  var self = new Audio_File_Player("", null, should_loop);
  self.source_type = 'buffer';
  self.implode(array);
  return self;
};

/*------------------------------------------------------------*/
//concatenates array into a single buffer and sets self.buffer to contatenated thing
Audio_File_Player.prototype.implode = function(array)
{
  var i, j, channel;
  var num_channels = 1;
  var buffer_length = 0;
  var sample_rate   = 44100;
 
  for(i=0; i<array.length; i++)
    {
      if(array[i].source_type !== "buffer")
        continue;
      num_channels = Math.min(num_channels, array[i].audio_buffer.numberOfChannels);
      buffer_length += array[i].audio_buffer.length;
      sample_rate   = array[i].audio_buffer.sampleRate; //not elegant but it grabs some arbitrary valid sample rate (e.g. the last one)
    }

  var buffer = global_audio_context.createBuffer(num_channels, buffer_length, sample_rate);
  for(i=0; i<num_channels; i++)
    {
      channel = buffer.getChannelData(i);
      buffer_length = 0;
      for(j=0; j<array.length; j++)
        {
          if(array[j].source_type !== "buffer")
            continue;
          channel.set(array[j].audio_buffer.getChannelData(i), buffer_length);
          buffer_length += array[j].audio_buffer.length;
        }
    }
  
  this.stop();
  //this.init("", null);
  this.source_type = 'buffer';
  this.audio_buffer = buffer;
  return self;
};

//MOVE THIS DOWN LATER
/*------------------------------------------------------------*/
/*
Audio_File_Player.prototype.process_gain = function(audioProcessingEvent)
{
  var input_buffer  = audioProcessingEvent.inputBuffer;
  var output_buffer = audioProcessingEvent.outputBuffer;
  
  var chan, sample;
  
  for(chan=0; chan<output_buffer.numberOfChannels; chan++) 
    {
      var input_data  = input_buffer.getChannelData(chan);
      var output_data = output_buffer.getChannelData(chan);

      for(sample=0; sample<input_buffer.length; sample++) 
        {
          output_data[sample] = input_data[sample] * this.gain;
          this.gain = (this.gain_filter_coeff * this.target_gain) + (this.one_minus_gain_filter_coeff * this.gain);
        }
    }
};
*/

/*------------------------------------------------------------*/
Audio_File_Player.prototype.create_buffer_source = function(filename) 
{
  //console.log("create_buffer_source");
  this.source_type = 'buffer';
  
  var request = new XMLHttpRequest();
  
  request.open("GET", filename, true);
  request.responseType = 'arraybuffer';
  request.addEventListener('load', this.file_opened_callback.bind(this), false);
  request.send();
};

/*------------------------------------------------------------*/
Audio_File_Player.prototype.file_opened_callback = function(event)
{
  var request = event.target;
  if(request.response !== null)
    {
      //console.log(request.response);
      //this.audio_buffer = global_audio_context.createBuffer(request.response, false);
      global_audio_context.decodeAudioData(request.response, function(buffer)
        {
          this.audio_buffer = buffer;
          if(this.should_auto_play || this.tried_to_play_before_load)
            {
              this.play();
              this.tried_to_play_before_load = false;
            }
        }.bind(this));
      //var source = global_audio_context.createBufferSource();
      //source.buffer = this.audio_buffer;
      //this.connect_audio_source_to_graph(source);
    }
  else console.log('invalid http request response');
};

/*------------------------------------------------------------*/
Audio_File_Player.prototype.create_media_element_source = function(filename, audio_element) 
{
  //console.log("create_media_element_source");
  this.source_type = 'media_element';
  var source = global_audio_context.createMediaElementSource(audio_element);
  //audio_element.src = filename;
  audio_element.innerHTML = "<source src='" + filename + "' type='audio/mpeg' />";
  audio_element.volume = 1;
  
  this.connect_audio_source_to_graph(source);
};

/*------------------------------------------------------------*/
Audio_File_Player.prototype.create_no_web_audio_source = function(filename, audio_element) 
{
  //console.log("create_no_web_audio_source");
  this.source_type = 'no_web_audio';
  //audio_element.src = filename;
  audio_element.innerHTML = "<source src='" + filename + "' type='audio/mpeg' />";
  audio_element.volume = 1;
};

/*------------------------------------------------------------*/
Audio_File_Player.prototype.create_cordova_plugin_source = function(filename)
{
  console.log("created cordova source");
  this.source_type = 'cordova';
  this.cordova_media = new Media(filename, null /*mediaSuccess*/, null /*[mediaError]*/, null/*[mediaStatus]*/);
};

/*------------------------------------------------------------*/
Audio_File_Player.prototype.play = function()
{
  if(this.playing) return;
  
  if(this.source_type == "buffer")
  {
    if(this.audio_buffer !== null)
      {
        this.playing = true;
        var source = global_audio_context.createBufferSource();
        source.loop = this.loop;
        source.buffer = this.audio_buffer;
        //todo: how to remove the old node?
        this.connect_audio_source_to_graph(source);
        this.audio_source.start();
      }
    else // didn't load yet
      {
        this.tried_to_play_before_load = true;
        console.log("tried_to_play_before_load");
      }
    }
  else if(this.source_type == "cordova")
    this.cordova_media.play();
  else
    this.audio_element.play();
};

/*------------------------------------------------------------*/
Audio_File_Player.prototype.set_gain = function(gain)
{
  if(this.source_type == 'no_web_audio')
    this.audio_element.volume = gain;
  else if(this.source_type == "cordova")
    this.cordova_media.setVolume(gain);
  else
    //this.target_gain = gain;
    this.audio_nodes.gain.gain.value = gain;
};

/*------------------------------------------------------------*/
Audio_File_Player.prototype.stop = function()
{
  this.playing = false;
  if(this.source_type == "buffer")
    {
      if(this.audio_source)
        {
          //todo: playbackState has been deprecated, but on Safari
          //we get error saying the object was in an invalid state
          //if state != 2, so for now we check.
          var can_stop = true;
          if(typeof(this.audio_source.playbackState) !== 'undefined')
            if(this.audio_source.playbackState != 2)
              can_stop = false;
          if(can_stop)
            this.audio_source.stop();
          else
            console.log("AUDIO STOP was in an invlaid state");
        }
    }
  else if(this.source_type == "cordova")
    this.cordova_media.stop();
  else
    this.audio_element.pause();
};

/*------------------------------------------------------------*/
Audio_File_Player.prototype.connect_audio_source_to_graph = function(source)
{
  source.connect (this.audio_nodes.gain);
  this.audio_source = source;
};

/*--------------------------------------------------------------------*/
// Evidently, on ios, we can not play sounds at arbitrary times unless
// the user presses a button to trigger a sound at least once.
// So this method plays a silent audio file. This should
// be called in response to some random button press. Doing this
// will then allow the app to speak at arbitrary times.
Audio_File_Player.webaudio_is_unlocked = false;
Audio_File_Player.unlock_web_audio_if_necessary = function()
{
  var is_ios = !!navigator.platform && /iPad|iPhone|iPod/.test(navigator.platform);
  if((is_ios === true) && (Audio_File_Player.webaudio_is_unlocked === false))
    {
      var buffer = global_audio_context.createBuffer(1, 1, 22050);
      var source = global_audio_context.createBufferSource();
      source.buffer = buffer;
      source.connect(global_audio_context.destination);
      source.start();

      // playbackState is deprecated but still used by ios at the moment
      setTimeout(function()
      {
        if((source.playbackState === source.PLAYING_STATE) || (source.playbackState === source.FINISHED_STATE))
          Audio_File_Player.webaudio_is_unlocked = true;
      }, 100);
    }
};
