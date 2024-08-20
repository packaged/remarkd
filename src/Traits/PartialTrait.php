<?php

namespace Packaged\Remarkd\Traits;

use Packaged\Helpers\Path;

class PartialTrait extends AbstractTraits
{
  public function getIdentifier(): string
  {
    return 'partial';
  }

  function parse(array &$rawLines, string $currentLine, array $options): string
  {
    // find current line in raw lines
    $key = array_search($currentLine, $rawLines, true);
    $filename = $options[1];
    $path = Path::system($this->_context->getProjectRoot(), $filename);
    if(file_exists($path))
    {
      $lines = file_get_contents($path);
      $lines = explode("\n", $lines);

      unset($rawLines[$key]);
      array_splice($rawLines, $key, 0, $lines);
    }

    return '';
  }
}
