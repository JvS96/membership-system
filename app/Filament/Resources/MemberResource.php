<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\Pages;
use App\Models\Member;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Members';

    protected static ?string $pluralModelLabel = 'Members';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        TextInput::make('member_number')
                            ->label('Member Number')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(fn () => Member::generateMemberNumber()),

                        TextInput::make('id_number')
                            ->label('ID Number')
                            ->required()
                            ->maxLength(13)
                            ->minLength(13)
                            ->unique(ignoreRecord: true)
                            ->live(debounce: 1000)
                            ->afterStateUpdated(function ($state, $set) {
                                if (!empty($state) && strlen($state) === 13) {
                                    // Validate and extract date of birth
                                    if (Member::isValidSouthAfricanId($state)) {
                                        $dateOfBirth = Member::extractDateOfBirthFromId($state);
                                        if ($dateOfBirth) {
                                            $set('date_of_birth', $dateOfBirth->format('Y-m-d'));
                                        }
                                    }
                                }
                            })
                            ->rule('required')
                            ->rule('string')
                            ->rule('size:13')
                            ->rule('regex:/^[0-9]{13}$/')
                            ->helperText('Enter a valid 13-digit South African ID number'),

                        TextInput::make('first_name')
                            ->label('First Name')
                            ->required()
                            ->maxLength(255)
                            ->rules([
                                'required',
                                'string',
                                'max:255',
                                'regex:/^[a-zA-Z\s\'-]+$/',
                            ])
                            ->helperText('Only letters, spaces, hyphens and apostrophes allowed'),

                        TextInput::make('last_name')
                            ->label('Last Name')
                            ->required()
                            ->maxLength(255)
                            ->rules([
                                'required',
                                'string',
                                'max:255',
                                'regex:/^[a-zA-Z\s\'-]+$/',
                            ])
                            ->helperText('Only letters, spaces, hyphens and apostrophes allowed'),

                        DatePicker::make('date_of_birth')
                            ->label('Date of Birth')
                            ->required()
                            ->disabled()
                            ->dehydrated(true)
                            ->helperText('Automatically extracted from ID number - cannot be edited'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->rules([
                                'required',
                                'string',
                                'email:rfc,dns',
                                'max:255',
                            ])
                            ->helperText('Must be a valid email address format'),

                        TextInput::make('cellphone')
                            ->label('Cellphone Number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->tel()
                            ->rules([
                                'required',
                                'string',
                                'regex:/^(\+27|0)[6-8][0-9]{8}$/',
                            ])
                            ->helperText('Format: +27123456789 or 0123456789 (South African numbers only)'),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'suspended' => 'Suspended',
                            ])
                            ->required()
                            ->default('active')
                            ->rules([
                                'required',
                                'string',
                                'in:active,inactive,suspended',
                            ]),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('member_number')
                    ->label('Member Number')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('full_name')
                    ->label('Name')
                    ->getStateUsing(fn ($record) => $record->first_name . ' ' . $record->last_name)
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name', 'last_name']),

                TextColumn::make('id_number')
                    ->label('ID Number')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('cellphone')
                    ->label('Cellphone')
                    ->searchable()
                    ->copyable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'inactive',
                        'danger' => 'suspended',
                    ]),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('search_id_member')
                    ->form([
                        TextInput::make('search')
                            ->label('Search by ID Number or Member Number')
                            ->placeholder('Enter ID Number or Member Number'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['search'],
                            fn (Builder $query, $search): Builder => $query->searchByIdOrMember($search),
                        );
                    }),

                Filter::make('cellphone_filter')
                    ->form([
                        TextInput::make('cellphone')
                            ->label('Filter by Cellphone (contains)')
                            ->placeholder('Enter cellphone number or part of it'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['cellphone'],
                            fn (Builder $query, $cellphone): Builder => $query->filterByCellphone($cellphone),
                        );
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'view' => Pages\ViewMember::route('/{record}'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
        ];
    }
}
