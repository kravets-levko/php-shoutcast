<?php

namespace Shoutcast\Filter\Playlist;

use Shoutcast\Interfaces\Connector\IInputMediaSource;
use Shoutcast\Interfaces\Connector\IOutputMediaSource;
use Shoutcast\Interfaces\Stream\IMediaSource;

class Random extends Blank implements IInputMediaSource, IOutputMediaSource, IMediaSource {

  protected function getNextItemIndex(int $currentIndex, array $items = []): int {
    if (count($this -> items) > 2) {
      // If there are more that two items - choose randomly...
      $result = rand(0, count($items) - 2);
      if ($result == $currentIndex) $result += 1;
    } else {
      // ... otherwise just loop
      $result = $currentIndex + 1;
    }
    return $result < count($items) ? $result : 0;
  }

  public function setInputMediaSource(IMediaSource $source) {
    $this -> items = $source -> getPlaylist();
    $this -> current = -1;
  }

  public function clearInputMediaSource() {
    $this -> items = [];
    $this -> current = -1;
  }

}
