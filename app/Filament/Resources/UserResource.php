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
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\ImageColumn;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Information')
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
                        Forms\Components\Select::make('atasan_id')
                            ->label('Atasan Langsung')
                            ->relationship('atasan', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                Section::make('Organization & Access')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->label('Role')
                            ->options(\Spatie\Permission\Models\Role::pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record) {
                                    $component->state($record->roles->first()?->id);
                                }
                            })
                            ->saveRelationshipsUsing(function ($record, $state) {
                                $role = \Spatie\Permission\Models\Role::find($state);
                                if ($role) {
                                    $record->syncRoles([$role]);
                                } else {
                                    $record->syncRoles([]);
                                }
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
                        Forms\Components\Select::make('position_id')
                            ->label('Position')
                            ->options(\App\Models\Position::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->afterStateHydrated(fn($component, $record) => $component->state($record?->userInfo?->position_id))
                            ->saveRelationshipsUsing(fn($record, $state) => $record->userInfo()->updateOrCreate(['user_id' => $record->id], ['position_id' => $state])),
                        Forms\Components\FileUpload::make('signature_attachment')
                            ->label('Signature / Tanda Tangan')
                            ->directory('signatures')
                            ->image()
                            ->imageEditor()
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($component, $record) {
                                // Reload relation to ensure fresh data
                                $record->load('userInfo');
                                $signature = $record->userInfo?->signature;

                                if (!$signature) {
                                    $component->state([]);
                                    return;
                                }

                                // Handle if saved as JSON array/object string accidentally
                                if (str_starts_with($signature, '[') || str_starts_with($signature, '{')) {
                                    $decoded = json_decode($signature, true);
                                    if (is_array($decoded)) {
                                        // Get the first value regardless of key (UUID)
                                        $path = array_values($decoded)[0] ?? null;
                                    } else {
                                        $path = $signature;
                                    }
                                } else {
                                    $path = $signature;
                                }

                                // FileUpload state must be an array
                                $component->state([$path]);
                            })
                            ->saveRelationshipsUsing(function ($record, $state) {
                                // Ensure we save a string, not an array
                                if (is_array($state)) {
                                    $path = array_values($state)[0] ?? null;
                                } else {
                                    $path = $state;
                                }

                                $record->userInfo()->updateOrCreate(
                                    ['user_id' => $record->id],
                                    ['signature' => $path]
                                );
                            }),
                    ])->columns(2),

                Section::make('Regional Access')
                    ->schema([
                        Forms\Components\Select::make('branches')
                            ->label('Branches')
                            ->relationship('branches', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->saveRelationshipsUsing(function ($record, $state) {
                                $record->branches()->sync($state);
                                // Update UserInfo with the first branch (Primary Branch)
                                $firstBranchId = !empty($state) ? $state[0] : null;
                                $record->userInfo()->updateOrCreate(
                                    ['user_id' => $record->id],
                                    ['branch_id' => $firstBranchId]
                                );
                            }),
                        Forms\Components\Select::make('provinces')
                            ->label('Provinces')
                            ->relationship('provinces', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn(Set $set) => $set('cities', [])),
                        Forms\Components\Select::make('cities')
                            ->label('Cities / Areas')
                            ->relationship('cities', 'name', modifyQueryUsing: fn(Builder $query, Get $get) => $query->whereIn('province_id', $get('provinces') ?? []))
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ])->columns(1),
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
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->label('Role')
                    ->searchable(),
                Tables\Columns\TextColumn::make('userInfo.company.name')
                    ->label('Company')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('userInfo.division.name')
                    ->label('Division')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('userInfo.position.name')
                    ->label('Position')
                    ->searchable(),
                Tables\Columns\TextColumn::make('branches.name')
                    ->label('Branches')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ImageColumn::make('userInfo.signature')
                    ->label('Signature')
                    ->disk('public')
                    ->size(40)
                    ->circular(),
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
