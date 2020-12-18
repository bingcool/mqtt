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

namespace Simps\MQTT;

use Simps\MQTT\Exception\InvalidArgumentException;
use Simps\MQTT\Exception\RuntimeException;
use Simps\MQTT\Packet\Pack;
use Simps\MQTT\Packet\UnPack;
use Simps\MQTT\Tools\PackTool;
use Simps\MQTT\Tools\UnPackTool;
use Throwable;
use TypeError;

class Protocol
{
    public static function pack(array $array)
    {
        try {
            $type = $array['type'];
            switch ($type) {
                case Types::CONNECT:
                    $package = Pack::connect($array);
                    break;
                case Types::CONNACK:
                    $package = Pack::connAck($array);
                    break;
                case Types::PUBLISH:
                    $package = Pack::publish($array);
                    break;
                case Types::PUBACK:
                case Types::PUBREC:
                case Types::PUBREL:
                case Types::PUBCOMP:
                case Types::UNSUBACK:
                    $body = pack('n', $array['message_id']);
                    if ($type === Types::PUBREL) {
                        $head = PackTool::packHeader($type, strlen($body), 0, 1);
                    } else {
                        $head = PackTool::packHeader($type, strlen($body));
                    }
                    $package = $head . $body;
                    break;
                case Types::SUBSCRIBE:
                    $package = Pack::subscribe($array);
                    break;
                case Types::SUBACK:
                    $package = Pack::subAck($array);
                    break;
                case Types::UNSUBSCRIBE:
                    $package = Pack::unSubscribe($array);
                    break;
                case Types::PINGREQ:
                case Types::PINGRESP:
                case Types::DISCONNECT:
                    $package = PackTool::packHeader($type, 0);
                    break;
                default:
                    throw new InvalidArgumentException('MQTT Type not exist');
            }
        } catch (TypeError $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode());
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode());
        }

        return $package;
    }

    public static function unpack(string $data)
    {
        try {
            $type = UnPackTool::getType($data);
            $remaining = UnPackTool::getRemaining($data);
            switch ($type) {
                case Types::CONNECT:
                    $package = UnPack::connect($remaining);
                    break;
                case Types::CONNACK:
                    $package = UnPack::connAck($remaining);
                    break;
                case Types::PUBLISH:
                    $dup = ord($data[0]) >> 3 & 0x1;
                    $qos = ord($data[0]) >> 1 & 0x3;
                    $retain = ord($data[0]) & 0x1;
                    $package = UnPack::publish($dup, $qos, $retain, $remaining);
                    break;
                case Types::PUBACK:
                case Types::PUBREC:
                case Types::PUBREL:
                case Types::PUBCOMP:
                case Types::UNSUBACK:
                    $package = ['type' => $type, 'message_id' => unpack('n', $remaining)[1]];
                    break;
                case Types::PINGREQ:
                case Types::PINGRESP:
                case Types::DISCONNECT:
                    $package = ['type' => $type];
                    break;
                case Types::SUBSCRIBE:
                    $package = UnPack::subscribe($remaining);
                    break;
                case Types::SUBACK:
                    $package = UnPack::subAck($remaining);
                    break;
                case Types::UNSUBSCRIBE:
                    $package = UnPack::unSubscribe($remaining);
                    break;
                default:
                    $package = [];
            }
        } catch (TypeError $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode());
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode());
        }

        return $package;
    }
}
