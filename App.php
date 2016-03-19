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
      foreach($ds as $dName => $newDInfo){
        if($this->downloadExists($dName)){
          $dInfo = getJSONData($dName);
          if($dInfo["paused"] == "1"){
            echo "paused";
          }else{
            saveJSONData($dName, $newDInfo);
          }
        }else{
          echo "cancelled";
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
      $this->ds = getJSONData("downloads");
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
  public function addDownload($url, $fileName, $dDir){
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
    if (getData("lastDownloadStatusCheck") < strtotime("-1 second")) {
      return false;
    }else{
      return true;
    }
  }
  
  public function getPHPExecutable() {
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
    return FALSE; // not found
  }
  
  public function convertToReadableSize($size){
    $base = log($size) / log(1024);
    $suffix = array("", "KB", "M", "G", "T");
    $f_base = floor($base);
    return round(pow(1024, $base - floor($base)), 1) . $suffix[$f_base];
  }

}
