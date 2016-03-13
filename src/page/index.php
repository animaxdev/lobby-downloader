<?php
$this->addScript("app.js");
?>
<div class="contents">
  <h1>Downloader</h1>
  <a id="newDownload" class="btn green">New Download</a>
  <?php
  $ds = getData("downloads");
  if($ds === null){
    ser("No Downloads", "Why don't you download some stuff ?");
  }
  ?>
</div>
<div id="newDownloadDialog" hide>
  <h4>New Download</h4>
  <form>
    <label>
      <span>Download URL</span>
      <input type="text" id="downloadURL" />
    </label>
    <button class="btn green">Start Download</button>
  </form>
</div>
