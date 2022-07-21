<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\RemarkdContext;

class MermaidBlock implements BlockInterface, BlockLineMatcher
{
  protected $_lines = [];

  public function addNewLine(string $line)
  {
    if(trim($line) === '!mermaid') {
      return null;
    }

    if(trim($line) === 'mermaid!') {
      return true;
    }

    $this->_lines[] = $line;

    return true;
  }

  public function complete(RemarkdContext $ctx): string
  {
    return '<div class="mermaid" style="margin: 20px 0;">' . implode("\n", $this->_lines) . '</div>';
  }

  public function match(string $line, bool $nested): ?BlockInterface
  {
    if(trim($line) === 'mermaid!')
    {
      return new static();
    }

    return null;
  }
}
