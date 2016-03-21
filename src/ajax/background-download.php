<?php
set_time_limit(0);
if(isset($argv[1])){
  $ds = json_decode($argv[2], true);
  $statusURL = $argv[1];
  
  function saveData($dName, $newDs){
    global $ds, $statusURL;
    $newDs = array_replace_recursive($ds[$dName], $newDs);
    
    $newDs = array(
      $dName => $newDs
    );
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $statusURL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "status=" . urlencode(json_encode($newDs)));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $server_output = curl_exec($ch);
    if($server_output == "cancelled" || $server_output == "paused"){
      $GLOBALS["$dName-cancel"] = 1;
    }
    curl_close ($ch);
  }
  
  require_once __DIR__ . "/../lib/vendor/autoload.php";
  
  $mrHandler = new \MultiRequest\Handler();
  $mrHandler->setConnectionsLimit(1000);
  
  
  
  $curlResult = array();
  
  foreach($ds as $dName => $dInfo) {
    $url = $dInfo['url'];
    
    /**
     * Set the initial params for each download
     */
    if(isset($dInfo['startTime']) && isset($dInfo['prevTime'])){
      $GLOBALS["$dName-startTime"] = $dInfo['startTime'];
      $GLOBALS["$dName-prevTime"] = $dInfo['prevTime'];
    }else{
      $GLOBALS["$dName-startTime"] = $GLOBALS["$dName-prevTime"] = microtime(true);
    }
    $GLOBALS["$dName-alreadyDownloaded"] = $GLOBALS["$dName-prevSize"] = $GLOBALS["$dName-currentSpeed"] = $GLOBALS["$dName-timeRemaining"] = 0;
    
    $savePath = $dInfo['downloadDir'] . DIRECTORY_SEPARATOR . $dInfo['fileName'];
    $savePathFP = fopen($savePath, "a+");

    $curlOptions = array(
      CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12',
      CURLOPT_NOPROGRESS => false,
      CURLOPT_FOLLOWLOCATION => 1,
      CURLOPT_BINARYTRANSFER => true,
      CURLOPT_PROGRESSFUNCTION => function($resource, $downloadSize, $downloaded, $upload_size, $uploaded = "") use($dName) {
        /**
         * On new versions of cURL, $resource parameter is not passed
         * So, swap vars if it doesn't exist
         */
        if(!is_resource($resource)){
          $uploaded = $upload_size;
          $upload_size = $downloaded;
          $downloaded = $downloadSize;
          $downloadSize = $resource;
        }
        
        $dInfo = array(
          "error" => "0",
          "startTime" => $GLOBALS["$dName-startTime"],
          "prevTime" => $GLOBALS["$dName-prevTime"]
        );
        
        if($downloaded > 0 && $downloadSize > 0){
          /**
           * If the file is resumed for download, then the
           * $downloaded and $downloadSize will be the difference
           * of the actual file size - partially downloaded file size
           * So, to get the actual file size, we must add the
           * partial file size to it.
           * $GLOBALS['alreadyDownloaded'] = partial file size
           */
          $downloaded = $GLOBALS["$dName-alreadyDownloaded"] + $downloaded;
          $downloadSize = $GLOBALS["$dName-alreadyDownloaded"] + $downloadSize;
          
          $dInfo["downloaded"] = $downloaded;
          $dInfo["size"] = $downloadSize;
          $dInfo['percentage'] = ($downloaded / $downloadSize) * 100;
        }
        
        if($GLOBALS["$dName-prevTime"] < strtotime("-1 second")){
          /**
           * Calculate Speed
           */
          $GLOBALS["$dName-averageSpeed"] = $downloaded / (microtime(true) - $GLOBALS["$dName-startTime"]);
          
          $GLOBALS["$dName-currentSpeed"] = max(
            round(
              ($downloaded - $GLOBALS["$dName-prevSize"]) / (microtime(true) - $GLOBALS["$dName-prevTime"]
            ), 0
          ), 0);
          $GLOBALS["$dName-prevTime"] = microtime(true);
          $GLOBALS["$dName-prevSize"] = $downloaded;
          
          if($GLOBALS["$dName-averageSpeed"] != 0){
            $GLOBALS["$dName-timeRemaining"] = abs(round(($downloaded - $downloadSize) / $GLOBALS["$dName-averageSpeed"], 0));
          }else{
            $GLOBALS["$dName-timeRemaining"] = 0;
          }
        }
        $dInfo["eta"] = $GLOBALS["$dName-timeRemaining"];
        $dInfo["speed"] = $GLOBALS["$dName-currentSpeed"];
        saveData($dName, $dInfo);
      },
      CURLOPT_HEADER => 0,
      CURLOPT_WRITEFUNCTION => function($ch, $data) use ($dName, $savePathFP) {
        if(isset($GLOBALS["$dName-cancel"])){
          /**
           * A random number
           * Fun fact - 11 (Roll number of mine when I was in 11th grade)
           *          - 29 (Roll number of mine when I was in 10th grade)
           *          - 31 (Roll number of mine when I was in 9th grade)
           */
          return -112931;
        }
        /**
         * Live write to file
         */
        $len = fwrite($savePathFP, $data);
        
        return $len; //return the exact length
      }
    );
    
    if(file_exists($savePath)){
      $from = filesize($savePath);
      $responseHeaders = @get_headers($url, 1);
      
      if(isset($responseHeaders['Content-Length']) && $responseHeaders['Content-Length'] != $from){
        $GLOBALS["$dName-alreadyDownloaded"] = $from;
        $curlOptions[CURLOPT_RANGE] = $from . "-" . $responseHeaders['Content-Length'];
      }else{
        /**
         * File already fully downloaded, so skip
         */
        saveData($dName, array(
          "percentage" => "100"
        ));
        continue;
      }
    }
    
    $request = new \MultiRequest\Request($url);
    $request->addCurlOptions($curlOptions);
    $request->onFailed(function($this, $Exception, \MultiRequest\Handler $handler) use($dName){
      /**
       * If the download was manually cancelled, don't save the error
       */
      if(!isset($GLOBALS["$dName-cancel"])){
        saveData($dName, array(
          "error" => $Exception->getMessage(),
          /**
           * On Error, the percentage is 100 because request completed
           */
          "percentage" => "0"
        ));
      }
    });
    $mrHandler->pushRequestToQueue($request);
  }
  $mrHandler->start();
}
