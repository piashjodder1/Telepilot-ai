<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->createAnother(false)
                ->mutateFormDataUsing(function (array $data): array {
                    if (($data['format'] ?? '') === 'poll') {
                        $question = $data['poll_question'] ?? '';
                        $options = $data['poll_options'] ?? [];
                        $data['content'] = $question . "\n" . implode("\n", $options);
                    }
                    unset($data['poll_question'], $data['poll_options']);
                    return $data;
                })
                ->after(function (\App\Models\Draft $record) {
                    if ($record->status === 'published') {
                        try {
                            $publisher = app(\App\Services\Telegram\TelegramPublisher::class);
                            $messageId = $publisher->publish($record, $record->channel);

                            \App\Models\PublishedPost::create([
                                'channel_id' => $record->channel_id,
                                'draft_id' => $record->id,
                                'telegram_message_id' => $messageId,
                                'content_preview' => mb_substr($record->content, 0, 100),
                                'published_at' => now(),
                            ]);

                            \Filament\Notifications\Notification::make()
                                ->title('Published successfully!')
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            $record->update([
                                'status' => 'failed',
                                'fail_reason' => $e->getMessage(),
                            ]);

                            \Filament\Notifications\Notification::make()
                                ->title('Publishing failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Posts'),
            'drafts' => Tab::make('Drafts')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft')),
            'scheduled' => Tab::make('Scheduled')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'scheduled')),
            'published' => Tab::make('Published')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'published')),
            'failed' => Tab::make('Failed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'failed')),
        ];
    }
}
