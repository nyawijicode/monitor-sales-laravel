<?php

namespace App\Filament\Sales\Resources;

use App\Filament\Sales\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Daftar Customer';

    protected static ?string $modelLabel = 'Customer';

    protected static ?string $pluralModelLabel = 'Daftar Customer';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Customer')
                    ->schema([
                        Forms\Components\TextInput::make('kode_customer')
                            ->label('Kode Customer')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generate: C.000001')
                            ->helperText('Kode akan dibuat otomatis saat menyimpan'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Nonaktifkan jika customer sudah tidak aktif'),
                        Forms\Components\TextInput::make('nama_instansi')
                            ->label('Nama Instansi/Perusahaan')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->validationMessages([
                                'unique' => 'Nama instansi/perusahaan ini sudah terdaftar. Untuk cabang, tambahkan nama cabang di akhir (misal: PT ABC Cabang Jakarta)',
                            ])
                            ->maxLength(255),
                        Forms\Components\Select::make('city_id')
                            ->label('Wilayah')
                            ->relationship('city', 'name')
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->province->name . ' - ' . $record->name)
                            ->nullable()
                            ->helperText('Pilih kota/kabupaten wilayah customer'),
                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informasi Kontak')
                    ->description('Data kontak person yang ditemui di instansi/perusahaan')
                    ->schema([
                        Forms\Components\TextInput::make('nama_kontak')
                            ->label('Nama')
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\TextInput::make('telepon')
                            ->label('No Telepon/HP')
                            ->tel()
                            ->maxLength(20)
                            ->nullable(),
                        Forms\Components\TextInput::make('jabatan')
                            ->label('Jabatan')
                            ->maxLength(255)
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_customer')
                    ->label('Kode')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Kode customer berhasil disalin')
                    ->weight('bold'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('nama_instansi')
                    ->label('Nama Instansi/Perusahaan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('alamat')
                    ->label('Alamat')
                    ->searchable()
                    ->wrap()
                    ->limit(50),
                Tables\Columns\TextColumn::make('city.name')
                    ->label('Wilayah')
                    ->searchable()
                    ->formatStateUsing(fn($record) => $record->city ? ($record->city->province->name . ' - ' . $record->city->name) : '-'),
                Tables\Columns\TextColumn::make('nama_kontak')
                    ->label('Nama Kontak')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telepon')
                    ->label('Telepon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        true => 'Aktif',
                        false => 'Tidak Aktif',
                    ])
                    ->default(true),
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
            ->defaultSort('kode_customer', 'desc');
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
