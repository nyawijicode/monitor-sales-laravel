<?php

namespace App\Filament\Sales\Resources;

use App\Filament\Sales\Resources\BOQResource\Pages;
use App\Models\BOQ;
use App\Models\Visit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BOQResource extends Resource
{
    protected static ?string $model = BOQ::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Mapping dan Pipeline';

    protected static ?string $navigationLabel = 'RAB dan Penawaran';

    protected static ?string $modelLabel = 'BOQ';

    protected static ?string $pluralModelLabel = 'RAB dan Penawaran';
    protected static ?int $navigationSort = 3;
    // Use slug to customize route name to 'boqs' instead of 'b-o-q-s'
    protected static ?string $slug = 'boqs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Section 1: Informasi BOQ
                Forms\Components\Section::make('Informasi BOQ')
                    ->schema([
                        Forms\Components\TextInput::make('boq_number')
                            ->label('Nomor BOQ')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generate: BOQ/000001/I/26')
                            ->helperText('Nomor BOQ akan dibuat otomatis'),

                        Forms\Components\Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Pilih perusahaan untuk BOQ ini'),

                        Forms\Components\Hidden::make('visit_id')
                            ->default(fn() => request()->query('visit'))
                            ->required(),

                        Forms\Components\TextInput::make('visit_number')
                            ->label('Nomor Visit')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(function ($record) {
                                if ($record && $record->visit) {
                                    return $record->visit->visit_number;
                                }
                                $visitId = request()->query('visit');
                                if ($visitId) {
                                    $visit = Visit::find($visitId);
                                    return $visit?->visit_number;
                                }
                                return '-';
                            }),

                        Forms\Components\TextInput::make('customer_name')
                            ->label('Customer')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(function ($record) {
                                if ($record && $record->visit) {
                                    return $record->visit->customer->nama_instansi;
                                }
                                $visitId = request()->query('visit');
                                if ($visitId) {
                                    $visit = Visit::find($visitId);
                                    return $visit?->customer?->nama_instansi;
                                }
                                return '-';
                            }),
                    ])
                    ->columns(2),

                // Section 1.5: Status Approval (Readonly)
                Forms\Components\Section::make('Status Approval')
                    ->schema([
                        Forms\Components\Placeholder::make('approval_status_display')
                            ->label('Status')
                            ->content(function (?BOQ $record): string {
                                if (!$record) return '🟡 Belum Ada';

                                $status = $record->approval_status;
                                $progress = $record->getApprovalProgress();

                                return match ($status) {
                                    'pending' => "🟡 Menunggu Persetujuan ($progress)",
                                    'approved' => "✅ Disetujui ($progress)",
                                    'rejected' => '❌ Ditolak',
                                };
                            }),

                        Forms\Components\Placeholder::make('approvers_list')
                            ->label('Daftar Approver')
                            ->content(function (?BOQ $record): \Illuminate\Support\HtmlString {
                                if (!$record) {
                                    return new \Illuminate\Support\HtmlString('<em>Tidak ada persetujuan</em>');
                                }

                                $approvers = $record->approvers;
                                if ($approvers->isEmpty()) {
                                    return new \Illuminate\Support\HtmlString('<em>Tidak ada approver</em>');
                                }

                                $html = '<ul style="margin: 0; padding-left: 20px;">';

                                foreach ($approvers as $approver) {
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
                            })
                            ->visible(fn(?BOQ $record): bool => $record !== null),

                        Forms\Components\Placeholder::make('approved_by_display')
                            ->label('Disetujui/Ditolak Oleh')
                            ->content(fn(?BOQ $record): string => $record?->approvedBy?->name ?? '-')
                            ->visible(fn(?BOQ $record): bool => $record && $record->approval_status !== 'pending'),

                        Forms\Components\Placeholder::make('approved_at_display')
                            ->label('Waktu')
                            ->content(fn(?BOQ $record): string => $record?->approved_at?->format('d/m/Y H:i') ?? '-')
                            ->visible(fn(?BOQ $record): bool => $record && $record->approval_status !== 'pending'),

                        Forms\Components\Placeholder::make('approval_notes_display')
                            ->label('Keterangan')
                            ->content(fn(?BOQ $record): string => $record?->approval_notes ?? '-')
                            ->visible(fn(?BOQ $record): bool => $record && $record->approval_status !== 'pending' && !empty($record->approval_notes)),
                    ])
                    ->columns(2)
                    ->visible(fn(?BOQ $record): bool => $record !== null),

                // Section 2: BOQ/Request Barang
                Forms\Components\Section::make('BOQ/Request Barang')
                    ->description('Tambahkan item barang yang dibutuhkan')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('nama_barang')
                                    ->label('Nama Barang')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('qty')
                                    ->label('Qty')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1),

                                Forms\Components\TextInput::make('harga_barang')
                                    ->label('Harga Barang')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->minValue(0),

                                Forms\Components\TextInput::make('harga_penawaran')
                                    ->label('Harga Penawaran')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->minValue(0)
                                    ->helperText('Opsional - Jika kosong akan menggunakan harga barang'),

                                Forms\Components\Textarea::make('spesifikasi')
                                    ->label('Spesifikasi')
                                    ->rows(3)
                                    ->maxLength(1000)
                                    ->columnSpanFull(),

                                Forms\Components\FileUpload::make('foto')
                                    ->label('Foto Barang')
                                    ->image()
                                    ->imageEditor()
                                    ->directory('boq-items')
                                    ->visibility('public')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Barang')
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => $state['nama_barang'] ?? 'Item Baru')
                            ->deleteAction(
                                fn(Forms\Components\Actions\Action $action) => $action->requiresConfirmation()
                            ),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with(['persetujuan.approvers']))
            ->columns([
                Tables\Columns\TextColumn::make('boq_number')
                    ->label('Nomor BOQ')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('visit.visit_number')
                    ->label('Nomor Visit')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('visit.customer.nama_instansi')
                    ->label('Customer')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Jumlah Item')
                    ->counts('items')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('items.nama_barang')
                    ->label('Nama Barang')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('approval_status')
                    ->label('Status Approval')
                    ->badge()
                    ->color(function (BOQ $record): string {
                        if ($record->approval_status === 'rejected') return 'danger';
                        if ($record->approval_status === 'approved') return 'success';

                        // Pending - check if partial approval
                        if ($record->persetujuan) {
                            $approved = $record->persetujuan->approvers()->where('status', 'approved')->count();
                            if ($approved > 0) return 'warning'; // Partial approval
                        }
                        return 'info'; // No approvals yet
                    })
                    ->formatStateUsing(function (BOQ $record): string {
                        $progress = $record->getApprovalProgress();
                        return match ($record->approval_status) {
                            'pending' => "Menunggu ($progress)",
                            'approved' => "Disetujui ($progress)",
                            'rejected' => 'Ditolak',
                        };
                    }),
                Tables\Columns\TextColumn::make('approvedBy.name')
                    ->label('Approval Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Waktu Approval')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Approval Actions
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
                    ->action(function (BOQ $record, array $data) {
                        if ($record->approve(auth()->id(), $data['notes'] ?? null)) {
                            \Filament\Notifications\Notification::make()
                                ->title('BOQ Disetujui')
                                ->body('BOQ berhasil disetujui.')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Gagal Menyetujui')
                                ->body('Anda tidak memiliki akses untuk menyetujui BOQ ini.')
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Setujui BOQ')
                    ->modalDescription('Apakah Anda yakin ingin menyetujui BOQ ini?')
                    ->visible(
                        fn(BOQ $record): bool =>
                        $record->approval_status === 'pending' &&
                            $record->canBeApproved()
                    ),

                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Keterangan (Wajib)')
                            ->required()
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('Jelaskan alasan penolakan BOQ ini'),
                    ])
                    ->action(function (BOQ $record, array $data) {
                        if ($record->reject(auth()->id(), $data['notes'])) {
                            \Filament\Notifications\Notification::make()
                                ->title('BOQ Ditolak')
                                ->body('BOQ berhasil ditolak.')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Gagal Menolak')
                                ->body('Anda tidak memiliki akses untuk menolak BOQ ini.')
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Tolak BOQ')
                    ->modalDescription('Apakah Anda yakin ingin menolak BOQ ini?')
                    ->visible(
                        fn(BOQ $record): bool =>
                        $record->approval_status === 'pending' &&
                            $record->canBeApproved()
                    ),

                Tables\Actions\Action::make('reset_approval')
                    ->label('Reset Approval')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (BOQ $record) {
                        if ($record->resetApproval(auth()->id())) {
                            \Filament\Notifications\Notification::make()
                                ->title('Approval Direset')
                                ->body('Status approval BOQ telah direset ke pending.')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Gagal Reset')
                                ->body('Tidak dapat mereset approval BOQ yang masih pending.')
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Reset Approval')
                    ->modalDescription('Apakah Anda yakin ingin mereset approval BOQ ini? Status akan kembali ke pending.')
                    ->visible(
                        fn(BOQ $record): bool =>
                        auth()->user()->hasRole('Super Admin') &&
                            $record->approval_status !== 'pending'
                    ),

                Tables\Actions\Action::make('preview_pdf')
                    ->label('Preview PDF')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading('Preview RAB PDF')
                    ->modalWidth('7xl')
                    ->modalContent(fn($record) => view('filament.modals.boq-pdf-preview', [
                        'pdfUrl' => route('boq.pdf.preview', $record->id)
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),

                Tables\Actions\Action::make('download_pdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->form([
                        Forms\Components\Radio::make('type')
                            ->label('Pilih Tipe PDF')
                            ->options([
                                'user' => 'User (Tanpa TTD)',
                                'internal' => 'Internal (Dengan TTD)',
                            ])
                            ->default('user')
                            ->required()
                            ->helperText('User: untuk customer. Internal: untuk arsip (hanya jika sudah disetujui)'),
                    ])
                    ->action(function ($record, array $data) {
                        // Check if internal requires approval
                        if ($data['type'] === 'internal' && !$record->isFullyApproved()) {
                            \Filament\Notifications\Notification::make()
                                ->title('BOQ Belum Disetujui')
                                ->body('Download Internal hanya tersedia setelah semua approver menyetujui BOQ.')
                                ->danger()
                                ->send();
                            return null;
                        }

                        return redirect()->route('boq.pdf.download', [
                            'id' => $record->id,
                            'type' => $data['type']
                        ]);
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBOQS::route('/'),
            'create' => Pages\CreateBOQ::route('/create'),
            'edit' => Pages\EditBOQ::route('/{record}/edit'),
            'view' => Pages\ViewBOQ::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $userId = auth()->id();
        $user = auth()->user();

        // Super Admin can see everything - bypass all filters
        if ($user && $user->hasRole('Super Admin')) {
            return parent::getEloquentQuery();
        }

        return parent::getEloquentQuery()
            ->where(function ($query) use ($userId, $user) {
                // 1. Show BOQ created by user themselves
                $query->where('user_id', $userId);

                // 2. Show BOQ from subordinates (where this user is atasan)
                $query->orWhereHas('user', function ($q) use ($userId) {
                    $q->where('atasan_id', $userId);
                });

                // 3. Show BOQ that needs approval from this user (CRITICAL FIX!)
                $query->orWhereHas('persetujuan.approvers', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                });

                // 4. Show BOQ from same provinces
                if ($user && $user->provinces && $user->provinces->count() > 0) {
                    $provinceIds = $user->provinces->pluck('id')->toArray();
                    $query->orWhereHas('visit.customer.city.province', function ($q) use ($provinceIds) {
                        $q->whereIn('provinces.id', $provinceIds);
                    });
                }

                // 5. Show BOQ from same cities
                if ($user && $user->cities && $user->cities->count() > 0) {
                    $cityIds = $user->cities->pluck('id')->toArray();
                    $query->orWhereHas('visit.customer.city', function ($q) use ($cityIds) {
                        $q->whereIn('cities.id', $cityIds);
                    });
                }
            });
    }
}
