<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\RemarkdContext;

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

  public function addLine(RemarkdContext $ctx, string $line): bool
  {
    if($line == $this->closer())
    {
      return empty($this->_children);
    }
    return parent::addLine($ctx, $line);
  }

}
