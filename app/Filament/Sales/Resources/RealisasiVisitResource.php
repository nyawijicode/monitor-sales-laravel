<?php

namespace App\Filament\Sales\Resources;

use App\Filament\Sales\Resources\RealisasiVisitResource\Pages;
use App\Models\Visit;
use App\Models\ActivityType;
use App\Models\CustomerStatus;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class RealisasiVisitResource extends Resource
{
    protected static ?string $model = Visit::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Mapping dan Pipeline';

    protected static ?string $navigationLabel = 'Realisasi Visit';

    protected static ?string $modelLabel = 'Realisasi Visit';

    protected static ?string $pluralModelLabel = 'Realisasi Visit';
    protected static ?int $navigationSort = 2;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Section 1: Rencana Visit (Read-only)
                Forms\Components\Section::make('Rencana Visit')
                    ->description('Informasi rencana visit (tidak dapat diubah)')
                    ->schema([
                        Forms\Components\TextInput::make('visit_number')
                            ->label('Nomor Visit')
                            ->disabled(),
                        Forms\Components\TextInput::make('customer_name')
                            ->label('Customer')
                            ->disabled()
                            ->formatStateUsing(fn($record) => $record?->customer?->nama_instansi ?? '-'),
                        Forms\Components\TextInput::make('customer_wilayah')
                            ->label('Wilayah')
                            ->disabled()
                            ->formatStateUsing(
                                fn($record) =>
                                $record?->customer?->city ?
                                    $record->customer->city->province->name . ' - ' . $record->customer->city->name : '-'
                            ),
                        Forms\Components\DatePicker::make('visit_plan')
                            ->label('Tanggal Rencana')
                            ->disabled()
                            ->displayFormat('d/m/Y'),
                        Forms\Components\TextInput::make('status_awal_name')
                            ->label('Status Awal')
                            ->disabled()
                            ->formatStateUsing(fn($record) => $record?->statusAwal?->name ?? '-'),
                    ])
                    ->columns(2)
                    ->collapsed(),

                // Section 2: Realisasi Visit
                Forms\Components\Section::make('Realisasi Visit')
                    ->description('Lengkapi informasi realisasi kunjungan')
                    ->schema([
                        Forms\Components\Select::make('activity_id')
                            ->label('Tipe Aktivitas')
                            ->options(ActivityType::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->helperText('Pilih jenis aktivitas yang dilakukan'),

                        Forms\Components\Select::make('status_akhir')
                            ->label('Status Visit Akhir')
                            ->options(CustomerStatus::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->helperText('Status customer setelah visit'),

                        Forms\Components\DatePicker::make('visit_date')
                            ->label('Tanggal Realisasi Visit')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->maxDate(now())
                            ->helperText('Tanggal pelaksanaan visit'),

                        Forms\Components\FileUpload::make('photo')
                            ->label('Bukti Visit (Foto)')
                            ->image()
                            ->imageEditor()
                            ->directory('visit-photos')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/*'])
                            ->helperText('Upload foto bukti kunjungan'),

                        Forms\Components\Toggle::make('is_join_visit')
                            ->label('Join Visit')
                            ->inline(false)
                            ->reactive()
                            ->helperText('Aktifkan jika ada sales lain yang ikut visit'),

                        Forms\Components\Select::make('participants')
                            ->label('Peserta Join Visit')
                            ->multiple()
                            ->relationship('participants', 'name')
                            ->preload()
                            ->searchable()
                            ->helperText('Pilih sales yang ikut dalam visit ini')
                            ->hidden(fn(Forms\Get $get) => !$get('is_join_visit')),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan Visit')
                            ->rows(3)
                            ->maxLength(1000)
                            ->helperText('Catatan atau keterangan tambahan (opsional)')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('visit_number')
                    ->label('Nomor Visit')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('customer.nama_instansi')
                    ->label('Customer')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('customer.city.name')
                    ->label('Wilayah')
                    ->formatStateUsing(
                        fn($record) =>
                        $record->customer->city ?
                            $record->customer->city->province->name . ' - ' . $record->customer->city->name : '-'
                    )
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('visit_date')
                    ->label('Tanggal Realisasi')
                    ->date('d/m/Y')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('activity.name')
                    ->label('Aktivitas')
                    ->badge()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('statusAwal.name')
                    ->label('Status Awal')
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('statusAkhir.name')
                    ->label('Status Akhir')
                    ->badge()
                    ->color('success')
                    ->placeholder('-'),
                Tables\Columns\IconColumn::make('photo')
                    ->label('Foto')
                    ->icon(fn($state) => $state ? 'heroicon-o-camera' : 'heroicon-o-x-mark')
                    ->color(fn($state) => $state ? 'success' : 'gray'),
                Tables\Columns\IconColumn::make('is_join_visit')
                    ->label('Join Visit')
                    ->boolean(),
                IconColumn::make('is_urgent')
                    ->label('Dadakan')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('belum_realisasi')
                    ->label('Belum Realisasi')
                    ->query(fn($query) => $query->whereNull('visit_date')),
                Tables\Filters\Filter::make('sudah_realisasi')
                    ->label('Sudah Realisasi')
                    ->query(fn($query) => $query->whereNotNull('visit_date'))
                    ->default(),
                Tables\Filters\SelectFilter::make('activity_id')
                    ->label('Tipe Aktivitas')
                    ->relationship('activity', 'name'),
                Tables\Filters\SelectFilter::make('status_akhir')
                    ->label('Status Akhir')
                    ->relationship('statusAkhir', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('boq')
                    ->label('BOQ')
                    ->icon('heroicon-o-document-text')
                    ->color('primary')
                    ->hidden(fn($record) => is_null($record->visit_date) || $record->boq !== null)
                    ->url(function ($record) {
                        if ($record->boq) {
                            return route('filament.sales.resources.boqs.edit', ['record' => $record->boq->id]);
                        }
                        return route('filament.sales.resources.boqs.create', ['visit' => $record->id]);
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('visit_number', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRealisasiVisits::route('/'),
            'edit' => Pages\EditRealisasiVisit::route('/{record}/edit'),
            'view' => Pages\ViewRealisasiVisit::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }
}
