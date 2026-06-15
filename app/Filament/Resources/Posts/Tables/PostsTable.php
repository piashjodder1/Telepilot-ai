<?php

namespace App\Filament\Resources\Posts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('content')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('rule.name')
                    ->label('Rule')
                    ->sortable(),
                TextColumn::make('channel.title')
                    ->label('Channel/Group')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'warning',
                        'scheduled' => 'info',
                        'published' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),

                TextColumn::make('scheduledPost.scheduled_at')
                    ->label('Scheduled For')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                \Filament\Actions\ActionGroup::make([
                    \Filament\Actions\Action::make('schedule')
                        ->label('Schedule Post')
                        ->icon('heroicon-o-clock')
                        ->color('primary')
                        ->form([
                            \Filament\Forms\Components\DateTimePicker::make('scheduled_at')->displayFormat('d M Y, h:i A')
                                ->label('Schedule Time (Asia/Dhaka)')
                                ->native(false)
                                ->displayFormat('Y-m-d h:i A')
                                ->timezone('Asia/Dhaka')
                                ->required(),
                        ])
                        ->action(function (\App\Models\Draft $record, array $data): void {
                            \App\Models\ScheduledPost::create([
                                'draft_id'     => $record->id,
                                'channel_id'   => $record->channel_id,
                                'rule_id'      => $record->rule_id,
                                'scheduled_at' => $data['scheduled_at'],
                                'status'       => 'scheduled',
                                'attempts'     => 0,
                            ]);

                            $record->update(['status' => 'scheduled']);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Post Scheduled Successfully!')
                                ->success()
                                ->send();
                        })
                        ->visible(fn ($record) => $record && in_array($record->status, ['draft', 'failed'])),
                    \Filament\Actions\Action::make('resend')
                        ->label('Publish Now')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->action(function (\App\Models\Draft $record): void {
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

                                $record->update(['status' => 'published']);

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
                        })
                        ->requiresConfirmation()
                        ->visible(fn ($record) => $record && in_array($record->status, ['draft', 'failed', 'scheduled'])),
                    \Filament\Actions\EditAction::make()
                        ->mutateRecordDataUsing(function (array $data): array {
                            if (($data['format'] ?? '') === 'poll' && !empty($data['content'])) {
                                $lines = explode("\n", $data['content']);
                                $data['poll_question'] = trim($lines[0] ?? '');
                                $data['poll_options'] = array_filter(array_map('trim', array_slice($lines, 1)));
                            }
                            return $data;
                        })
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
                            if ($record->status === 'published' && $record->wasChanged('status')) {
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
                    \Filament\Actions\DeleteAction::make(),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->tooltip('Actions')
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
