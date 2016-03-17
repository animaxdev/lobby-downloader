<div id="downloads">
  <?php
  $ds = \H::getJSONData("downloads");
  if($ds === null){
    ser("No Downloads", "Why don't you download some stuff ?");
  }else{
    $ds = array_keys($ds);
    foreach($ds as $d){
      $dInfo = \H::getJSONData($d);
    ?>
      <div class='card' data-id="<?php echo $d;?>">
        <div class='card-content'>
          <span class="card-title"><?php echo $dInfo['url'];?></span>
          <p>
            <div class="progress">
              <div class="determinate" style="width: <?php echo $dInfo['downloaded'];?>"></div>
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
