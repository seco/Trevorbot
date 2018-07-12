<?php
// INFO
//media_type1 is an image
//media_type2 is a video
//media_type8 is a carousel

function imageDownload($imagePath, $mediaObj){
  $url = $mediaObj->getImageVersions2()->getCandidates()[0]->getUrl();
  $data = file_get_contents($url);
  file_put_contents($imagePath, $data);
}

function videoDownload($videoPath, $mediaObj){
  $url = $mediaObj->getVideoVersions()[0]->getUrl();
  $data = file_get_contents($url);
  file_put_contents($videoPath, $data);
}



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

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);
try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit();
}

//1 - get last page of liked images

$nextMaxId = null;
do{
  $feed = $ig->media->getLikedFeed($nextMaxId);
  $nextMaxId = $feed->getNextMaxId();
}while($nextMaxId != null);

//2 - download images/videos from the last page
$items = $feed->getItems();

$count = 0;
foreach ($items as $item) {

  //so we can unlike the images after
  $mediaId = $item->getId();
  array_push($mediaIdArray, $mediaId);

  //find media type - video, carousel, image
  $mediaType = $item->getMediaType();

  if($mediaType == 1){
    $count++;
    $path = "$folderPath/media/" . $count . ".jpg";
    imageDownload($path, $item);
    //create caption.txt
    file_put_contents($folderPath . "/media/caption" . $count . ".txt", "creds @" . $item->getUser()->getUsername());
  }
  if($mediaType == 2){
    $count++;
    $path = "$folderPath/media/" . $count . ".mp4";
    videoDownload($path, $item);
    file_put_contents($folderPath . "/media/caption" . $count . ".txt", "creds @" . $item->getUser()->getUsername());
  }
  if($mediaType == 8){
    $carouselMedia = $item->getCarouselMedia();
    //create a folder to store all media
    $uniqueId = uniqid();
    mkdir($folderPath . "/media/" . $uniqueId);
    $filePath = $folderPath . "/media/$uniqueId/";

    //create caption.txt
    file_put_contents($filePath . "caption.txt", "creds @" . $item->getUser()->getUsername());

    $carouselCount = 0;
    foreach ($carouselMedia as $media) {
      $carouselCount++;

      $type = $media->getMediaType();
      if($type == 1){
        $path = $filePath . $carouselCount . ".jpg";
        imageDownload($path, $media);
      }
      if($type == 2){
        $path = $filePath . $carouselCount . ".mp4";
        videoDownload($path, $media);
      }

    }
  }
}

//unlike images
foreach ($mediaIdArray as $id) {
  $ig->media->unlike($id);
}
