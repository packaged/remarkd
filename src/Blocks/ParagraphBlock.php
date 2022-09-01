<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Tags\Text\Paragraph;

class ParagraphBlock extends BasicBlock
{
  protected $_tag = Paragraph::class;
  protected $_allowChildren = false;
}
