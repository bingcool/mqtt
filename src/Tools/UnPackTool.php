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

use Simps\MQTT\Exception\LengthException;

class UnPackTool extends Common
{
    public static function getType(string $data)
    {
        return ord($data[0]) >> 4;
    }

    public static function string(&$remaining)
    {
        $length = unpack('n', $remaining)[1];
        if ($length + 2 > strlen($remaining)) {
            throw new LengthException("unpack remaining length error, get {$length}");
        }
        $string = substr($remaining, 2, $length);
        $remaining = substr($remaining, $length + 2);

        return $string;
    }

    public static function shortInt(&$remaining)
    {
        $tmp = unpack('n', $remaining);
        $remaining = substr($remaining, 2);

        return $tmp[1];
    }

    public static function longInt(&$remaining)
    {
        $tmp = unpack('N', $remaining);
        $remaining = substr($remaining, 4);

        return $tmp[1];
    }

    private static function getRemainingLength(string $data, &$headBytes)
    {
        $headBytes = $multiplier = 1;
        $value = 0;
        do {
            if (!isset($data[$headBytes])) {
                throw new LengthException('Malformed Remaining Length');
            }
            $digit = ord($data[$headBytes]);
            $value += ($digit & 127) * $multiplier;
            $multiplier *= 128;
            ++$headBytes;
        } while (($digit & 128) != 0);

        return $value;
    }

    public static function getRemaining(string $data)
    {
        $remainingLength = static::getRemainingLength($data, $headBytes);

        return substr($data, $headBytes, $remainingLength);
    }
}
