# RabbitMQ Dead Letter Exchange (DLX) - PHP

این مثال نحوه استفاده از **Dead Letter Exchange (DLX)** در RabbitMQ را با استفاده از کتابخانه `php-amqplib` نشان می‌دهد.

---

# ساختار پروژه

```
exchange_dead/
│
├── publisher.php
├── client.php
├── dead_email.php
└── README.md
```

---

# Dead Letter Exchange چیست؟

Dead Letter Exchange یا **DLX** محلی است که RabbitMQ پیام‌هایی را که امکان پردازش آن‌ها وجود ندارد، به آن ارسال می‌کند.

به طور معمول پیام در شرایط زیر وارد DLX می‌شود:

- پیام Reject شود (`basic_reject` یا `basic_nack`) و `requeue=false` باشد.
- مدت زمان TTL پیام یا Queue به پایان برسد.
- Queue به حداکثر ظرفیت خود برسد.

---

# معماری پروژه

```
                 Publisher
                     │
                     ▼
          Exchange (orther_exchange)
                     │
                     ▼
               Queue (email)
                     │
             Consumer (client.php)
                     │
          reject(false)
                     │
                     ▼
      Dead Letter Exchange
      (orther_exchange-dead)
                     │
                     ▼
          Queue (email_dead)
                     │
                     ▼
        dead_email.php
```

---

# Publisher

در فایل `publisher.php` ابتدا Exchangeها و Queueها ساخته می‌شوند.

```php
$channel->exchange_declare(
    'orther_exchange',
    'direct',
    false,
    true,
    false
);

$channel->exchange_declare(
    'orther_exchange-dead',
    'direct',
    false,
    true,
    false
);
```

سپس Queue اصلی به DLX متصل می‌شود.

```php
new AMQPTable([
    'x-dead-letter-exchange' => 'orther_exchange-dead',
    'x-dead-letter-routing-key' => 'email_dead'
]);
```

یعنی اگر پیامی وارد وضعیت Dead Letter شود، RabbitMQ آن را به Exchange زیر ارسال می‌کند.

```
orther_exchange-dead
```

با Routing Key زیر

```
email_dead
```

---

# Queue اصلی

```
Queue : email
```

دارای تنظیمات زیر است.

| Property | مقدار |
|----------|--------|
| Dead Letter Exchange | orther_exchange-dead |
| Dead Letter Routing Key | email_dead |

---

# Consumer اصلی

در فایل `client.php`

پیام دریافت می‌شود.

```php
echo $msg->body;
```

سپس عمداً Reject می‌شود.

```php
$msg->reject(false);
```

پارامتر `false` یعنی:

```
requeue = false
```

در نتیجه RabbitMQ پیام را دوباره داخل Queue قرار نمی‌دهد و آن را به Dead Letter Exchange منتقل می‌کند.

---

# Dead Consumer

فایل

```
dead_email.php
```

به Queue زیر گوش می‌دهد.

```
email_dead
```

وقتی پیام Reject شود، خروجی خواهد بود.

```
hello world!
```

---

# نحوه اجرا

## مرحله اول

Consumer مربوط به Dead Letter را اجرا کنید.

```bash
php dead_email.php
```

---

## مرحله دوم

Consumer اصلی را اجرا کنید.

```bash
php client.php
```

---

## مرحله سوم

پیام را Publish کنید.

```bash
php publisher.php
```

---

# نتیجه

```
Publisher
      │
      ▼
email Queue
      │
      ▼
client.php
      │
reject(false)
      │
      ▼
Dead Letter Exchange
      │
      ▼
email_dead Queue
      │
      ▼
dead_email.php
```

در نهایت پیام توسط `dead_email.php` دریافت خواهد شد.

---

# تفاوت Ack و Reject

| عملیات | نتیجه |
|---------|--------|
| `ack()` | پیام با موفقیت پردازش شده و از Queue حذف می‌شود. |
| `reject(true)` | پیام دوباره به Queue برمی‌گردد (Requeue). |
| `reject(false)` | پیام به DLX ارسال می‌شود (در صورت تنظیم بودن DLX)، در غیر این صورت حذف می‌شود. |

---

# فایل‌های پروژه

| فایل | توضیح |
|------|-------|
| `publisher.php` | ایجاد Exchangeها، Queueها و ارسال پیام |
| `client.php` | دریافت پیام و Reject کردن آن |
| `dead_email.php` | دریافت پیام‌های Dead Letter |

---

# نکات

- Dead Letter Exchange یک **نوع جدید از Exchange نیست**؛ معمولاً همان Exchangeهای `Direct`، `Topic` یا `Fanout` هستند که RabbitMQ برای پیام‌های Dead Letter از آن‌ها استفاده می‌کند.
- برای فعال شدن DLX باید Queue دارای تنظیمات `x-dead-letter-exchange` باشد.
- در این مثال، انتقال پیام به DLX با استفاده از `reject(false)` انجام می‌شود.
- اگر `reject(true)` استفاده شود، پیام دوباره به Queue اصلی برمی‌گردد و وارد DLX نخواهد شد.