<?php
namespace Lobby\App;

/**
 * `dName` is same as `id`
 * Download Name is same as Download ID
 */

class downloader extends \Lobby\App {

  public $downloadStatusFile, $ds = "";

  public function page($p){
    $this->downloadStatusFile = APP_DIR . "/src/data/download-status.txt";
    $this->downloadExists();
    
    if($p === "/receive-status" && isset($_POST['status'])){
      $ds = json_decode(\H::i("status"), true);
      if(is_array($ds)){
        foreach($ds as $dName => $newDInfo){
          if($this->downloadExists($dName)){
            $dInfo = \H::getJSONData($dName);
            if($dInfo["paused"] == "1"){
              echo "paused";
            }else{
              \H::saveJSONData($dName, $newDInfo);
            }
          }else{
            echo "cancelled";
          }
        }
      }
      saveData("lastDownloadStatusCheck", time());
      exit;
    }else{
      return "auto";
    }
  }
  
  public function downloadExists($dName = ""){
    if($this->ds == ""){
      $this->ds = \H::getJSONData("downloads");
      if($this->ds === null){
        $this->ds = array();
      }else{
        $this->ds = array_keys($this->ds);
      }
    }
    if($dName !== ""){
      return in_array($dName, $this->ds);
    }
  }
  
  /**
   * Add a download
   */
  public function addDownload($url, $fileName, $dDir, $resumable = true){
    /**
     * Make an ID
     */
    $dName = md5($url . rand(0, 1000));
    
    \H::saveJSONData($dName, array(
      "downloaded" => "0",
      "downloadDir" => $dDir,
      "error" => "0",
      
      /**
       * Estimated time to complete download
       * ETA - Estimated Time to Arrive
       * ETA is calculated on basis of average speed and not current speed
       */
      "eta" => "0",
      
      "fileName" => $fileName,
      
      "percentage" => "0",
      "paused" => "0",
      "resumable" => $resumable ? "1" : "0",
      "size" => 1,
      
      /**
       * The current speed of download, not average speed
       */
      "speed" => "0",
      "url" => $url
    ));
    \H::saveJSONData("downloads", array(
      $dName => 1
    ));
  }
  
  public function isDownloadRunning(){
    if ($this->getData("lastDownloadStatusCheck") < strtotime("-5 seconds")) {
      return false;
    }else{
      return true;
    }
  }
  
  public function getPHPExecutable() {
    if(defined("PHP_BINARY") && PHP_BINARY != ""){
      return PHP_BINARY;
    }else{
      $paths = explode(PATH_SEPARATOR, getenv('PATH'));
      foreach ($paths as $path) {
        // we need this for XAMPP (Windows)
        if (strstr($path, 'php.exe') && isset($_SERVER["WINDIR"]) && file_exists($path) && is_file($path)) {
          return $path;
        }else {
          $php_executable = $path . DIRECTORY_SEPARATOR . "php" . (isset($_SERVER["WINDIR"]) ? ".exe" : "");
          if (file_exists($php_executable) && is_file($php_executable)) {
            return $php_executable;
          }
        }
      }
    }
    return FALSE; // not found
  }
  
  public function convertToReadableSize($size){
    $base = log($size) / log(1000);
    $suffix = array("", "KB", "MB", "GB", "TB");
    $f_base = floor($base);
    return round(pow(1000, $base - floor($base)), 1) . $suffix[$f_base];
  }
  
  public function secToTime($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds / 60) % 60);
    $seconds = $seconds % 60;
  
    return $hours > 0 ? "$hours hours, $minutes minutes" : ($minutes > 0 ? "$minutes minutes, $seconds seconds" : "$seconds seconds");
  } 
  
  /**
   * Pauses all running downloads
   */
  public function pauseAllDownloads(){
    $paused = array();
    foreach($this->ds as $dName){
      $dInfo = \H::getJSONData($dName);
      if($dInfo['paused'] == "0" && $dInfo['percentage'] != "100"){
        $paused[] = $dName;
        saveJSONData($dName, array(
          "paused" => "1"
        ));
      }
    }
    return $paused;
  }
  
  public function refreshDownloads(){
    $paused = $this->pauseAllDownloads();
    sleep(1);
    foreach($paused as $dName){
      \H::saveJSONData($dName, array(
        "paused" => 0
      ));
    }
    saveData("lastDownloadStatusCheck", "1");
  }

}
