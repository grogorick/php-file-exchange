<?php

class DirectoryConfig {
  private const CONFIG_FILE = 'DIRECTORIES_CONFIG.json';

  private static $configFile = null;
  private static $config = [];

  public static function load($path)
  {
    self::$configFile = $path . self::CONFIG_FILE;
    if (is_file(self::$configFile)) {
      self::$config = json_decode(file_get_contents(self::$configFile), true);
    }
  }

  public static function save()
  {
    file_put_contents(self::$configFile, json_encode(self::$config));
  }

  public static function set($id, $conf)
  {
    self::$config[$id] = $conf;
    self::save();
  }

  public static function delete($id)
  {
    unset(self::$config[$id]);
    self::save();
  }

  public static function get($id)
  {
    return self::$config[$id];
  }

  public static function dict()
  {
    return self::$config;
  }
}