<?php
require('localization/localization.php');
LOCALIZATION\INIT_FROM_FILE('localization.yaml');
LOCALIZATION\SET_LOCALE($_GET['lang'] ?? null);
use function LOCALIZATION\L;


if (!isset($_GET['dir'])) {
  require('php/view.php');
  exit();
}


require('php/config.php');
DirectoryConfig::load('./');
$conf = DirectoryConfig::get($_GET['dir']);

if (is_null($conf))
  die(L('invalid_config_id', $_GET['dir']));

define('DIR', $conf['dir']);

if (!is_dir(DIR))
  die(L('precheck_directory_not_found', DIR));
if (!is_writable(DIR))
  die(L('precheck_directory_not_writable', DIR));

define('FILES', array_filter(scandir(DIR), fn($file) => is_file(DIR . $file)));


$dir_name = basename($conf['dir']);
$allowed_file_extensions = $conf['allowed_ext'];
$prohibited_file_extensions = $conf['prohibited_ext'];
$max_file_size = $conf['max_file_size'];
$disk_quota = $conf['disk_quota'];


require('php/utils.php');

if (!is_null($max_file_size))
  $max_file_size = parse_file_size($max_file_size);
else
  $max_file_size = try_get_server_upload_max_filesize();

$disk_quota = parse_file_size($disk_quota);


require('php/actions.php');
require('php/view.php');