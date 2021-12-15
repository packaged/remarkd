<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\RemarkdContext;

class ParagraphBlock implements BlockInterface
{
  protected $_lines = [];

  public function addNewLine(string $line)
  {
    $line = BlockEngine::trimLine($line);
    if(empty($line))
    {
      return null;
    }
    $this->_lines[] = $line;
    return true;
  }

  public function complete(RemarkdContext $ctx): string
  {
    return '<p>' . $ctx->ruleEngine()->parse(implode(" ", $this->_lines)) . '</p>';
  }
}
