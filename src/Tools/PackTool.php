<?php
/**
 * This file is part of Simps
 *
 * @link     https://github.com/simps/mqtt
 * @contact  Lu Fei <lufei@simps.io>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code
 */

declare(strict_types=1);

namespace Simps\MQTT\Tools;

class PackTool extends Common
{
    public static function string(string $str): string
    {
        $len = strlen($str);

        return pack('n', $len) . $str;
    }

    public static function longInt($int)
    {
        return pack('N', $int);
    }

    public static function shortInt($int)
    {
        return pack('n', $int);
    }

    public static function packHeader(int $type, int $bodyLength, int $dup = 0, int $qos = 0, int $retain = 0): string
    {
        $type = $type << 4;
        if ($dup) {
            $type |= 1 << 3;
        }
        if ($qos) {
            $type |= $qos << 1;
        }
        if ($retain) {
            $type |= 1;
        }

        return chr($type) . static::packRemainingLength($bodyLength);
    }

    private static function packRemainingLength(int $bodyLength)
    {
        $string = '';
        do {
            $digit = $bodyLength % 128;
            $bodyLength = $bodyLength >> 7;
            if ($bodyLength > 0) {
                $digit = ($digit | 0x80);
            }
            $string .= chr($digit);
        } while ($bodyLength > 0);

        return $string;
    }
}
