<?php

namespace Shoutcast\Interfaces\Stream;

interface IDataStream {

  public function readData(int $limit): ?string;

}