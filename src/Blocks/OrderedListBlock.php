<?php
namespace Packaged\Remarkd\Blocks;

class OrderedListBlock extends AbstractListBlock
{
  protected $_listType = 'ol';

  public function startCodes(): array
  {
    return [
      "0 ",
      "1 ",
      "2 ",
      "3 ",
      "4 ",
      "5 ",
      "6 ",
      "7 ",
      "8 ",
      "9 ",
      "0.",
      "1.",
      "2.",
      "3.",
      "4.",
      "5.",
      "6.",
      "7.",
      "8.",
      "9.",
    ];
  }

}
