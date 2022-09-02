<?php
namespace Packaged\Remarkd\Blocks;

class CodeBlock extends ContainerBlock
{
  protected $_contentType = Block::TYPE_VERBATIM;
  protected $_tag = \Packaged\Glimpse\Tags\Text\CodeBlock::class;
  protected $_allowChildren = false;
  protected $_closer = '```';
}
