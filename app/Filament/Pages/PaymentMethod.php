<?php

namespace App\Filament\Pages;

use App\Models\PaymentMethod as PaymentMethodModel;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

class PaymentMethod extends Page
{
    public ?array $data = [];

    protected string $view = 'filament.pages.payment-method';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    public function mount(): void
    {
        $this->form->fill(PaymentMethodModel::query()->first()?->toArray() ?? []);
    }

    public function form(Schema $schema): Schema
    {

        return $schema
            ->statePath('data')
            ->components([
                Section::make('Payment Method')
                    ->schema([
                        TextInput::make('aba_payway_link')
                            ->label('ABA Payway Link')
                            ->prefixIcon('heroicon-o-link')
                            ->maxLength(255),
                        TextInput::make('bot_token')
                            ->prefixIcon('heroicon-o-key')
                            ->label('Bot Token')
                            ->maxLength(255),
                        TextInput::make('bot_chat_id')
                            ->prefixIcon('heroicon-o-chat-bubble-left-right')
                            ->label('Bot Chat ID')
                            ->maxLength(255),
                    ]),
            ]);
    }

    public function save(): void
    {
        PaymentMethodModel::query()->updateOrCreate(['id' => 1], $this->form->getState());

        Notification::make()
            ->success()
            ->title('Saved')
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save changes')
                ->submit('save'),
        ];
    }
}
