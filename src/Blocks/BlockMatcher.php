<?php
namespace Packaged\Remarkd\Blocks;

interface BlockMatcher
{
  public function match($line): ?Block;
}
