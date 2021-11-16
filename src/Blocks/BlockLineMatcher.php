<?php
namespace Packaged\Remarkd\Blocks;

interface BlockLineMatcher
{
  public function match(string $line, bool $nested): ?BlockInterface;
}
