<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Tags\Div;

class ListingBlock extends ContainerBlock
{
  protected $_contentType = Block::TYPE_VERBATIM;
  protected $_tag = Div::class;
  protected $_allowChildren = true;
  protected $_class = ['listing-block'];
  protected $_closer = '----';

  protected $_match = '/^-{4,10}$/';
}
