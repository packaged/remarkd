<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Tags\Div;

class CalloutBlock extends BasicBlock implements BlockMatcher
{
  protected $_tag = Div::class;
  protected $_contentType = Block::TYPE_SIMPLE;

  public function match($line): ?Block
  {
    if(preg_match('/^\<\d+\> (.*)/', $line))
    {
      return clone $this;
    }
    return null;
  }

  public function allowLine(string $line): bool
  {
    return empty($this->_children) || $line[0] != '<';
  }

}
