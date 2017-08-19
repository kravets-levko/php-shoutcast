<?php

namespace Shoutcast\Interfaces;

interface IDataSink {

  public function drownHeaders(array $headers);

  public function drownData(int $limit): bool;

}
