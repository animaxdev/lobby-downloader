<?php
namespace Lobby\App;

/**
 * `dName` is same as `id`
 * Download Name is same as Download ID
 */

class downloader extends \Lobby\App {

  public $ds = null;

  public function init(){
    $this->downloadExists();
  }

  public function page($p){
    return "auto";
  }

  public function downloadExists($dName = ""){
    if($this->ds === null){
      $this->ds = $this->data->getArray("downloads");
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

    $this->data->saveArray($dName, array(
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

      "status" => "paused",
      "url" => $url
    ));
    $this->data->saveArray("downloads", array(
      $dName => 1
    ));
  }

  public function isDownloadRunning(){
    if ($this->data->getValue("lastDownloadStatusCheck") < strtotime("-10 seconds")) {
      return false;
    }else{
      return true;
    }
  }

  /**
   * Get the array of downloads that are in queue for download
   */
  public function getActiveDownloads(){
    $doDs = array();
    /**
     * The existing downloads
     */
    foreach($this->ds as $dName){
      $dInfo = $this->data->getArray($dName);
      if($dInfo['paused'] == "0" && $dInfo['percentage'] != "100"){
        $doDs[$dName] = $dInfo;
      }
    }
    return $doDs;
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
      $dInfo = $this->data->getArray($dName);
      if($dInfo['paused'] == "0" && $dInfo['percentage'] != "100"){
        $paused[] = $dName;
        $this->data->saveArray($dName, array(
          "paused" => "1"
        ));
      }
    }
    return $paused;
  }

  public function refreshDownloads(){
    $paused = $this->pauseAllDownloads();
    foreach($paused as $dName){
      $this->data->saveArray($dName, array(
        "paused" => 0
      ));
    }
    $this->data->saveValue("lastDownloadStatusCheck", "1");
  }

}
