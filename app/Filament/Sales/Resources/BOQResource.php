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
                    ->bulleted(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR'),
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

        return parent::getEloquentQuery()
            ->where(function ($query) use ($userId, $user) {
                // Show BOQ created by user themselves
                $query->where('user_id', $userId);

                // OR show BOQ from subordinates (where this user is atasan)
                $query->orWhereHas('user', function ($q) use ($userId) {
                    $q->where('atasan_id', $userId);
                });

                // OR show BOQ from same provinces
                if ($user->provinces && $user->provinces->count() > 0) {
                    $provinceIds = $user->provinces->pluck('id')->toArray();
                    $query->orWhereHas('visit.customer.city.province', function ($q) use ($provinceIds) {
                        $q->whereIn('provinces.id', $provinceIds);
                    });
                }

                // OR show BOQ from same cities
                if ($user->cities && $user->cities->count() > 0) {
                    $cityIds = $user->cities->pluck('id')->toArray();
                    $query->orWhereHas('visit.customer.city', function ($q) use ($cityIds) {
                        $q->whereIn('cities.id', $cityIds);
                    });
                }
            });
    }
}
