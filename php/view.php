<?php
use function LOCALIZATION\L;
session_start();
?>
<!DOCTYPE html>
<html lang="de" xml:lang="de">
<head>
  <meta charset="utf-8">
  <meta name="robots" content="noindex,nofollow" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?=$dir_name?> &#183; File Exchange</title>
  <link rel="icon" type="image/png" href="favicon.png" />
  <link rel="stylesheet" href="css/style.css">
</head>

<body>
  <?php
    if (!defined('DIR')) {
      ?>

  <div id="messages" class="init">
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

    if (!is_null($password_hash) && !isset($_SESSION['password_approved_' . $conf_id])) {
      if (isset($_POST['password']) && check_password($_POST['password'], $password_hash)) {
        $_SESSION['password_approved_' . $conf_id] = true;
      }
      else {
        ?>

  <div id="messages" class="init">
    <form method="POST">
      <div><?=$dir_name?></div>
      <input name="password" type="password" placeholder="<?=L('password')?>">
      <input type="submit" value=">">
    </form>
  </div>
</body>
</html>
        <?php
        exit();
      }
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
    <div id="dir-name"><?=$dir_name?></div>
    <div>
      <span id="used-disk-space"><?=file_size_str($used_disk_space)?></span><?=$disk_quota ? ' / ' . file_size_str($disk_quota) : ''?>
      <div id="info">
        <div>
          <?=
            (!empty($allowed_file_extensions) ? '<span>' . L('allowed_file_extensions') . implode(', ', $allowed_file_extensions) . '</span>' : '') .
            (!empty($prohibited_file_extensions) ? '<span>' . L('prohibited_file_extensions') . implode(', ', $prohibited_file_extensions) . '</span>' : '') .
            ($max_file_size ? '<span>' . L('max_file_size') . $max_file_size . '</span>' : '')
          ?>
        </div>
      </div>
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