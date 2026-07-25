# RabbitMQ Topic Exchange (PHP)

این مثال نحوه استفاده از **Topic Exchange** در RabbitMQ با استفاده از کتابخانه `php-amqplib` را نشان می‌دهد.

## ساختار پروژه

```
exchange_topic/
│
├── producer.php
├── consumer.php
└── README.md
```

---

# Topic Exchange چیست؟

در **Topic Exchange** پیام‌ها بر اساس **Routing Key** و **Pattern** به Queueها ارسال می‌شوند.

برخلاف `Direct Exchange` که Routing Key باید دقیقاً برابر باشد، در Topic می‌توان از Wildcard استفاده کرد.

## Wildcardها

| Pattern | توضیح |
|----------|--------|
| `*` | دقیقاً یک کلمه |
| `#` | صفر یا بیشتر از صفر کلمه |

---

# Publisher

در فایل `producer.php` یک پیام با Routing Key زیر ارسال می‌شود:

```php
$channel->basic_publish(
    new AMQPMessage('HELLO'),
    'exchange_topic',
    'user.select'
);
```

در این مثال:

- Exchange : `exchange_topic`
- Routing Key : `user.select`

---

# Consumer

Consumer یک Queue به نام `user_event` ایجاد می‌کند.

```php
$channel->queue_declare(
    'user_event',
    false,
    true,
    false,
    false
);
```

سپس Queue را با Pattern زیر به Exchange متصل می‌کند:

```php
$channel->queue_bind(
    'user_event',
    'exchange_topic',
    'user.*'
);
```

---

# نحوه عملکرد

```
                 Routing Key

Producer ----------------------> user.select
                  │
                  ▼
          Topic Exchange
                  │
                  ▼
        Pattern : user.*
                  │
                  ▼
             user_event
                  │
                  ▼
              Consumer
```

از آنجایی که Routing Key برابر است با:

```
user.select
```

و Pattern نیز:

```
user.*
```

پیام به Queue ارسال خواهد شد.

---

# مثال‌های Pattern

| Routing Key | Pattern | Match |
|-------------|----------|-------|
| user.select | user.* | ✅ |
| user.insert | user.* | ✅ |
| user.delete | user.* | ✅ |
| order.select | user.* | ❌ |
| user.profile.update | user.* | ❌ |

---

## استفاده از #

Pattern زیر:

```
user.#
```

پیام‌های زیر را دریافت می‌کند:

```
user.select
user.insert
user.profile
user.profile.update
user.role.admin
```

زیرا `#` یعنی صفر یا بیشتر از صفر کلمه.

---

# اجرای مثال

ابتدا Consumer را اجرا کنید:

```bash
php consumer.php
```

سپس Publisher را اجرا کنید:

```bash
php producer.php
```

خروجی:

```
HELLO
```

---

# تفاوت Direct و Topic

## Direct

```
Routing Key = user.select

Binding = user.select
```

فقط در صورت برابر بودن کامل پیام ارسال می‌شود.

---

## Topic

```
Routing Key = user.select

Binding = user.*
```

به دلیل استفاده از Pattern پیام ارسال می‌شود.

---

# فایل‌های پروژه

| فایل | توضیح |
|------|-------|
| producer.php | ارسال پیام به Topic Exchange |
| consumer.php | دریافت پیام با استفاده از Pattern |

---

# نکات

- `*` فقط یک بخش از Routing Key را Match می‌کند.
- `#` صفر یا بیشتر از صفر بخش را Match می‌کند.
- اگر Queue به Exchange Bind نشده باشد، پیام دریافت نخواهد شد.
- در Topic Exchange، Routing Key اهمیت دارد و بر اساس Pattern بررسی می‌شود.