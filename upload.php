<?php

set_time_limit(0);
date_default_timezone_set('UTC');
$folderPath = "/Users/luca/Desktop/bots/trevorbot";
require "$folderPath/composer/vendor/autoload.php";


/////// CONFIG ///////
$username = 'trevorbot420';
$password = 'firebase';
$mediaIdArray = [];
$debug = true;
$truncatedDebug = false;
//////////////////////

//get a random file/folder
function randomMedia($path)
{
    $files = glob($path . '/*');
    $file = array_rand($files);
    return $files[$file];
}

function fetchCaption($path)
{
  $read = fopen($path, "r");
  $op = fread($read, filesize($path));
  fclose($read);
  return $op;
}


$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);
//for making sure video format/dimensions etc. are compatible to upload to insta
\InstagramAPI\Utils::$ffprobeBin = '/usr/local/bin/ffprobe';
\InstagramAPI\Media\Video\FFmpeg::$defaultBinary = '/usr/local/bin/ffmpeg';

try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit();
}

if(count(glob("$folderPath/media/*")) === 0){

  echo "getting more media to post";
  $output1 = shell_exec("/usr/bin/php $folderPath/getPicsInstagram.php");
  $output2 = shell_exec("/usr/local/bin/python3 $folderPath/getPicsReddit.py");
  file_put_contents("$folderPath/debug/getPicsInstagram.txt", $output1);
  file_put_contents("$folderPath/debug/getPicsReddit.txt", $output2);
  //$output2 = shell_exec("python3 getPicsReddit.py");

  if(count(glob("media/*")) === 0){
    echo "\n\n like/upvote more media - no more media to fetch or post";
    exit();
  }
}

//sometimes chooses .txt so we need to fix that
do{
  $randMedia = randomMedia("$folderPath/media");
  $pathinfo = pathinfo($randMedia);

}while($pathinfo["extension"] === "txt");

//its carousel
if(is_dir($randMedia)){
$media = [];

$fileSystemIterator = new FilesystemIterator($randMedia);
$count = -1;
foreach($fileSystemIterator as $file) {

  $count++;
  $mediaParts = pathinfo($file);
  if ($mediaParts["extension"] == "jpg"){
    $media[$count]['type'] = 'photo';
    $media[$count]['file'] = $file;
  }
  if ($mediaParts["extension"] == "mp4"){
    $media[$count]['type'] = 'video';
    $media[$count]['file'] = $randMedia . "/" . $mediaParts["basename"];
  }
}

$captionText = file_get_contents($captionPath, true);
$caption = "$captionText";

//////////////////////
$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);
try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}
////// NORMALIZE MEDIA //////
// All album files must have the same aspect ratio.
// We copy the app's behavior by using the first file
// as template for all subsequent ones.
$mediaOptions = [
    'targetFeed' => \InstagramAPI\Constants::FEED_TIMELINE_ALBUM,
    // Uncomment to expand media instead of cropping it.
    'operation' => \InstagramAPI\Media\InstagramMedia::EXPAND,
];
foreach ($media as &$item) {
    /** @var \InstagramAPI\Media\InstagramMedia|null $validMedia */
    $validMedia = null;
    switch ($item['type']) {
        case 'photo':
            $validMedia = new \InstagramAPI\Media\Photo\InstagramPhoto($item['file'], $mediaOptions);
            break;
        case 'video':
            $validMedia = new \InstagramAPI\Media\Video\InstagramVideo($item['file'], $mediaOptions);
            break;
        default:
            // Ignore unknown media type.
    }
    if ($validMedia === null) {
        continue;
    }
    try {
        $item['file'] = $validMedia->getFile();
        // We must prevent the InstagramMedia object from destructing too early,
        // because the media class auto-deletes the processed file during their
        // destructor's cleanup (so we wouldn't be able to upload those files).
        $item['__media'] = $validMedia; // Save object in an unused array key.
    } catch (\Exception $e) {
        continue;
    }
    if (!isset($mediaOptions['forceAspectRatio'])) {
        // Use the first media file's aspect ratio for all subsequent files.
        /** @var \InstagramAPI\Media\MediaDetails $mediaDetails */
        $mediaDetails = $validMedia instanceof \InstagramAPI\Media\Photo\InstagramPhoto
            ? new \InstagramAPI\Media\Photo\PhotoDetails($item['file'])
            : new \InstagramAPI\Media\Video\VideoDetails($item['file']);
        $mediaOptions['forceAspectRatio'] = $mediaDetails->getAspectRatio();
    }
}
unset($item);
/////////////////////////////
try {
    $ig->timeline->uploadAlbum($media, ['caption' => $caption]);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}

array_map('unlink', glob("$randMedia/*.*"));
rmdir($randMedia);

//end of carousel upload!
}
else{
  //video or image
  $mediaParts = pathinfo($randMedia);

  if($mediaParts["extension"] == "jpg" || $mediaParts["extension"] == "jpeg" || $mediaParts["extension"] == "png" || $mediaParts["extension"] == "PNG"){
    $captionPath = "$folderPath/media/caption" . $mediaParts["filename"] . ".txt";
    $captionText = file_get_contents($captionPath, true);
    $caption = "$captionText";

    try {
        $photo = new \InstagramAPI\Media\Photo\InstagramPhoto($randMedia);
        $ig->timeline->uploadPhoto($photo->getFile(), ['caption' => $caption]);
    } catch (\Exception $e) {
        echo 'Something went wrong: '.$e->getMessage()."\n";
    }
    //delete uploaded media
    unlink($randMedia);
    unlink($captionPath);
  }

  if ($mediaParts["extension"] == "mp4") {
    $captionPath = "$folderPath/media/caption" . $mediaParts["filename"] . ".txt";
    $captionText = file_get_contents($captionPath, true);
    $caption = "$captionText - volume up.";

try {
    $video = new \InstagramAPI\Media\Video\InstagramVideo($randMedia);
    $ig->timeline->uploadVideo($video->getFile(), ['caption' => $caption]);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    }

    //delete uploaded media
    unlink($randMedia);
    unlink($captionPath);
  }
}
