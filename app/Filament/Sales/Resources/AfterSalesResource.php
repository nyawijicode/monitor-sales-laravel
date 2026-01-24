<?php

namespace App\Filament\Sales\Resources;

use App\Filament\Sales\Resources\AfterSalesResource\Pages;
use App\Models\AfterSales;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AfterSalesResource extends Resource
{
    protected static ?string $model = AfterSales::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Layanan Purna Jual';

    protected static ?string $navigationLabel = 'Garansi & Pelunasan';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Referensi Order')
                    ->schema([
                        Forms\Components\Select::make('sales_order_id')
                            ->label('Referensi Sales Order (SO)')
                            ->relationship('salesOrder', 'so_number', fn(Builder $query) => $query->whereIn('status', ['in_accounting', 'paid_dp', 'completed']))
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->so_number} - " . ($record->boq->visit->customer->nama_instansi ?? 'Unknown'))
                            ->searchable(['so_number', 'boq.visit.customer.nama_instansi'])
                            ->preload()
                            ->required()
                            ->helperText('Pilih Sales Order untuk proses pelunasan/garansi'),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending (Menunggu Tagihan)',
                                'billed' => 'Tagihan Dikirim',
                                'paid' => 'Lunas (Selesai)',
                            ])
                            ->default('pending')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Pelunasan (Finance)')
                    ->schema([
                        Forms\Components\FileUpload::make('final_billing_file')
                            ->label('Upload Tagihan Pelunasan')
                            ->directory('after-sales/invoices')
                            ->openable()
                            ->downloadable(),

                        Forms\Components\FileUpload::make('payment_proof_file')
                            ->label('Bukti Pelunasan')
                            ->directory('after-sales/payments')
                            ->openable()
                            ->downloadable(),
                    ])->columns(2),

                Forms\Components\Section::make('Garansi (After Sales)')
                    ->schema([
                        Forms\Components\FileUpload::make('warranty_letter_file')
                            ->label('Surat Garansi')
                            ->directory('after-sales/warranty')
                            ->openable()
                            ->downloadable(),

                        Forms\Components\Select::make('warranty_status')
                            ->label('Status Garansi')
                            ->options([
                                'draft' => 'Draft / Belum Terbit',
                                'approved' => 'Approved / Sudah Terbit',
                            ])
                            ->default('draft'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('salesOrder.so_number')
                    ->label('Ref SO')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'gray',
                        'billed' => 'warning',
                        'paid' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('warranty_status')
                    ->label('Garansi')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'approved' => 'success',
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
            'index' => Pages\ListAfterSales::route('/'),
            'create' => Pages\CreateAfterSales::route('/create'),
            'edit' => Pages\EditAfterSales::route('/{record}/edit'),
        ];
    }
}
