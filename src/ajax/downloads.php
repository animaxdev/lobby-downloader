<div id="downloads">
  <?php
  $ds = \H::getJSONData("downloads");
  if($ds === null){
    ser("No Downloads", "Why don't you download some stuff ?");
  }else{
    $ds = array_keys($ds);
    foreach($ds as $dName){
      $dInfo = \H::getJSONData($dName);
      $percentage = $dInfo['percentage'];
    ?>
      <div class='card' data-id="<?php echo $dName;?>" <?php if($percentage != "100"){ echo "data-active='1'"; }?>>
        <div class='card-content'>
          <span class="card-title"><?php echo $dInfo['url'];?></span>
          <p>
            <span>Downloading To <?php echo $dInfo['downloadDir'] . DIRECTORY_SEPARATOR . $dInfo['fileName'];?></span>
            <div class="progress">
              <div class="determinate" style="width: <?php echo $percentage;?>%"></div>
            </div>
            <div class="download-info">
              <?php
              if($dInfo['error'] != "0"){
              ?>
                <span class="red">Download <b>Failed</b> - <?php echo $dInfo['error'];?></span>
              <?php
              }else if($percentage == "0"){
              ?>
                <span>Download started - Establishing connection with server</span>
              <?php
              }else if($percentage == "100"){
              ?>
                <span>Download Finished</span>
              <?php
              }else{
                $percentage = round($percentage, 2);
              ?>
                <span class="chip">Downloaded <?php echo $this->convertToReadableSize($dInfo['downloaded']) . " of " . $this->convertToReadableSize($dInfo['size']) . " ($percentage%)";?></span>
                <div class="chip"><?php echo $this->convertToReadableSize($dInfo['speed']);?>/S</div>
                <div class="chip"><?php echo $dInfo['eta'];?> seconds remaining</div>
              <?php
              }
              ?>
            </div>
          </p>
          <a id="removeDownload"></a>
        </div>
      </div>
    <?php
    }
  }
  ?>
</div>
