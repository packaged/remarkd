<?php
namespace Packaged\Remarkd\Rules;

class EmojiRule implements RemarkdownRule
{
  //data source : https://www.unicode.org/Public/emoji/14.0/emoji-test.txt
  protected static $_emoji = [
    '+1'                           => "\xF0\x9F\x91\x8D",
    'thumbsup'                     => "\xF0\x9F\x91\x8D",
    '_1'                           => "\xF0\x9F\x91\x8E",
    'thumbsdown'                   => "\xF0\x9F\x91\x8E",
    'eyes'                         => "\xF0\x9F\x91\x80",
    'grinning'                     => "\xF0\x9F\x98\x80",
    'grin'                         => "\xF0\x9F\x98\x81",
    'joy'                          => "\xF0\x9F\x98\x82",
    'smiley'                       => "\xF0\x9F\x98\x83",
    'smile'                        => "\xF0\x9F\x98\x84",
    'sweat_smile'                  => "\xF0\x9F\x98\x85",
    'satisfied'                    => "\xF0\x9F\x98\x86",
    'laughing'                     => "\xF0\x9F\x98\x86",
    'innocent'                     => "\xF0\x9F\x98\x87",
    'smiling_imp'                  => "\xF0\x9F\x98\x88",
    'wink'                         => "\xF0\x9F\x98\x89",
    'blush'                        => "\xF0\x9F\x98\x8A",
    'yum'                          => "\xF0\x9F\x98\x8B",
    'relieved'                     => "\xF0\x9F\x98\x8C",
    'heart_eyes'                   => "\xF0\x9F\x98\x8D",
    'sunglasses'                   => "\xF0\x9F\x98\x8E",
    'smirk'                        => "\xF0\x9F\x98\x8F",
    'neutral_face'                 => "\xF0\x9F\x98\x90",
    'expressionless'               => "\xF0\x9F\x98\x91",
    'unamused'                     => "\xF0\x9F\x98\x92",
    'sweat'                        => "\xF0\x9F\x98\x93",
    'pensive'                      => "\xF0\x9F\x98\x94",
    'confused'                     => "\xF0\x9F\x98\x95",
    'confounded'                   => "\xF0\x9F\x98\x96",
    'kissing'                      => "\xF0\x9F\x98\x97",
    'kissing_heart'                => "\xF0\x9F\x98\x98",
    'kissing_smiling_eyes'         => "\xF0\x9F\x98\x99",
    'kissing_closed_eyes'          => "\xF0\x9F\x98\x9A",
    'stuck_out_tongue'             => "\xF0\x9F\x98\x9B",
    'stuck_out_tongue_winking_eye' => "\xF0\x9F\x98\x9C",
    'stuck_out_tongue_closed_eyes' => "\xF0\x9F\x98\x9D",
    'disappointed'                 => "\xF0\x9F\x98\x9E",
    'worried'                      => "\xF0\x9F\x98\x9F",
    'angry'                        => "\xF0\x9F\x98\xA0",
    'rage'                         => "\xF0\x9F\x98\xA1",
    'cry'                          => "\xF0\x9F\x98\xA2",
    'persevere'                    => "\xF0\x9F\x98\xA3",
    'triumph'                      => "\xF0\x9F\x98\xA4",
    'disappointed_relieved'        => "\xF0\x9F\x98\xA5",
    'frowning'                     => "\xF0\x9F\x98\xA6",
    'anguished'                    => "\xF0\x9F\x98\xA7",
    'fearful'                      => "\xF0\x9F\x98\xA8",
    'weary'                        => "\xF0\x9F\x98\xA9",
    'sleepy'                       => "\xF0\x9F\x98\xAA",
    'tired_face'                   => "\xF0\x9F\x98\xAB",
  ];

  public function apply(string $text): string
  {
    return preg_replace_callback('/:(\S+):/', function (array $matches) {
      return self::$_emoji[str_replace('-', '_', $matches[1])] ?? $matches[0];
    }, $text);
  }
}
