<?php

namespace Shoutcast\Filter\Playlist;

use Shoutcast\Filter;
use Shoutcast\Interfaces\Connector\IOutputMediaSource;
use Shoutcast\Interfaces\Stream\IMediaSource;

class Blank extends Filter implements IOutputMediaSource, IMediaSource {

  protected $items = [];
  protected $current = -1;

  protected function loadPlaylistItems(): ?array {
    return [];
  }

  protected function getNextItemIndex(int $currentIndex, array $items = []): int {
    return $currentIndex + 1;
  }

  protected function afterConstruction() {
    $this -> current = -1;

    $items = $this -> loadPlaylistItems();
    if (!is_array($items)) $items = [];

    $this -> items = array_filter(array_map(function($item) {
      if (is_array($item)) return $item;
      if (is_string($item)) {
        return [
          'filename' => $item,
        ];
      }
      return null;
    }, $items));
  }

  public function getOutputMediaSource(): IMediaSource {
    return $this;
  }

  public function getPlaylist(): array {
    return $this -> items;
  }

  public function getCurrentMedia(): ?array {
    if (array_key_exists($this -> current, $this -> items)) {
      return $this -> items[$this -> current];
    }
    return null;
  }

  public function getNextMedia(): ?array {
    if (count($this -> items) > 0) {
      $this -> current = $this -> getNextItemIndex($this -> current, $this -> items);
      // Check if the end of playlist is reached
      if (($this -> current >= 0) && ($this -> current < count($this -> items))) {
        return $this -> items[$this -> current];
      }
    }
    return null;
  }

}
