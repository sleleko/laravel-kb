# Паттерн "Фабричный метод" (Factory Method) в Laravel

📌 **Фабричный метод** — это паттерн проектирования, который позволяет делегировать создание объектов подклассам, предоставляя общий интерфейс для их создания.

## 🔹 Когда использовать?

✅ Когда нужно скрыть сложную логику создания объектов.  
✅ Когда требуется создавать разные объекты, но с единым интерфейсом.  
✅ Когда необходимо расширять систему, не изменяя существующий код.

---

## 🔹 Реальный пример в Laravel

Допустим, у нас есть система уведомлений, которая может отправлять сообщения через **SMS**, **Email** или **Telegram**.

Вместо того, чтобы захламлять код `if-else` конструкциями, используем **Фабричный метод**.

### 1. Интерфейс уведомлений
Создадим единый интерфейс для всех способов уведомлений:

```php
namespace App\Services\Notifications;

interface Notifier
{
    public function send(string $recipient, string $message): void;
}
```

### 2. Реализация различных способов уведомлений

#### 📲 SMS-уведомления
```php
namespace App\Services\Notifications;

use Illuminate\Support\Facades\Http;

class SmsNotifier implements Notifier
{
    public function send(string $recipient, string $message): void
    {
        Http::post('https://sms-gateway.example.com/send', [
            'phone' => $recipient,
            'message' => $message
        ]);
    }
}
```

#### 📧 Email-уведомления
```php
namespace App\Services\Notifications;

use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationMail;

class EmailNotifier implements Notifier
{
    public function send(string $recipient, string $message): void
    {
        Mail::to($recipient)->send(new NotificationMail($message));
    }
}
```

#### 💬 Telegram-уведомления
```php
namespace App\Services\Notifications;

use Illuminate\Support\Facades\Http;

class TelegramNotifier implements Notifier
{
    public function send(string $recipient, string $message): void
    {
        Http::post("https://api.telegram.org/bot".config('services.telegram.bot_token')."/sendMessage", [
            'chat_id' => $recipient,
            'text' => $message
        ]);
    }
}
```

### 3. Фабричный метод для создания объектов-уведомителей

```php
namespace App\Services\Notifications;

abstract class NotifierFactory
{
    abstract public function createNotifier(): Notifier;
}

class SmsNotifierFactory extends NotifierFactory
{
    public function createNotifier(): Notifier
    {
        return new SmsNotifier();
    }
}

class EmailNotifierFactory extends NotifierFactory
{
    public function createNotifier(): Notifier
    {
        return new EmailNotifier();
    }
}

class TelegramNotifierFactory extends NotifierFactory
{
    public function createNotifier(): Notifier
    {
        return new TelegramNotifier();
    }
}
```

### 4. Сервис для отправки уведомлений через фабрику
Теперь можно легко подменять способы уведомлений через конфиг.

```php
namespace App\Services;

use App\Services\Notifications\SmsNotifierFactory;
use App\Services\Notifications\EmailNotifierFactory;
use App\Services\Notifications\TelegramNotifierFactory;
use App\Services\Notifications\Notifier;

class NotificationService
{
    protected Notifier $notifier;

    public function __construct()
    {
        $factory = match (config('notifications.default')) {
            'sms' => new SmsNotifierFactory(),
            'email' => new EmailNotifierFactory(),
            'telegram' => new TelegramNotifierFactory(),
            default => new EmailNotifierFactory(),
        };

        $this->notifier = $factory->createNotifier();
    }

    public function sendNotification(string $recipient, string $message): void
    {
        $this->notifier->send($recipient, $message);
    }
}
```

### 5. Использование в контроллере
Теперь можно легко отправлять уведомления, не привязываясь к конкретной реализации.

```php
namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function send(Request $request)
    {
        $this->notificationService->sendNotification($request->recipient, $request->message);
        return response()->json(['message' => 'Уведомление отправлено!']);
    }
}
```

---

## 🔹 Плюсы фабричного метода в Laravel

✅ **Гибкость** — можно легко добавлять новые способы уведомлений.  
✅ **Разделение логики** — фабрика инкапсулирует создание объектов.  
✅ **Легкость тестирования** — можно подменять реализации в тестах.

---
#laravel #php #patterns #design_patterns

