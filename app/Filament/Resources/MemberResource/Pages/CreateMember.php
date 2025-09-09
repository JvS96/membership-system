<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use App\Models\Member;
use Filament\Resources\Pages\CreateRecord;

class CreateMember extends CreateRecord
{
    protected static string $resource = MemberResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate member number if not set
        if (empty($data['member_number'])) {
            $data['member_number'] = Member::generateMemberNumber();
        }

        // Extract date of birth from ID number and validate
        if (!empty($data['id_number'])) {
            // Additional server-side validation
            if (!Member::isValidSouthAfricanId($data['id_number'])) {
                throw new \Exception('Invalid South African ID number provided.');
            }

            $dateOfBirth = Member::extractDateOfBirthFromId($data['id_number']);
            if ($dateOfBirth) {
                $data['date_of_birth'] = $dateOfBirth->format('Y-m-d');
            } else {
                throw new \Exception('Could not extract valid date of birth from ID number.');
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Member created successfully';
    }
}
