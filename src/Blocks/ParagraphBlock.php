<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Tags\LineBreak;
use Packaged\Glimpse\Tags\Text\Paragraph;
use Packaged\Remarkd\RemarkdContext;

class ParagraphBlock extends BasicBlock
{
  protected $_tag = Paragraph::class;
  protected $_allowChildren = false;
  protected $_contentType = Block::TYPE_SIMPLE;

  public function appendLine(RemarkdContext $ctx, string $line): bool
  {
    if($this->_attr && $this->_attr->has('%hardbreaks') && !empty($this->children()))
    {
      $this->addChild(new LineBreak());
    }
    return parent::appendLine($ctx, $line);
  }
}
