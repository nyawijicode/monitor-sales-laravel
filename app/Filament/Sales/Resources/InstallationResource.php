<?php

namespace App\Filament\Sales\Resources;

use App\Filament\Sales\Resources\InstallationResource\Pages;
use App\Models\Installation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InstallationResource extends Resource
{
    protected static ?string $model = Installation::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Layanan Purna Jual';

    protected static ?string $navigationLabel = 'Instalasi';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Jadwal Instalasi')
                    ->schema([
                        Forms\Components\Select::make('delivery_order_id')
                            ->label('Referensi Pengiriman (DO)')
                            ->relationship('deliveryOrder', 'do_number', fn(Builder $query) => $query->whereIn('status', ['shipped', 'delivered']))
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->do_number} - " . ($record->salesOrder->boq->visit->customer->nama_instansi ?? 'Unknown'))
                            ->searchable(['do_number', 'salesOrder.boq.visit.customer.nama_instansi'])
                            ->preload()
                            ->required()
                            ->helperText('Hanya barang yang sudah dikirim/diterima yang bisa dijadwalkan instalasi'),

                        Forms\Components\DateTimePicker::make('schedule_date')
                            ->label('Jadwal Mulai')
                            ->required(),

                        Forms\Components\TextInput::make('technician_name')
                            ->label('Nama Teknisi / Penanggung Jawab')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending (Menunggu Jadwal)',
                                'scheduled' => 'Terjadwal',
                                'in_progress' => 'Sedang Dikerjakan',
                                'finished' => 'Selesai',
                            ])
                            ->default('pending')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Penyelesaian')
                    ->description('Diisi setelah instalasi selesai')
                    ->schema([
                        Forms\Components\DateTimePicker::make('finish_date')
                            ->label('Waktu Selesai'),

                        Forms\Components\FileUpload::make('proof_file')
                            ->label('Upload BAST / Bukti Pasang')
                            ->directory('installations/proofs')
                            ->openable()
                            ->downloadable(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Pemasangan')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('deliveryOrder.do_number')
                    ->label('Ref DO')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('technician_name')
                    ->label('Teknisi')
                    ->searchable(),

                Tables\Columns\TextColumn::make('schedule_date')
                    ->label('Jadwal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'gray',
                        'scheduled' => 'info',
                        'in_progress' => 'warning',
                        'finished' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListInstallations::route('/'),
            'create' => Pages\CreateInstallation::route('/create'),
            'edit' => Pages\EditInstallation::route('/{record}/edit'),
        ];
    }
}
