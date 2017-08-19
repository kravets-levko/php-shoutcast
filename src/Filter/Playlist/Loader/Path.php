<?php

namespace Shoutcast\Filter\Playlist\Loader;

class Path extends Blank {

  protected function readDirectory($path, $patterns, &$result, $recursive) {
    $items = scandir($path);
    foreach ($items as $item) {
      if (($item == '.') || ($item == '..')) continue;
      $item = $path . '/' . $item;
      if (is_file($item)) {
        if (is_array($patterns)) {
          foreach ($patterns as $pattern) {
            if (fnmatch($pattern, $item)) {
              $result[] = $item;
              break;
            }
          }
        } else {
          $result[] = $item;
        }
      } elseif (is_dir($item) && $recursive) {
        $this -> readDirectory($item, $patterns, $result, $recursive);
      }
    }
  }

  public function getItems(): ?array {
    $path = $this -> config['path'];
    if (is_string($path)) $path = [$path];
    if (!is_array($path)) $path = [];
    $path = array_unique(array_map(function($path) {
      return rtrim(realpath($path), '/');
    }, $path));

    $recursive = @$this -> config['recursive'];

    $pattern = @$this -> config['pattern'];
    if (is_string($pattern)) $pattern = [$pattern];
    if (!is_array($pattern) || (count($pattern) == 0)) $pattern = null;

    $result = [];
    foreach ($path as $item) {
      $this -> readDirectory($item, $pattern, $result, $recursive);
    }
    return $result;
  }

}
