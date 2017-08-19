<?php

namespace Shoutcast\Interfaces\Stream;

interface IMetadataSource {

  public function getMetadata(): array;

  public function getArtist(): string;

  public function getTitle(): string;

  public function getDuration(): int;

}
