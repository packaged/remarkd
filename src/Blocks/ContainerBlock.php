<?php
namespace Packaged\Remarkd\Blocks;

class ContainerBlock extends BasicBlock implements BlockMatcher
{
  protected $_closeOnEmpty = false;
  /** @var string */
  protected $_match = '/^!{4,10}$/';

  public function match($line, ?Block $parent): ?Block
  {
    if($this->_match && preg_match($this->_match, $line))
    {
      $block = clone $this;
      $block->_closer = $line;
      return $block;
    }
    if($line === $this->_closer)
    {
      return clone $this;
    }
    return null;
  }

  public function allowChild($child): bool
  {
    if($this->closer() && $child instanceof Block)
    {
      return $this->closer() != $child->closer();
    }
    return parent::allowChild($child);
  }
}
