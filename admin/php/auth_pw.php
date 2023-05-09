<?php
session_start();

require('auth.php');

class Auth implements AuthBase
{
  /***************************************************************************/
  private const PW = 'please change';
  /***************************************************************************/

  private const SESSION_VAR = 'file-exchange-auth-pw';

  public static function is_logged_in()
  {
    return isset($_SESSION[self::SESSION_VAR]);
  }

  public static function logout()
  {
    unset($_SESSION[self::SESSION_VAR]);
  }

  public static function handle_login()
  {
    if (isset($_POST['pw'])) {
      if ($_POST['pw'] == self::PW) {
        $_SESSION[self::SESSION_VAR] = true;
        return;
      }
      else {
        $login_failed = true;
      }
    }
?>
<!DOCTYPE html>
<html lang="en" xml:lang="en">
<head>
  <meta charset="utf-8">
  <meta name="robots" content="noindex,nofollow" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>File Exchange</title>
</head>
<body>
  <form method="post">
    <input type="password" name="pw" autofocus>
    <input type="submit" value="Login">
  </form>
  <?=isset($login_failed) ? 'Falsches Passwort' : ''?>
</body>
</html>
<?php
  }
}