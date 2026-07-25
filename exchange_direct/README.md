# RabbitMQ Direct Exchange (PHP)

این مثال نحوه استفاده از **Direct Exchange** در RabbitMQ با استفاده از کتابخانه `php-amqplib` را نشان می‌دهد.

---

# ساختار پروژه

```
exchange_direct/
│
├── publisher.php
├── client.php
└── README.md
```

---

# Direct Exchange چیست؟

در **Direct Exchange** پیام‌ها بر اساس **Routing Key** به Queue مناسب ارسال می‌شوند.

پیام تنها به Queueهایی ارسال می‌شود که Routing Key آن‌ها دقیقاً با Routing Key پیام برابر باشد.

---

# معماری پروژه

```
                 Routing Key = sms

                Publisher
                    │
                    ▼
         Exchange (exchange_order)
                    │
          ┌─────────┴─────────┐
          ▼                   ▼
      Queue(sms)         Queue(email)
      Binding=sms        Binding=sms
          │                   │
          ▼                   ▼
      SMS Service       Email Service
```

در این مثال هر دو Queue با Routing Key برابر با `sms` به Exchange متصل شده‌اند، بنابراین هر دو پیام را دریافت خواهند کرد.

---

# Publisher

ابتدا Exchange از نوع Direct ساخته می‌شود.

```php
$channel->exchange_declare(
    'exchange_order',
    'direct',
    false,
    true,
    false
);
```

سپس دو Queue ایجاد می‌شوند.

```php
$channel->queue_declare('sms', false, true, false, false);
$channel->queue_declare('email', false, true, false, false);
```

هر دو Queue با Routing Key برابر با `sms` به Exchange متصل می‌شوند.

```php
$channel->queue_bind(
    'sms',
    'exchange_order',
    'sms'
);

$channel->queue_bind(
    'email',
    'exchange_order',
    'sms'
);
```

در نهایت پیام ارسال می‌شود.

```php
$channel->basic_publish(
    new AMQPMessage("hello"),
    'exchange_order',
    'sms'
);
```

---

# Consumer

Consumer روی Queue زیر منتظر دریافت پیام است.

```php
email
```

پس از دریافت پیام، آن را نمایش داده و Ack می‌کند.

```php
echo $msg->body;

$msg->ack();
```

---

# نحوه اجرا

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
hello
```

---

# Routing Key

در این مثال Routing Key برابر است با:

```
sms
```

از آنجایی که هر دو Queue با همین Routing Key Bind شده‌اند،

```
Queue sms
Queue email
```

هر دو پیام را دریافت خواهند کرد.

اگر Queue دیگری با Routing Key متفاوت داشته باشیم:

```
payment
```

پیام را دریافت نخواهد کرد.

---

# مثال

| Routing Key پیام | Binding Queue | نتیجه |
|------------------|---------------|--------|
| sms | sms | ✅ |
| sms | email (bind=sms) | ✅ |
| sms | payment | ❌ |
| email | sms | ❌ |

---

# تفاوت Direct و Fanout

## Direct

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

فقط Queueهایی که Routing Key آن‌ها مطابق پیام باشد، پیام را دریافت می‌کنند.

---

## Fanout

```
Routing Key نادیده گرفته می‌شود.
```

تمام Queueهای متصل پیام را دریافت می‌کنند.

---

# فایل‌های پروژه

| فایل | توضیح |
|------|-------|
| `publisher.php` | ایجاد Exchange، Queue و ارسال پیام |
| `client.php` | دریافت پیام از Queue و Ack کردن آن |

---

# نکات

- در Direct Exchange، **Routing Key باید با Binding Key دقیقاً برابر باشد.**
- چند Queue می‌توانند با یک Routing Key یکسان به Exchange متصل شوند؛ در این صورت **همه آن Queueها** پیام را دریافت می‌کنند.
- اگر هیچ Queueای با Routing Key مربوطه وجود نداشته باشد، پیام به هیچ Queueای ارسال نخواهد شد (مگر تنظیمات دیگری مانند Alternate Exchange استفاده شده باشد).
- پس از پردازش موفق پیام، با `ack()` پیام از Queue حذف می‌شود.