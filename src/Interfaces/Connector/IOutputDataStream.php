<?php

namespace Shoutcast\Interfaces\Connector;

use Shoutcast\Interfaces\Stream\IDataStream;

interface IOutputDataStream {

  public function getOutputDataStream(): IDataStream;

}
