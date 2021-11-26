<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\RemarkdContext;

class WellBlock implements BlockInterface, BlockStartCodes
{
  protected $_lines = [];

  public function addNewLine(string $line)
  {
    if(substr($line, 0, 2) !== '||')
    {
      return false;
    }
    $this->_lines[] = trim(substr($line, 2));
    return true;
  }

  public function complete(RemarkdContext $ctx): string
  {
    return '<div class="well">' . $ctx->ruleEngine()->parse(implode("<br/>", $this->_lines)) . '</div>';
  }

  public function startCodes(): array
  {
    return ['||'];
  }

}
