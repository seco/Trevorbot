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

function getTrueOp($item){
  //check if they are @ting someone in the caption and add them to credits
  if ($item->hasCaption()){
    $opCaption = $item->getCaption()->getText();
    print($opCaption);
    $opCaptionExploded = explode(" ", $opCaption);
    //if the @ sign is not by itself
    if (!in_array("@", $opCaptionExploded)){
      print("not in array");
      //so now if the caption contains @ we know it is not by itself
      if (strpos($opCaption, "@") !== false){
        print("there is a credit in caption");
        //get first string of @ appearance
        foreach ($opCaptionExploded as $value) {
          if (strpos($value, "@") !== false){
            print("not equal to false");
            $trueOp = $value;
             break;
          }
        }
        $trueOp = str_replace("@","",$trueOp);
        return($trueOp);
      }
    }
  }
}

set_time_limit(0);
date_default_timezone_set('UTC');
$folderPath = "/Users/luca/Desktop/bots/trevorbot";
require "$folderPath/composer/vendor/autoload.php";

/////// CONFIG ///////
$username = 'xxxxxxxx';
$password = 'xxxxxxxx';
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

  //get true op if there is one
  $credit = getTrueOp($item);
  if($credit == null){
    //if not just credit meme account you stole from
    $credit = $item->getUser()->getUsername();
  }
  else{
    $stolenFrom = $item->getUser()->getUsername();
    $credit = "$credit, (stolen from @$stolenFrom)";
  }

  if($mediaType == 1){
    $count++;
    $path = "$folderPath/media/" . $count . ".jpg";
    imageDownload($path, $item);
    //create caption.txt
    file_put_contents($folderPath . "/media/caption" . $count . ".txt", "creds @" . $credit);
  }
  if($mediaType == 2){
    $count++;
    $path = "$folderPath/media/" . $count . ".mp4";
    videoDownload($path, $item);
    file_put_contents($folderPath . "/media/caption" . $count . ".txt", "creds @" . $credit);
  }
  if($mediaType == 8){
    $carouselMedia = $item->getCarouselMedia();
    //create a folder to store all media
    $uniqueId = uniqid();
    mkdir($folderPath . "/media/" . $uniqueId);
    $filePath = $folderPath . "/media/$uniqueId/";

    //create caption.txt
    file_put_contents($filePath . "caption.txt", "creds @" . $credit);

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
