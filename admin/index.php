<?php
require('../localization/localization.php');
LOCALIZATION\INIT_FROM_FILE('localization.yaml');
LOCALIZATION\SET_LOCALE($_GET['lang'] ?? null);

require('php/auth_pw.php');
if (!Auth::is_logged_in()) {
  Auth::handle_login();
  if (Auth::is_logged_in())
    header('Location:' . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']);
  exit();
}

require('../php/config.php');
DirectoryConfig::load('../');

require('../php/utils.php');
define('SERVER_UPLOAD_MAX_FILESIZE', try_get_server_upload_max_filesize());

define('ALIAS_PATTERN', '[A-Za-z]\\w*');

require('php/actions.php');
require('php/view.php');