<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Tags\Lists\OrderedList;

class OrderedListBlock extends ListBlock
{
  protected $_tag = OrderedList::class;
  protected $_match = ListItemBlock::OL_MATCH;
}
