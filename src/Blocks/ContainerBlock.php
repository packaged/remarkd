<?php
namespace Packaged\Remarkd\Blocks;

class ContainerBlock extends BasicBlock implements BlockMatcher
{
  public static function i($marker)
  {
    $block = new static();
    $block->_closer = $marker;
    return $block;
  }

  public function match($line): ?Block
  {
    if($line === $this->_closer)
    {
      return clone $this;
    }
    return null;
  }
}
