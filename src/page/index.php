<?php
$this->addScript("app.js");
$this->addStyle("app.css");
?>
<div class="contents">
  <h1>Downloader</h1>
  <a id="newDownload" class="btn green">New Download</a>
  <a class="btn blue" href="<?php echo $this->u("/settings");?>"><?php _e("Settings");?></a>
  <?php require_once APP_DIR . "/src/ajax/downloads.php";?>
</div>
<div id="newDownloadDialog" hide>
  <h4>New Download</h4>
  <form>
    <label>
      <span>Download URL</span>
      <input type="text" name="downloadURL" />
    </label>
    <label>
      <span>Download To</span>
      <div class="row">
        <div class="col s8">
        <input type="text" name="downloadPath" id="dLoc"  value="<?php echo getData("downloadsDir");?>" />
        </div>
        <a id="chooseDLoc" class="btn orange col s4">Choose Path</a>
      </div>
    </label>
    <button class="btn green">Start Download</button>
  </form>
</div>
