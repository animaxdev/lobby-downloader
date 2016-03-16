<?php
$this->addScript("app.js");
?>
<div class="contents">
  <h1>Downloader</h1>
  <a id="newDownload" class="btn green">New Download</a>
  <?php require_once APP_DIR . "/src/ajax/downloads.php";?>
</div>
<div id="newDownloadDialog" hide>
  <h4>New Download</h4>
  <form>
    <label>
      <span>Download URL</span>
      <input type="text" name="downloadURL" />
    </label>
    <button class="btn green">Start Download</button>
  </form>
</div>
