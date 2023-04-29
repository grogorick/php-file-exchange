<?php
require('localization/localization.php');
LOCALIZATION\INIT_FROM_FILE('localization.yaml');
LOCALIZATION\SET_LOCALE($_GET['lang'] ?? 'en');
use function LOCALIZATION\L;

require('utils.php');

$dir = $_GET['dir'] ?? './FILES/';
if (!in_array(mb_substr($dir, -1), ['\\', '/']))
  $dir += '/';
define('DIR', $dir);

$allowed_file_extensions = ['.jpg', '.png'];
$max_file_size = 1024 * 1024;

if (!is_dir(DIR)) {
  if (file_exists((DIR)))
    die(L('precheck_path_is_file', DIR));
  elseif (!mkdir(DIR, 0777, true))
    die(L('precheck_mkdir_failed', DIR));
}

function action_response($data)
{
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data);
  exit();
}

switch ($_GET['action']) {
  case 'upload':
    {
      $response = [];

      foreach ($_FILES['files']['error'] as $i => $error_code) {
        $file_name = $_FILES['files']['name'][$i];

        if ($error_code !== UPLOAD_ERR_OK) {
          $response[] = [$i, $file_name, L('upload_failed_error_code', $error_code)];
          continue;
        }

        $file_ext = strtolower(substr($file_name, strrpos($file_name, '.')));
        if (!in_array($file_ext, $allowed_file_extensions)) {
          $response[] = [$i, $file_name, L('upload_failed_file_type', $file_ext)];
          continue;
        }

        $file_size = $_FILES['files']['size'][$i];
        if ($file_size > $max_file_size) {
          $response[] = [$i, $file_name, L('upload_failed_file_size', file_size($file_size))];
          continue;
        }

        $file_tmp = $_FILES['files']['tmp_name'][$i];
        $upload_successful = move_uploaded_file($file_tmp, DIR . $file_name);
        if (!$upload_successful) {
          $response[] = [$i, $file_name, L('upload_failed_move_failed')];
          continue;
        }

        $response[] = [$i, $file_name, false];
      }
      action_response($response);
    }

  case 'deleteSource':
    {
      $file_name = $_GET['delete'];
      $file_path = DIR . $file_name;
      unset($file_path);
      action_response([$file_name]);
    }

  default:
}

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

  <form id="file-upload-form" action="./?action=upload" method="post" enctype="multipart/form-data">
    <div class="item-row">
      <input id="file-input" type="file" name="files[]" multiple><label for="file-input"
        class="button drag-drop-visual-area"><?=L('select_files')?></label>
      <input type="submit" class="button" value="<?=L('upload')?>">
    </div>
  </form>

  <div id="file-list">
    <?php
      function file_element($file_name = null)
      {
        $is_template = is_null($file_name);
        ?>
            <div <?=$is_template ? 'id="file-item-template"' : ''?> class="item-row <?=$is_template ? 'hidden' : ''?>">
              <div class="file-name"><?=$file_name?></div>
              <div class="file-details"><?=$file_name ? file_size($file_name) : ''?></div>
            </div>
        <?php
      }

      file_element();

      $files = scandir(DIR);
      foreach ($files as $file_name)
        if (is_file(DIR . $file_name))
          file_element($file_name);
    ?>
  </div>

  <div id="drag-drop-indicator" class="hidden"><div><div><?=L('drop_files')?></div></div></div>

  <script>
    let allowedFileExtensions = ['<?=implode('\', \'', $allowed_file_extensions)?>'];
    let maxFileSize = <?=$max_file_size?>;
  </script>
  <?=LOCALIZATION\INIT_JS()?>
  <script src="js/script.js"></script>

</body>
</html>