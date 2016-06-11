<?php
$this->setTitle("Settings");
?>
<div class="contents">
  <h2>Settings</h2>
  <?php
  if(isset($_POST["save"])){
    $this->saveData("startOnLobbyOpen", isset($_POST["startOnLobbyOpen"]) ? "1" : "0");
    echo sss("Saved", "Settings have been saved");
  }
  ?>
  <form method="POST" action="<?php echo \Lobby::u();?>">
    <label class="input-field">
      <input type="checkbox" name="startOnLobbyOpen" <?php echo $this->getData("startOnLobbyOpen") === "1" ? "checked='checked'" : "";?> />
      <span>Start all active downloads when Lobby is opened<br/>This means downloads will be resumed before Downloader App is opened</span>
    </label>
    <div class="input-field">
      <button class="btn green" name="save">Save</button>
    </div>
  </form>
</div>
