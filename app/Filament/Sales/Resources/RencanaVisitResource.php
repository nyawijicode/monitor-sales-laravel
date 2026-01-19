<?php

namespace App\Filament\Sales\Resources;

use App\Filament\Sales\Resources\RencanaVisitResource\Pages;
use App\Models\Visit;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RencanaVisitResource extends Resource
{
    protected static ?string $model = Visit::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Mapping dan Pipeline';

    protected static ?string $navigationLabel = 'Rencana Visit';

    protected static ?string $modelLabel = 'Rencana Visit';

    protected static ?string $pluralModelLabel = 'Rencana Visit';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Visit')
                    ->schema([
                        Forms\Components\TextInput::make('visit_number')
                            ->label('Nomor Visit')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generate: RV2601-xxxxx')
                            ->helperText('Nomor akan dibuat otomatis saat menyimpan'),

                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'nama_instansi')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(
                                fn($record) =>
                                $record->kode_customer . ' - ' . $record->nama_instansi
                            )
                            ->getSearchResultsUsing(function ($search) {
                                $userCities = auth()->user()->cities->pluck('id');
                                return Customer::where('is_active', true)
                                    ->where(function ($query) use ($search) {
                                        $query->where('nama_instansi', 'like', "%{$search}%")
                                            ->orWhere('kode_customer', 'like', "%{$search}%");
                                    })
                                    ->whereIn('city_id', $userCities)
                                    ->limit(50)
                                    ->get()
                                    ->map(fn($customer) => [
                                        'value' => $customer->id,
                                        'label' => $customer->kode_customer . ' - ' . $customer->nama_instansi,
                                    ]);
                            })
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $customer = Customer::find($state);
                                    if ($customer) {
                                        $set('customer_info', [
                                            'alamat' => $customer->alamat,
                                            'wilayah' => $customer->city ?
                                                $customer->city->province->name . ' - ' . $customer->city->name : '-',
                                            'nama_kontak' => $customer->nama_kontak,
                                            'telepon' => $customer->telepon,
                                            'jabatan' => $customer->jabatan,
                                        ]);
                                    }
                                }
                            }),

                        Forms\Components\Placeholder::make('customer_info')
                            ->label('Informasi Customer')
                            ->content(function ($get) {
                                $info = $get('customer_info');
                                if (!$info) return '-';

                                return view('filament.forms.customer-info', compact('info'));
                            })
                            ->hidden(fn($get) => !$get('customer_id')),

                        Forms\Components\DatePicker::make('visit_plan')
                            ->label('Tanggal Rencana Visit')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->minDate(now())
                            ->helperText('Pilih tanggal rencana kunjungan'),

                        Forms\Components\TextInput::make('status_awal_display')
                            ->label('Status Customer')
                            ->default('Lead')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Status awal customer akan tercatat sebagai Lead'),
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
                Tables\Columns\TextColumn::make('customer.kode_customer')
                    ->label('Kode Customer')
                    ->searchable(),
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
                    ),
                Tables\Columns\TextColumn::make('visit_plan')
                    ->label('Tanggal Rencana')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('statusAwal.name')
                    ->label('Status Awal')
                    ->badge()
                    ->color('warning'),
                Tables\Columns\IconColumn::make('visit_date')
                    ->label('Terealisasi')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->getStateUsing(fn($record) => !is_null($record->visit_date)),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('belum_terealisasi')
                    ->label('Belum Terealisasi')
                    ->query(fn($query) => $query->whereNull('visit_date'))
                    ->default(),
                Tables\Filters\Filter::make('sudah_terealisasi')
                    ->label('Sudah Terealisasi')
                    ->query(fn($query) => $query->whereNotNull('visit_date')),
            ])
            ->actions([
                Tables\Actions\Action::make('realisasi')
                    ->label('Realisasi')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('success')
                    ->hidden(fn($record) => !is_null($record->visit_date))
                    ->url(fn($record) => route('filament.sales.resources.realisasi-visits.edit', $record)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListRencanaVisits::route('/'),
            'create' => Pages\CreateRencanaVisit::route('/create'),
            'edit' => Pages\EditRencanaVisit::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }
}
