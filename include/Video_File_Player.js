function Video_File_Player(obfuscated_file_path, video_id)
{
  file_path   = atob(obfuscated_file_path);
  var video   = document.getElementById(video_id);
  //var source  = document.createElement("source");
  var source  = document.createElement("img");
  source.src  = file_path;
  source.alt  = "An image of a natural landscape.";
  //source.type = "video/mp4";
  //video.muted = true;
  //video.loop  = true;
  video.appendChild(source);

  this.video = video;
}

Video_File_Player.prototype.play = function()
{
  //console.log("play video");
  ////this.video.reload();
  //this.video.pause();
  //this.video.currentTime = 0;
  //this.video.play();
};

Video_File_Player.prototype.stop = function()
{
  //console.log("stop video");
  //this.video.pause();
};
