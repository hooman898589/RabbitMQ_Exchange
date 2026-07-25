# RabbitMQ Fanout Exchange (PHP)

این مثال نحوه استفاده از **Fanout Exchange** در RabbitMQ با استفاده از کتابخانه `php-amqplib` را نشان می‌دهد.

---

# ساختار پروژه

```
exchange_fanout/
│
├── publisher.php
├── client.php
└── README.md
```

---

# Fanout Exchange چیست؟

در **Fanout Exchange**، پیام برای **تمام Queueهایی که به Exchange متصل (Bind) شده‌اند** ارسال می‌شود.

در این نوع Exchange، **Routing Key هیچ اهمیتی ندارد** و RabbitMQ آن را نادیده می‌گیرد.

---

# معماری پروژه

```
                Publisher
                    │
                    ▼
      Fanout Exchange (email_exchange)
                    │
          ┌─────────┼─────────┐
          ▼         ▼         ▼
      Queue1    Queue2    Queue3
          │         │         │
          ▼         ▼         ▼
     Consumer1 Consumer2 Consumer3
```

هر Queue که به Exchange متصل باشد، یک نسخه از پیام را دریافت خواهد کرد.

---

# Publisher

ابتدا Exchange از نوع Fanout ایجاد می‌شود.

```php
$channel->exchange_declare(
    'email_exchange',
    'fanout',
    false,
    true,
    false
);
```

سپس پیام ارسال می‌شود.

```php
$channel->basic_publish(
    new AMQPMessage("hello"),
    'email_exchange'
);
```

توجه کنید که در Fanout نیازی به Routing Key نیست.

---

# Consumer

Consumer یک Queue به نام `email` ایجاد می‌کند.

```php
list($queue, $msg_count,) = $channel->queue_declare(
    'email',
    false,
    true,
    false,
    false
);
```

سپس Queue به Exchange متصل می‌شود.

```php
$channel->queue_bind(
    $queue,
    'email_exchange'
);
```

هر پیامی که به Exchange ارسال شود، وارد این Queue خواهد شد.

---

# اجرای مثال

ابتدا Consumer را اجرا کنید.

```bash
php client.php
```

سپس Publisher را اجرا کنید.

```bash
php publisher.php
```

خروجی

```
0 new messages
[x] hello
```

---

# اگر چند Consumer داشته باشیم

فرض کنید سه Queue مختلف به Exchange متصل باشند.

```
                Fanout Exchange
                       │
         ┌─────────────┼─────────────┐
         ▼             ▼             ▼
      email        sms          notification
         │             │             │
         ▼             ▼             ▼
   Email Service  SMS Service  Notification Service
```

اگر Publisher فقط یک پیام ارسال کند،

```
hello
```

هر سه Queue آن را دریافت خواهند کرد.

---

# تفاوت Fanout و Direct

## Direct Exchange

```
Routing Key بررسی می‌شود.
```

```
Publisher
     │
     ▼
Direct Exchange
     │
     ├── sms
     ├── email
     └── payment
```

فقط Queueهایی که Routing Key آن‌ها با پیام برابر باشد، پیام را دریافت می‌کنند.

---

## Fanout Exchange

```
Routing Key نادیده گرفته می‌شود.
```

```
Publisher
     │
     ▼
Fanout Exchange
     │
 ┌───┼────┐
 ▼   ▼    ▼
Q1  Q2   Q3
```

تمام Queueهای متصل پیام را دریافت می‌کنند.

---

# فایل‌های پروژه

| فایل | توضیح |
|------|-------|
| `publisher.php` | ایجاد Fanout Exchange و ارسال پیام |
| `client.php` | ایجاد Queue، اتصال آن به Exchange و دریافت پیام |

---

# نکات

- Fanout Exchange برای **Broadcast** یا ارسال هم‌زمان یک پیام به چند سرویس استفاده می‌شود.
- Routing Key در Fanout بررسی نمی‌شود.
- اگر هنگام Publish هیچ Queueای به Exchange متصل نباشد، پیام از بین می‌رود.
- هر Queue متصل، **نسخه مستقل** خود از پیام را دریافت می‌کند.
- پس از پردازش موفق پیام، با `ack()` پیام از Queue حذف می‌شود.

---

# کاربردها

Fanout Exchange معمولاً در سناریوهای زیر استفاده می‌شود:

- ثبت لاگ (Logging)
- ارسال اعلان (Notifications)
- همگام‌سازی Cache
- انتشار Event بین چند Microservice
- Broadcast کردن پیام برای چند Consumer