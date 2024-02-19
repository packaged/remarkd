<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Tags\Div;

class StepsContainer extends BasicBlock
{
  protected $_contentType = Block::TYPE_COMPOUND;
  protected $_tag = Div::class;
  protected $_allowChildren = true;
  protected $_class = ['steps-container'];
  protected $_closer = '-|_';
}
