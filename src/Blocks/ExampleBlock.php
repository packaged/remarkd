<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Tags\Div;

class ExampleBlock extends ContainerBlock
{
  protected $_contentType = Block::TYPE_COMPOUND;
  protected $_tag = Div::class;
  protected $_allowChildren = true;
  protected $_class = ['example-block'];
  protected $_closer = '====';

  protected $_match = '/^={4,10}$/';
}
