<?php
use function LOCALIZATION\L;
?>
<!DOCTYPE html>
<html lang="de" xml:lang="de">
<head>
  <meta charset="utf-8">
  <meta name="robots" content="noindex,nofollow" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>File Exchange</title>
  <link rel="stylesheet" href="css/style.css">
</head>

<body>
  <?php
    if (!defined('DIR')) {
      ?>

  <div id="messages">
    <form method="GET">
      <input name="dir" placeholder="<?=L('directory')?>">
      <input type="submit" value=">">
    </form>
  </div>
</body>
</html>
      <?php
      exit();
    }

    $used_disk_space = used_disk_space();

    $url_upload = add_url_params([
      'action' => 'upload',
      'no-js' => ''
    ]);
  ?>

  <div id="messages"></div>

  <form id="file-upload-form" action="./?<?=$url_upload?>" method="post" enctype="multipart/form-data">
    <div class="row">
      <input id="file-input" class="button" type="file" name="files[]" multiple>
      <label for="file-input" class="button hidden">+</label>
      <input type="submit" class="button" value="<?=L('upload')?>">
    </div>
  </form>

  <div id="overview" class="row">
    <div>
      <span id="used-disk-space"><?=file_size_str($used_disk_space)?></span> / <?=file_size_str($disk_quota)?>
    </div>
  </div>

  <div id="file-list">
    <?php
      function file_element($file_name = null)
      {
        $is_template = is_null($file_name);
        $url_download = add_url_params([
          'action' => 'download',
          'file' => $is_template ? '' : to_url($file_name)
        ]);
        $url_delete = add_url_params([
          'action' => 'delete',
          'no-js' => ''
        ]);
        ?>

    <div class="row item <?=$is_template ? 'hidden' : ''?>" <?=$is_template ? 'id="file-item-template"' : ''?>>
      <div class="file-name" data-value="<?=$file_name?>">
        <a class="download" download href="./?<?=$url_download?>"><?=$file_name?></a>
      </div>
      <div class="file-details">
        <span class="file-size" data-value="<?=$is_template ? '' : filesize(DIR . $file_name)?>"><?=$is_template ? '' : file_size($file_name)?></span>
        <span class="file-time"><?=$is_template ? '' : file_time($file_name)?></span>
      </div>
      <div class="file-delete">
        <form action="./?<?=$url_delete?>" method="post">
          <input type="hidden" name="file" value="<?=to_url($file_name)?>">
          <input class="button" type="submit" value="X">
        </form>
      </div>
    </div>
        <?php
      }

      foreach (FILES as $file_name)
        file_element($file_name);

      file_element();
    ?>

    <div class="row empty"><?=L('empty')?></div>
  </div>

  <div id="drag-drop-indicator" class="hidden"><div><div><?=L('drop_files')?></div></div></div>

  <script>
    let allowedFileExtensions = [<?=implode(', ', array_map(fn($ext) => '\'' . $ext . '\'', $allowed_file_extensions))?>];
    let prohibitedFileExtensions = [<?=implode(', ', array_map(fn($ext) => '\'' . $ext . '\'', $prohibited_file_extensions))?>];
    let maxFileSize = <?=$max_file_size?>;
    let usedDiskSpace = <?=$used_disk_space?>;
    let diskQuota = <?=$disk_quota?>;
  </script>
  <?=LOCALIZATION\INIT_JS()?>
  <script src="js/utils.js"></script>
  <script src="js/upload.js"></script>
  <script src="js/upload_dragndrop.js"></script>
  <script src="js/delete.js"></script>

</body>
</html>