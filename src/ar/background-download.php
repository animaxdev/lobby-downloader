<?php
use \MultiRequest\Request;
use \MultiRequest\Handler;

set_time_limit(-1);
if(isset($argv[1])){
  $ds = json_decode($argv[2], true);
  $statusURL = $argv[1];
  $lastSent = 0;

  function saveData($dName, $newDs, $force = false){
    global $ds, $statusURL, $lastSent;

    if($lastSent < microtime() - 250000 && !$force)
      return;
    
    $lastSent = microtime();
    $newDs = array_replace_recursive($ds[$dName], $newDs);
    $ds[$dName] = $newDs;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $statusURL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "dName=". urlencode($dName) ."&newDInfo=" . urlencode(json_encode($newDs)));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $server_output = curl_exec($ch);
    if($server_output == "cancelled" || $server_output == "paused"){
      $GLOBALS["$dName-cancel"] = 1;
    }
    curl_close($ch);
  }

  require_once __DIR__ . "/../lib/vendor/autoload.php";

  $mrHandler = new Handler();
  $mrHandler->setConnectionsLimit(1000);

  $curlResult = array();

  foreach($ds as $dName => $dInfo) {
    $url = $dInfo['url'];

    $cookieJarFilename = tempnam(sys_get_temp_dir(), time() . "_" . substr(md5(microtime()), 0, 5));
    /**
     * Set the initial params for each download
     */
    $GLOBALS["$dName-startTime"] = $GLOBALS["$dName-prevTime"] = microtime(true);

    $GLOBALS["$dName-alreadyDownloaded"] = $GLOBALS["$dName-prevSize"] = $GLOBALS["$dName-currentSpeed"] = $GLOBALS["$dName-timeRemaining"] = 0;

    $GLOBALS["$dName-lastSpeedCalc"] = 0;

    $savePath = $dInfo['downloadDir'] . DIRECTORY_SEPARATOR . $dInfo['fileName'];
    $savePathFP = fopen($savePath, "a+");

    $curlOptions = array(
      CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12',
      CURLOPT_FOLLOWLOCATION => 1,
      CURLOPT_CONNECTTIMEOUT => 0,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_BINARYTRANSFER => true,
      CURLOPT_COOKIEJAR => $cookieJarFilename,
      CURLOPT_COOKIEFILE => $cookieJarFilename,
      CURLOPT_NOPROGRESS => false,
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
        $sessionDownloaded = 0;
        $dInfo = array(
          "error" => "0",
          "paused" => "0",
          "percentage" => "0",
          "status" => "running"
        );

        if($downloaded > 0 && $downloadSize > 0){
          $sessionDownloaded = $downloaded;
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
          $dInfo["percentage"] = ($downloaded / $downloadSize) * 100;
        }

        if($GLOBALS["$dName-lastSpeedCalc"] < strtotime("-1 seconds")){
          /**
           * Calculate Speed
           */
          $GLOBALS["$dName-averageSpeed"] = $sessionDownloaded / (microtime(true) - $GLOBALS["$dName-startTime"]);

          $GLOBALS["$dName-currentSpeed"] = max(
            round(
              ($sessionDownloaded - $GLOBALS["$dName-prevSize"]) / (microtime(true) - $GLOBALS["$dName-prevTime"]
            ), 0
          ), 0);
          $GLOBALS["$dName-prevTime"] = microtime(true);
          $GLOBALS["$dName-prevSize"] = $sessionDownloaded;

          if($GLOBALS["$dName-averageSpeed"] != 0){
            $GLOBALS["$dName-timeRemaining"] = round(
              (
                $dInfo["size"] - $dInfo["downloaded"]
              )
              /
              $GLOBALS["$dName-averageSpeed"],
              0
            );
          }

          $dInfo["eta"] = $GLOBALS["$dName-timeRemaining"];
          $dInfo["speed"] = $GLOBALS["$dName-currentSpeed"];

          $GLOBALS["$dName-lastSpeedCalc"] = time();
        }

        // Download complete
        if($dInfo["percentage"] == "100"){
          $dInfo["status"] = "completed";
          saveData($dName, $dInfo, true);
        }else{
          saveData($dName, $dInfo);
        }
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
      $fileSize = filesize($savePath);
      $responseHeaders = @get_headers($url, 1);

      $contentLength = 0;
      if(isset($responseHeaders['Content-Length'])){
        $contentLength = $responseHeaders['Content-Length'];
        if(is_array($contentLength)){
          $index = count($contentLength) - 1;
          $contentLength = $contentLength[$index];
        }
      }

      if($contentLength > $fileSize){
        $GLOBALS["$dName-alreadyDownloaded"] = $fileSize;
        $curlOptions[CURLOPT_RANGE] = $fileSize . "-" . $contentLength;
      }else{
        /**
         * File already fully downloaded, so skip
         */
        saveData($dName, array(
          "percentage" => "100",
          "status" => "completed"
        ), true);
        continue;
      }
    }

    $request = new Request($url);
    $request->addCurlOptions($curlOptions);
    $request->onFailed(function($that, $Exception, Handler $handler) use($dName){
      /**
       * If the download was manually cancelled, don't save the error
       */
      if(!isset($GLOBALS["$dName-cancel"]) && $Exception->getMessage() !== "Response failed with code \"206\""){
        saveData($dName, array(
          "error" => $Exception->getMessage(),
          /**
           * On Error, the percentage is 100 because request completed
           */
          "percentage" => "0",
          "status" => "error"
        ), true);
      }
    });
    $mrHandler->pushRequestToQueue($request);
  }
  $mrHandler->onRequestComplete(function(Request $request, Handler $handler){
    
  });
  $mrHandler->start();
}
