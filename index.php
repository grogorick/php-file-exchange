<?php
require('localization/localization.php');
LOCALIZATION\INIT_FROM_FILE('localization.yaml');
LOCALIZATION\SET_LOCALE($_GET['lang'] ?? null);
use function LOCALIZATION\L;

$dir = $_GET['dir'] ?? './FILES/';
if (!in_array(mb_substr($dir, -1), ['\\', '/']))
  $dir += '/';
define('DIR', $dir);

require('php/utils.php');

$allowed_file_extensions = []; // ['.jpg', '.png', '.zip'];
$prohibited_file_extensions = ['.htm', '.pdf'];
$max_file_size = null; // '10M';
$disk_quota = '100M';

if (is_null($max_file_size))
  $max_file_size = parse_file_size(ini_get('upload_max_filesize'));
else
  $max_file_size = parse_file_size($max_file_size);

$disk_quota = parse_file_size($disk_quota);

if (!is_dir(DIR)) {
  if (file_exists((DIR)))
    die(L('precheck_path_is_file', DIR));
  elseif (!mkdir(DIR, 0777, true))
    die(L('precheck_mkdir_failed', DIR));
}

require('php/actions.php');
require('php/view.php');