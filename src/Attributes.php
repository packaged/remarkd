<?php
namespace Packaged\Remarkd;

use ArrayAccess;

class Attributes implements ArrayAccess
{
  protected $_position = [];
  protected $_named = [];
  protected $_raw;

  public function __construct($raw = '')
  {
    $this->_raw = trim($raw, '[]');
    $matches = [];
    preg_match_all('/(^|[\s,]+)([^, =}]+)(=((\"([^\"]*)\")|([^\s,}]*)))?/', $this->_raw, $matches);
    if(isset($matches[2]))
    {
      foreach($matches[2] as $pos => $match)
      {
        $this->_position[$pos] = $match;
        $this->_named[$match] = $matches[7][$pos] ?: $matches[6][$pos];
      }
    }
  }

  public function raw(): string
  {
    return $this->_raw;
  }

  public function position(int $pos, bool $getValue = false): ?string
  {
    if($getValue)
    {
      return $this->_named[$this->_position[$pos]] ?? null;
    }
    return $this->_position[$pos] ?? null;
  }

  public function has(string $key): bool
  {
    return array_key_exists($key, $this->_named);
  }

  public function get(string $key, $default = null): ?string
  {
    return $this->_named[$key] ?? $default;
  }

  public function id(): ?string
  {
    foreach($this->_position as $k)
    {
      if($k[0] === '#')
      {
        return substr($k, 1);
      }
    }
    return null;
  }

  public function classes(): array
  {
    $classes = [];
    foreach($this->_position as $k)
    {
      if($k[0] === '.')
      {
        $classes[] = substr($k, 1);
      }
    }
    return $classes;
  }

  // ArrayAccess implementation to allow array-like read access
  public function offsetExists($offset): bool
  {
    if(is_int($offset))
    {
      return array_key_exists($offset, $this->_position);
    }
    if(is_string($offset))
    {
      return $this->has($offset);
    }
    return false;
  }

  public function offsetGet($offset):mixed
  {
    if(is_int($offset))
    {
      // Behave like position($pos)
      return $this->position($offset);
    }
    if(is_string($offset))
    {
      // Behave like get($key)
      return $this->get($offset);
    }
    return null;
  }

  public function offsetSet($offset, $value): void
  {
    // Attributes are intended to be immutable after construction in current design.
    // Disallow mutation via array access to avoid inconsistent state.
    throw new \LogicException('Attributes are read-only via array access');
  }

  public function offsetUnset($offset): void
  {
    // See note in offsetSet
    throw new \LogicException('Attributes are read-only via array access');
  }
}
