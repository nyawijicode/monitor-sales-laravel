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

                Forms\Components\Section::make('Status Approval')
                    ->schema([
                        Forms\Components\Placeholder::make('approvers_list')
                            ->label('Daftar Approver')
                            ->content(function (?AfterSales $record): \Illuminate\Support\HtmlString {
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
                    ->visible(fn(?AfterSales $record) => $record && $record->approvers()->exists()),
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

                Tables\Columns\TextColumn::make('approval_status_display')
                    ->label('Status Garansi')
                    ->badge()
                    ->color(function (AfterSales $record): string {
                        if ($record->warranty_status === 'rejected') return 'danger';
                        if ($record->warranty_status === 'approved') return 'success';

                        $approvers = $record->approvers;
                        if ($approvers->count() > 0) {
                            $approved = $approvers->where('status', 'approved')->count();
                            if ($approved > 0) return 'warning';
                            return 'info';
                        }

                        return 'gray';
                    })
                    ->formatStateUsing(function (AfterSales $record): string {
                        if ($record->approvers->count() === 0) return 'Draft/No Workflow';

                        $progress = $record->getApprovalProgress();
                        return match ($record->warranty_status) {
                            'approved' => "Disetujui ($progress)",
                            'rejected' => 'Ditolak',
                            default => "Menunggu ($progress)",
                        };
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
                Tables\Actions\Action::make('approve')
                    ->label('Setujui Garansi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Keterangan (Opsional)')
                            ->rows(3)
                            ->maxLength(1000),
                    ])
                    ->action(function (AfterSales $record, array $data) {
                        if ($record->approve(auth()->id(), $data['notes'] ?? null)) {
                            \Filament\Notifications\Notification::make()
                                ->title('Garansi Disetujui')
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
                    ->visible(fn(AfterSales $record) => $record->canBeApproved()),

                Tables\Actions\Action::make('reject')
                    ->label('Tolak Garansi')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Keterangan (Wajib)')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (AfterSales $record, array $data) {
                        if ($record->reject(auth()->id(), $data['notes'])) {
                            \Filament\Notifications\Notification::make()
                                ->title('Garansi Ditolak')
                                ->success()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->visible(fn(AfterSales $record) => $record->canBeApproved()),

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
