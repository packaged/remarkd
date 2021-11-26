<?php
namespace Packaged\Remarkd\Markup;

class MarkupResource
{
  const TYPE_CSS = 'css';
  const TYPE_JS = 'js';
  const TYPE_HTML = 'html';

  public $type = self::TYPE_HTML;
  public $key = '';
  public $content = '';
}
