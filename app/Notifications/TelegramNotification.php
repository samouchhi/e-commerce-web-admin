<?php

namespace App\Notifications;

use App\Models\GeneralSetting;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class TelegramNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['telegram'];
    }

    /**
     * Get the mail representation of the notification.
     */
    // public function toMail(object $notifiable): MailMessage
    // {
    //     return (new MailMessage)
    //         ->line('The introduction to the notification.')
    //         ->action('Notification Action', url('/'))
    //         ->line('Thank you for using our application!');
    // }

    public function toTelegram($notifiable): TelegramMessage
{
    $telegram = app(TelegramService::class);
    $url = url("dashboard/orders/{$notifiable->id}");

    $items = $notifiable->items
        ->map(function ($item) {
            $product = $item->productVariant->product->name;
            $variant = $item->productVariant->name;

            return "• {$product} — {$variant}\n" .
                "   Qty: {$item->quantity} × {$item->price}$";
        })
        ->implode("\n");

    return TelegramMessage::create()
        ->token($telegram->token())
        ->to($telegram->chatId())
        ->content(
            "🛒 *ទទួលបានការបញ្ជាទិញថ្មី!*\n\n" .
            "📦 *លេខបញ្ជាទិញ:* `{$notifiable->order_number}`\n" .
            "👤 *ឈ្មោះអតិថិជន:* {$notifiable->customer->name}\n" .
            "📞 *ទូរស័ព្ទ:* {$notifiable->customer->phone}\n" .
            "💰 *ប្រាក់សរុប:* {$notifiable->total_amount}$\n\n" .
            "━━━━━━━━━━━━━━\n\n" .
            "🛍️ *ទំនិញដែលបានបញ្ជាទិញ:*\n\n" .
            $items .
            "\n\n━━━━━━━━━━━━━━"
        )
        ->button('📄 View Invoice', $url);
}

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
