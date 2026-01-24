<?php

namespace App\Filament\Sales\Resources;

use App\Filament\Sales\Resources\SalesOrderResource\Pages;
use App\Models\SalesOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalesOrderResource extends Resource
{
    protected static ?string $model = SalesOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Order & Finance';

    protected static ?string $navigationLabel = 'Sales Order (SO)';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Order')
                    ->schema([
                        Forms\Components\Select::make('boq_id')
                            ->label('Nomor BOQ')
                            ->relationship('boq', 'boq_number', fn(Builder $query) => $query->where('approval_status', 'approved'))
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->boq_number} - " . ($record->visit->customer->nama_instansi ?? 'Unknown'))
                            ->searchable(['boq_number', 'visit.customer.nama_instansi'])
                            ->preload()
                            ->required()
                            ->helperText('Hanya BOQ yang sudah disetujui yang muncul')
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Auto generate SO number logic could go here
                            }),

                        Forms\Components\TextInput::make('so_number')
                            ->label('Nomor SO')
                            ->unique(ignoreRecord: true)
                            ->placeholder('SO/XXXX/XX/XX')
                            ->helperText('Nomor SO akan digenerate otomatis jika kosong (logic di backend)'),

                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'submitted' => 'Submitted (Menunggu Approval)',
                                'approved' => 'Approved (Siap Proses)',
                                'in_accounting' => 'Proses Accounting',
                                'paid_dp' => 'DP Lunas',
                                'completed' => 'Lunas',
                            ])
                            ->default('draft')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Dokumen Pendukung')
                    ->description('Upload dokumen dari Customer')
                    ->schema([
                        Forms\Components\FileUpload::make('po_spk_file')
                            ->label('Upload PO / SPK')
                            ->directory('sales-orders/po')
                            ->openable()
                            ->downloadable(),

                        Forms\Components\FileUpload::make('npwp_file')
                            ->label('Upload NPWP Perusahaan')
                            ->directory('sales-orders/npwp')
                            ->openable()
                            ->downloadable(),
                    ])->columns(2),

                Forms\Components\Section::make('Keuangan (DP)')
                    ->description('Proses Tagihan dan Pembayaran DP')
                    ->schema([
                        Forms\Components\FileUpload::make('dp_invoice_file')
                            ->label('Invoice DP (Dari Accounting)')
                            ->directory('sales-orders/invoices')
                            ->openable()
                            ->downloadable()
                            ->helperText('Diisi oleh Admin Sales / Accounting'),

                        Forms\Components\FileUpload::make('dp_payment_proof')
                            ->label('Bukti Bayar DP')
                            ->directory('sales-orders/payments')
                            ->openable()
                            ->downloadable()
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->helperText('Upload bukti transfer dari customer'),
                    ])->columns(2),

                Forms\Components\Section::make('Approval & Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('so_number')
                    ->label('Nomor SO')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('boq.boq_number')
                    ->label('Ref BOQ')
                    ->searchable(),

                Tables\Columns\TextColumn::make('boq.total_amount')
                    ->label('Nilai Deal')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'submitted' => 'warning',
                        'approved' => 'success',
                        'in_accounting' => 'info',
                        'paid_dp' => 'success',
                        'completed' => 'primary',
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
            'index' => Pages\ListSalesOrders::route('/'),
            'create' => Pages\CreateSalesOrder::route('/create'),
            'edit' => Pages\EditSalesOrder::route('/{record}/edit'),
        ];
    }
}
