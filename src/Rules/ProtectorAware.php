<?php
namespace Packaged\Remarkd\Rules;

/**
 * Rules implementing this receive a protector from the RuleEngine that wraps
 * generated content (URLs, alt text) in passthrough tokens, shielding it from
 * later formatting rules. Tokens are restored when the engine finishes.
 */
interface ProtectorAware
{
  public function setProtector(?callable $protect);
}
