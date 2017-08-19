<?php

namespace Shoutcast\Interfaces\Stream;

interface IMediaSource {

  public function getPlaylist(): array;

  public function getCurrentMedia(): ?array;

  public function getNextMedia(): ?array;

}
