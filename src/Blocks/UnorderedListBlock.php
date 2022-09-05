<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Tags\Lists\UnorderedList;

class UnorderedListBlock extends BasicBlock
{
  protected $_contentType = Block::TYPE_COMPOUND;
  protected $_allowChildren = true;
  protected $_tag = UnorderedList::class;
}
