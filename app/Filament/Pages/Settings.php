<?php

namespace App\Filament\Pages;

use App\Models\GeneralSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class Settings extends Page
{
    public ?array $data = [];

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $title = 'General Settings';

    protected string $view = 'filament.pages.settings';

    public function mount(): void
    {
        $this->form->fill(GeneralSetting::query()->first()?->toArray() ?? []);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Site Information')
                    ->schema([
                        TextInput::make('site_name')
                            ->label('Name')
                            ->maxLength(255),
                        TextInput::make('site_email')
                            ->label('Email')
                            ->prefixIcon(Heroicon::Envelope)
                            ->email()
                            ->maxLength(255),
                        TextInput::make('site_phone')
                            ->label('Phone')
                            ->prefixIcon(Heroicon::Phone)
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('site_address')
                            ->prefixIcon(Heroicon::MapPin)
                            ->label('Address'),
                        Textarea::make('site_description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('site_facebook_url')
                            ->prefixIcon('fab-facebook')
                            ->label('Facebook URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('site_twitter_url')
                            ->prefixIcon('fab-twitter')
                            ->label('Twitter URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('site_instagram_url')
                            ->prefixIcon('fab-instagram')
                            ->label('Instagram URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('site_linkedin_url')
                            ->prefixIcon('fab-linkedin')
                            ->label('LinkedIn URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('site_youtube_url')
                            ->prefixIcon('fab-youtube')
                            ->label('YouTube URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('site_telegram_url')
                            ->prefixIcon('fab-telegram')
                            ->label('Telegram URL')
                            ->url()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make('Branding Logo')
                    ->schema([
                        FileUpload::make('site_logo')
                            ->label('Logo')
                            ->image()
                            ->disk('public')
                            ->directory('settings'),
                        FileUpload::make('site_favicon')
                            ->label('Favicon')
                            ->image()
                            ->disk('public')
                            ->directory('settings'),
                    ])
                    ->columns(2),
            ]);
    }

    public function save(): void
    {
        GeneralSetting::query()->updateOrCreate(['id' => 1], $this->form->getState());

        Notification::make()
            ->success()
            ->title('Settings saved')
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
