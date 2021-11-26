<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\RemarkdContext;

class HeadingBlock implements BlockInterface, BlockStartCodes
{
  protected $_heading = '';
  protected $_level;

  public function addNewLine(string $line)
  {
    $line = BlockEngine::trimLine($line);
    $this->_level = substr_count($line, '#', 0);
    $this->_heading = trim(substr(trim($line), $this->_level));
    return null;
  }

  public function complete(RemarkdContext $ctx): string
  {
    return $ctx->ruleEngine()->parse('<h' . $this->_level . '>' . $this->_heading . '</h' . $this->_level . '>');
  }

  public function startCodes(): array
  {
    return ['# ', '##',];
  }

}
