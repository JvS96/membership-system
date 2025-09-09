<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use App\Models\Member;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMember extends EditRecord
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('View Member')
                ->icon('heroicon-o-eye'),
            Actions\DeleteAction::make()
                ->label('Delete Member')
                ->icon('heroicon-o-trash'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Extract date of birth from ID number if ID was changed
        if (!empty($data['id_number']) && $data['id_number'] !== $this->record->id_number) {
            $dateOfBirth = Member::extractDateOfBirthFromId($data['id_number']);
            if ($dateOfBirth) {
                $data['date_of_birth'] = $dateOfBirth->format('Y-m-d');
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Member updated successfully';
    }
}
