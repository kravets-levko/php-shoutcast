<?php

  ini_set('display_errors', '1');
  error_reporting(E_ALL);

  @ini_set('output_buffering', 'Off');
  @ini_set('zlib.output_compression', 'Off');
  @ini_set('implicit_flush', 'On');
  @ob_end_clean();
  @ob_implicit_flush(true);

  chdir(__DIR__); // explicitly set current directory

  require_once __DIR__ . '/vendor/autoload.php';

  function get_config() {
    $result = @file_get_contents(__DIR__ . '/station.json');
    $result = @json_decode($result, true);
    return is_array($result) ? $result : [];
  }

  file_put_contents(__DIR__ . '/dump.log', '');
  function write_log($var) {
    file_put_contents(__DIR__ . '/dump.log', print_r($var, true), FILE_APPEND);
  }

  $station = new Shoutcast\Station(get_config());
  $station -> run();
