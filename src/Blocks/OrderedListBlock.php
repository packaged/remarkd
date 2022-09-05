<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Tags\Lists\OrderedList;

class OrderedListBlock extends BasicBlock
{
  protected $_contentType = Block::TYPE_COMPOUND;
  protected $_allowChildren = true;
  protected $_tag = OrderedList::class;
}
