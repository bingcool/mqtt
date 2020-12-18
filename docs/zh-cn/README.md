# MQTT 协程客户端

适用于 PHP 的 MQTT 协议解析和协程客户端。

支持 MQTT 协议 `3.1`、`3.1.1` 和 `5.0` 版本，支持`QoS 0`、`QoS 1`、`QoS 2`。

## 依赖要求

* PHP >= `7.0`
* Swoole >= `4.4.19`
* mbstring PHP 扩展

## 安装

```bash
composer require simps/mqtt
```

## 示例

参考 [examples](https://github.com/simps/mqtt/tree/master/examples) 目录

## Client API

### __construct()

创建一个MQTT客户端实例

```php
Simps\MQTT\Client::__construct(array $config, array $swConfig = [], int $type = SWOOLE_SOCK_TCP)
```

创建一个适用于Fpm|Apache环境的MQTT客户端实例，主要用于publish消息，设置第四个参数clientType = \Simps\MQTT\Client::SYNC_CLIENT_TYPE
```php
Simps\MQTT\Client::__construct(array $config, array $swConfig = [], int $type = SWOOLE_SOCK_TCP, int clientType = \Simps\MQTT\Client::SYNC_CLIENT_TYPE)
```

* 参数`array $config`

客户端选项数组，可以设置以下选项：

```php
$config = [
    'host' => '127.0.0.1', // MQTT服务端IP
    'port' => 1883, // MQTT服务端端口
    'time_out' => 5, // 连接MQTT服务端超时时间，默认0.5秒
    'user_name' => '', // 用户名
    'password' => '', // 密码
    'client_id' => '', // 客户端id
    'keep_alive' => 10, // 默认0秒，设置成0代表禁用
    'protocol_name' => 'MQTT', // 协议名，默认为MQTT(3.1.1版本)，也可为MQIsdp(3.1版本)
    'protocol_level' => 4, // 协议等级，MQTT3.1.1版本为4，5.0版本为5，MQIsdp为3
    'properties' => [ // MQTT5 需要
        'session_expiry_interval' => 0,
        'receive_maximum' => 0,
        'topic_alias_maximum' => 0,
    ],
];
```

!> Client 会根据设置的`protocol_level`来使用对应的协议解析

* 参数`array $swConfig`

用于设置`Swoole\Coroutine\Client`的配置，请参考Swoole文档：[set()](https://wiki.swoole.com/#/coroutine_client/client?id=set)

### connect()

连接Broker

```php
Simps\MQTT\Client->connect(bool $clean = true, array $will = [])
```

* 参数`bool $clean`

清理会话，默认为`true`

具体描述请查看对应协议文档：`清理会话 Clean Session`

* 参数`array $will`

遗嘱消息，当客户端断线后Broker会自动发送遗嘱消息给其它客户端

需要设置的内容如下

```php
$will = [
    'topic' => '', // 主题
    'qos' => 1, // QoS等级
    'retain' => 0, // retain标记
    'content' => '', // content
];
```

### publish()

向某个主题发布一条消息

```php
Simps\MQTT\Client->publish($topic, $content, $qos = 0, $dup = 0, $retain = 0, array $properties = [])
```

* 参数`$topic` 主题
* 参数`$content` 内容
* 参数`$qos` QoS等级，默认0
* 参数`$dup` 重发标志，默认0
* 参数`$retain` retain标记，默认0
* 参数`$properties` 属性，MQTT5需要

### subscribe()

订阅一个主题或者多个主题

```php
Simps\MQTT\Client->subscribe(array $topics)
```

* 参数`array $topics`

`$topics`的`key`是主题，值为`QoS`的数组，例如

```php
// MQTT 3.x
$topics = [
    // 主题 => Qos
    'topic1' => 0, 
    'topic2' => 1,
];

// MQTT 5.0
$topics = [
    // 主题 => 选项
    'topic1' => [
        'qos' => 1,
        'no_local' => true,
        'retain_as_published' => true,
        'retain_handling' => 2,
    ], 
    'topic2' => [
        'qos' => 2,
        'no_local' => false,
        'retain_as_published' => true,
        'retain_handling' => 1,
    ], 
];
```

### unSubscribe()

取消订阅一个主题或者多个主题

```php
Simps\MQTT\Client->unSubscribe(array $topics)
```

* 参数`array $topics`

```php
$topics = ['topic1', 'topic2'];
```

### close()

正常断开与Broker的连接，`DISCONNECT(14)`报文会被发送到Broker

```php
Simps\MQTT\Client->close(int $code = ReasonCode::NORMAL_DISCONNECTION)
```

* 参数`$code` 响应码，MQTT5中需要，MQTT3直接调用即可

### recv()

接收消息

```php
Simps\MQTT\Client->recv(): bool|arary|string
```

### send()

发送消息

```php
Simps\MQTT\Client->send(array $data, $response = true)
```

* 参数`array $data`

`$data`是需要发送的数据，必须包含`type`等信息

* 参数`bool $response`

是否需要回执。如果为`true`，会调用一次`recv()`

### ping()

发送心跳包

```php
Simps\MQTT\Client->ping()
```

### buildMessageId()

生成MessageId

```php
Simps\MQTT\Client->buildMessageId()
```

### genClientId()

生成ClientId

```php
Simps\MQTT\Client->genClientID()
```
