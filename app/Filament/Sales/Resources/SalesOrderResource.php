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
                                'rejected' => 'Rejected (Ditolak)',
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

                Forms\Components\Section::make('Status Approval')
                    ->schema([
                        Forms\Components\Placeholder::make('approvers_list')
                            ->label('Daftar Approver')
                            ->content(function (?SalesOrder $record): \Illuminate\Support\HtmlString {
                                if (!$record || $record->approvers->isEmpty()) {
                                    return new \Illuminate\Support\HtmlString('<em>Tidak ada approval workflow</em>');
                                }

                                $html = '<ul style="margin: 0; padding-left: 20px;">';
                                foreach ($record->approvers as $approver) {
                                    $icon = match ($approver->status) {
                                        'approved' => '✅',
                                        'rejected' => '❌',
                                        default => '⏳',
                                    };
                                    $statusText = match ($approver->status) {
                                        'approved' => '<strong style="color: green;">Disetujui</strong>',
                                        'rejected' => '<strong style="color: red;">Ditolak</strong>',
                                        default => '<em style="color: gray;">Menunggu</em>',
                                    };
                                    $name = $approver->user->name ?? '-';
                                    $html .= "<li>{$icon} {$name} - {$statusText}</li>";
                                }
                                $html .= '</ul>';
                                return new \Illuminate\Support\HtmlString($html);
                            }),
                    ])
                    ->visible(fn(?SalesOrder $record) => $record && $record->approvers()->exists()),
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
                        'rejected' => 'danger',
                        'in_accounting' => 'info',
                        'paid_dp' => 'success',
                        'completed' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('approval_status_display')
                    ->label('Status Approval')
                    ->badge()
                    ->color(function (SalesOrder $record): string {
                        if ($record->status === 'rejected') return 'danger';
                        if ($record->status === 'approved') return 'success';

                        // Pending - check if partial approval
                        $approvers = $record->approvers;
                        if ($approvers->count() > 0) {
                            $approved = $approvers->where('status', 'approved')->count();
                            if ($approved > 0) return 'warning'; // Partial approval
                            return 'info'; // Waiting
                        }

                        return 'gray'; // No approval workflow
                    })
                    ->formatStateUsing(function (SalesOrder $record): string {
                        if ($record->approvers->count() === 0) return '-';

                        $progress = $record->getApprovalProgress();
                        return match ($record->status) {
                            'submitted' => "Menunggu ($progress)",
                            'approved' => "Disetujui ($progress)",
                            'rejected' => 'Ditolak',
                            default => $record->status, // draft etc
                        };
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Keterangan (Opsional)')
                            ->rows(3)
                            ->maxLength(1000),
                    ])
                    ->action(function (SalesOrder $record, array $data) {
                        if ($record->approve(auth()->id(), $data['notes'] ?? null)) {
                            \Filament\Notifications\Notification::make()
                                ->title('Sales Order Disetujui')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Gagal Menyetujui')
                                ->body('Anda tidak memiliki akses.')
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->visible(fn(SalesOrder $record) => $record->canBeApproved()),

                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Keterangan (Wajib)')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (SalesOrder $record, array $data) {
                        if ($record->reject(auth()->id(), $data['notes'])) {
                            \Filament\Notifications\Notification::make()
                                ->title('Sales Order Ditolak')
                                ->success()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->visible(fn(SalesOrder $record) => $record->canBeApproved()),

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
