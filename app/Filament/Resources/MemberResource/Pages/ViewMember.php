<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Support\Enums\FontWeight;

class ViewMember extends ViewRecord
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit Member')
                ->icon('heroicon-o-pencil'),
            Actions\DeleteAction::make()
                ->label('Delete Member')
                ->icon('heroicon-o-trash'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Member Information')
                    ->schema([
                        TextEntry::make('member_number')
                            ->label('Member Number')
                            ->weight(FontWeight::Bold)
                            ->copyable(),

                        TextEntry::make('full_name')
                            ->label('Full Name')
                            ->getStateUsing(fn ($record) => $record->first_name . ' ' . $record->last_name)
                            ->weight(FontWeight::Bold),

                        TextEntry::make('id_number')
                            ->label('ID Number')
                            ->copyable(),

                        TextEntry::make('date_of_birth')
                            ->label('Date of Birth')
                            ->date('d F Y'),
                    ])
                    ->columns(2),

                Section::make('Contact Information')
                    ->schema([
                        TextEntry::make('email')
                            ->label('Email Address')
                            ->copyable()
                            ->icon('heroicon-o-envelope'),

                        TextEntry::make('cellphone')
                            ->label('Cellphone Number')
                            ->copyable()
                            ->icon('heroicon-o-phone'),
                    ])
                    ->columns(2),

                Section::make('Status & Dates')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'inactive' => 'warning',
                                'suspended' => 'danger',
                                default => 'gray',
                            }),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('d F Y, H:i'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('d F Y, H:i'),
                    ])
                    ->columns(3),
            ]);
    }
}
