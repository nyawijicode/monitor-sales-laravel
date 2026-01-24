<?php

namespace App\Filament\Sales\Resources;

use App\Filament\Sales\Resources\DeliveryOrderResource\Pages;
use App\Models\DeliveryOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DeliveryOrderResource extends Resource
{
    protected static ?string $model = DeliveryOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Logistik';

    protected static ?string $navigationLabel = 'Pengiriman (DO)';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengiriman')
                    ->schema([
                        Forms\Components\Select::make('sales_order_id')
                            ->label('Referensi Sales Order (SO)')
                            ->relationship('salesOrder', 'so_number', fn(Builder $query) => $query->whereIn('status', ['in_accounting', 'paid_dp', 'completed']))
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->so_number} - " . ($record->boq->visit->customer->nama_instansi ?? 'Unknown'))
                            ->searchable(['so_number', 'boq.visit.customer.nama_instansi'])
                            ->preload()
                            ->required()
                            ->helperText('Hanya SO dengan status Accounting Process atau DP Paid yang muncul'),

                        Forms\Components\TextInput::make('do_number')
                            ->label('Nomor DO')
                            ->placeholder('DO/XXXX/XX/XX')
                            ->unique(ignoreRecord: true),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending (Menunggu Barang)',
                                'processed' => 'Diproses (Sedang Disiapkan)',
                                'shipped' => 'Dikirim (Dalam Perjalanan)',
                                'delivered' => 'Terkirim (Sudah Diterima)',
                            ])
                            ->default('pending')
                            ->required(),

                        Forms\Components\Select::make('shipping_type')
                            ->label('Tipe Pengiriman')
                            ->options([
                                'internal' => 'Internal (Kurir Sendiri)',
                                'external' => 'Eksternal (Ekspedisi)',
                                'pickup' => 'Pickup (Ambil Sendiri)',
                            ])
                            ->required()
                            ->reactive(),

                        Forms\Components\DatePicker::make('schedule_date')
                            ->label('Jadwal Pengiriman')
                            ->required(),

                        Forms\Components\TextInput::make('courier_name')
                            ->label('Nama Kurir / Ekspedisi')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('receipt_number')
                            ->label('Nomor Resi')
                            ->visible(fn(Forms\Get $get) => $get('shipping_type') === 'external'),
                    ])->columns(2),

                Forms\Components\Section::make('Dokumen Pendukung')
                    ->schema([
                        Forms\Components\FileUpload::make('invoice_file')
                            ->label('Invoice Resmi')
                            ->directory('delivery-orders/invoices')
                            ->openable()
                            ->downloadable(),

                        Forms\Components\FileUpload::make('do_file_unsigned')
                            ->label('Surat Jalan (Draft/Tanpa TTD)')
                            ->directory('delivery-orders/do')
                            ->openable()
                            ->downloadable(),

                        Forms\Components\FileUpload::make('checklist_file')
                            ->label('Checklist Barang & Aksesoris')
                            ->directory('delivery-orders/checklists')
                            ->openable()
                            ->downloadable(),
                    ])->columns(2),

                Forms\Components\Section::make('Bukti Pengiriman')
                    ->description('Diisi setelah barang diterima customer')
                    ->schema([
                        Forms\Components\FileUpload::make('do_file_signed')
                            ->label('Surat Jalan (Sudah TTD Customer)')
                            ->directory('delivery-orders/do-signed')
                            ->openable()
                            ->downloadable(),

                        Forms\Components\FileUpload::make('photos')
                            ->label('Foto Barang Sampai')
                            ->directory('delivery-orders/proofs')
                            ->multiple()
                            ->panelLayout('grid')
                            ->image()
                            ->openable()
                            ->downloadable(),
                    ])->columns(2),

                Forms\Components\Section::make('Catatan')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Pengiriman')
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('do_number')
                    ->label('Nomor DO')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('salesOrder.so_number')
                    ->label('Ref SO')
                    ->searchable(),

                Tables\Columns\TextColumn::make('schedule_date')
                    ->label('Jadwal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processed' => 'warning',
                        'shipped' => 'info',
                        'delivered' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('courier_name')
                    ->label('Kurir')
                    ->searchable(),

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
            'index' => Pages\ListDeliveryOrders::route('/'),
            'create' => Pages\CreateDeliveryOrder::route('/create'),
            'edit' => Pages\EditDeliveryOrder::route('/{record}/edit'),
        ];
    }
}
