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
    $key = array_search($currentLine, $rawLines, true);
    if($key === false)
    {
      return '';
    }

    [$filename, $attributes] = $this->_parseOptions($options[1] ?? '');
    $path = Path::system($this->_context->getProjectRoot(), $filename);
    if(is_file($path))
    {
      $lines = file_get_contents($path);
      $lines = explode("\n", $lines);

      if($this->_enabled($attributes, 'strip-title') && isset($lines[0]) && strpos($lines[0], '=') === 0)
      {
        array_shift($lines);
      }

      while(!empty($lines) && trim(end($lines)) === '')
      {
        array_pop($lines);
      }

      $dropLast = (int)($attributes['drop-last'] ?? 0);
      if($dropLast > 0)
      {
        array_splice($lines, -$dropLast);
      }

      array_splice($rawLines, $key, 1, $lines);
      return '';
    }

    array_splice($rawLines, $key, 1, ["File not found: $filename"]);
    return '';
  }

  protected function _parseOptions(string $raw): array
  {
    $filename = trim($raw);
    $attributes = [];

    if(preg_match('/^(.*?)\[([^]]*)]$/', $filename, $matches))
    {
      $filename = trim($matches[1]);
      foreach(explode(',', $matches[2]) as $attribute)
      {
        $attribute = trim($attribute);
        if($attribute === '')
        {
          continue;
        }

        if(strpos($attribute, '=') !== false)
        {
          [$key, $value] = explode('=', $attribute, 2);
          $attributes[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
        }
        else
        {
          $attributes[$attribute] = true;
        }
      }
    }

    return [$filename, $attributes];
  }

  protected function _enabled(array $attributes, string $key): bool
  {
    if(!array_key_exists($key, $attributes))
    {
      return false;
    }

    return !in_array(strtolower((string)$attributes[$key]), ['0', 'false', 'no'], true);
  }
}
