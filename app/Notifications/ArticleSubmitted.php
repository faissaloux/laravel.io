<?php

namespace App\Notifications;

use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class ArticleSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Article $article) {}

    public function via($notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable)
    {
        if (is_null(config('services.telegram-bot-api.channel'))) {
            return;
        }

        $url = route('articles.show', $this->article->slug());

        return TelegramMessage::create()
            ->to(config('services.telegram-bot-api.channel'))
            ->content($this->content())
            ->button('View Article', $url);
    }

    private function content(): string
    {
        if ($this->article->author()->isVerifiedAuthor()) {
            $content = "*New Article Published by verified author!*\n\n";
            $content .= 'Title: '.$this->article->title()."\n";
            $content .= 'By: '.$this->article->author()->username();

            return $content;
        }

        $content = "*New Article Submitted!*\n\n";
        $content .= 'Title: '.$this->article->title()."\n";
        $content .= 'By: '.$this->article->author()->username();

        return $content;
    }
}
