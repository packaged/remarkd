<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Tags\Lists\UnorderedList;

class UnorderedListBlock extends ListBlock
{
  protected $_tag = UnorderedList::class;
  protected $_match = ListItemBlock::UL_MATCH;
}
