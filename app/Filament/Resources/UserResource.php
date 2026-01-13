<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('username')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context === 'create'),
                Forms\Components\Select::make('roles')
                    ->label('Role')
                    ->options(\Spatie\Permission\Models\Role::pluck('name', 'id')) // Value is ID to be consistent
                    ->required()
                    ->searchable()
                    ->preload()
                    ->afterStateHydrated(function ($component, $record) {
                        // Load the first role ID. If multiple roles exist, this takes the first one.
                        // Ideally, users should strictly have one role if this UI is used.
                        if ($record) {
                            $component->state($record->roles->first()?->id);
                        }
                    })
                    ->saveRelationshipsUsing(function ($record, $state) {
                        // Sync roles using the ID (state)
                        // Assuming $state is a single ID because Select is not ->multiple()
                        $role = \Spatie\Permission\Models\Role::find($state);

                        if ($role) {
                            $record->syncRoles([$role]);
                        } else {
                            // Handle case where role might be null (though required)
                            $record->syncRoles([]);
                        }

                        // Also update user_info
                        $record->userInfo()->updateOrCreate(
                            ['user_id' => $record->id],
                            ['role_id' => $state]
                        );
                    }),
                Forms\Components\Select::make('company_id')
                    ->label('Company')
                    ->options(\App\Models\Company::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->afterStateHydrated(fn($component, $record) => $component->state($record?->userInfo?->company_id))
                    ->saveRelationshipsUsing(fn($record, $state) => $record->userInfo()->updateOrCreate(['user_id' => $record->id], ['company_id' => $state])),
                Forms\Components\Select::make('division_id')
                    ->label('Division')
                    ->options(\App\Models\Division::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->afterStateHydrated(fn($component, $record) => $component->state($record?->userInfo?->division_id))
                    ->saveRelationshipsUsing(fn($record, $state) => $record->userInfo()->updateOrCreate(['user_id' => $record->id], ['division_id' => $state])),
                Forms\Components\Select::make('branch_id')
                    ->label('Branch')
                    ->options(\App\Models\Branch::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->afterStateHydrated(fn($component, $record) => $component->state($record?->userInfo?->branch_id))
                    ->saveRelationshipsUsing(fn($record, $state) => $record->userInfo()->updateOrCreate(['user_id' => $record->id], ['branch_id' => $state])),
                Forms\Components\Select::make('position_id')
                    ->label('Position')
                    ->options(\App\Models\Position::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->afterStateHydrated(fn($component, $record) => $component->state($record?->userInfo?->position_id))
                    ->saveRelationshipsUsing(fn($record, $state) => $record->userInfo()->updateOrCreate(['user_id' => $record->id], ['position_id' => $state])),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
