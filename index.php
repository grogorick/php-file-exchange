<?php
require('localization/localization.php');
LOCALIZATION\INIT_FROM_FILE('localization.yaml');
LOCALIZATION\SET_LOCALE($_GET['lang'] ?? null);
use function LOCALIZATION\L;


$dir = $_GET['dir'] ?? 'FILES';
if (!in_array(mb_substr($dir, -1), ['\\', '/']))
  $dir .= '/';
define('DIR', $dir);

if (!is_dir(DIR))
  die(L('precheck_directory_not_found', DIR));
if (!is_writable(DIR))
  die(L('precheck_directory_not_writable', DIR));

define('FILES', array_filter(scandir(DIR), fn($file) => is_file(DIR . $file)));


$allowed_file_extensions = []; // ['.jpg', '.png', '.zip'];
$prohibited_file_extensions = ['.htm', '.pdf'];
$max_file_size = null; // '10M';
$disk_quota = '100M';


require('php/utils.php');

if (is_null($max_file_size))
  $max_file_size = parse_file_size(ini_get('upload_max_filesize'));
else
  $max_file_size = parse_file_size($max_file_size);

$disk_quota = parse_file_size($disk_quota);


require('php/actions.php');
require('php/view.php');