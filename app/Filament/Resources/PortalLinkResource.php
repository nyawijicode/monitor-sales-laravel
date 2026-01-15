<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PortalLinkResource\Pages;
use App\Models\PortalLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PortalLinkResource extends Resource
{
    protected static ?string $model = PortalLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationGroup = 'Portal Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                Forms\Components\TextInput::make('slug')
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->unique(PortalLink::class, 'slug', ignoreRecord: true),
                Forms\Components\TextInput::make('url')
                    ->required()
                    ->label('URL')
                    ->helperText('Start with http:// for external links or / for internal paths (e.g. /teknisi)'),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('icon')
                    ->label('Icon Name (Heroicons)')
                    ->helperText('e.g. heroicon-o-home')
                    ->default('heroicon-o-link'),
                // Alternatively use FileUpload if user wants images
                // ->image(),
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\TextInput::make('badge_text'),
                        Forms\Components\Select::make('badge_color')
                            ->options([
                                'primary' => 'Primary',
                                'secondary' => 'Secondary',
                                'success' => 'Success',
                                'danger' => 'Danger',
                                'warning' => 'Warning',
                                'info' => 'Info',
                                'gray' => 'Gray',
                            ])
                            ->default('primary'),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ]),
                Forms\Components\Toggle::make('is_active')
                    ->required()
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('url')
                    ->searchable(),
                Tables\Columns\TextColumn::make('badge_text')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'primary' => 'primary', // This logic might need adjustment if storing color in badge_color column
                        default => 'gray',
                    }),
                // Fix badge color usage using the record
                Tables\Columns\TextColumn::make('badge_text_display')
                    ->label('Badge')
                    ->state(fn(PortalLink $record) => $record->badge_text)
                    ->badge()
                    ->color(fn(PortalLink $record) => $record->badge_color),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()

                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
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
            'index' => Pages\ListPortalLinks::route('/'),
            'create' => Pages\CreatePortalLink::route('/create'),
            'edit' => Pages\EditPortalLink::route('/{record}/edit'),
        ];
    }
}
