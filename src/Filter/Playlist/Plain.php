<?php

namespace Shoutcast\Filter\Playlist;

use \Shoutcast\Filter\Playlist\Loader\Blank as BlankLoader;
use \Shoutcast\Filter\Playlist\Loader\Inline as InlineLoader;
use \Shoutcast\Filter\Playlist\Loader\Json as JsonLoader;
use \Shoutcast\Filter\Playlist\Loader\M3U as M3ULoader;
use \Shoutcast\Filter\Playlist\Loader\Path as PathLoader;

class Plain extends Blank {

  protected static $loader = [
    'blank' => BlankLoader::class,
    'inline' => InlineLoader::class,
    'json' => JsonLoader::class,
    'm3u' => M3ULoader::class,
    'path' => PathLoader::class,
  ];

  protected function loadItems(string $loader, array $config = []) {
    if (array_key_exists($loader, static::$loader)) {
      $class = static::$loader[$loader];
      /**
       * @var BlankLoader $loader
       */
      $loader = new $class($config);
      return $loader -> getItems();
    }
    return null;
  }

  protected function loadPlaylistItems(): ?array {
    $loaders = @$this -> config['items'];
    if (!is_array($loaders)) $loaders = [];

    $result = [];
    foreach ($loaders as $config) {
      if (is_array($config)) {
        $loader = @$config['loader'];
        $enabled = @$config['enabled'];
        if ($enabled) {
          unset($config['loader'], $config['enabled']);
          $items = $this -> loadItems($loader, $config);
          if (is_array($items)) $result = array_merge($result, array_values($items));
        }
      }
    }
    return $result;
  }

}
