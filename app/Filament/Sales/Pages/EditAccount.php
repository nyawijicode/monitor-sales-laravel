<?php

namespace App\Filament\Sales\Pages;

use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;

class EditAccount extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Edit Akun';

    protected static ?string $title = 'Edit Akun';

    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.sales.pages.edit-account';

    public ?array $data = [];

    public function mount(): void
    {
        $user = auth()->user();
        $userInfo = $user->userInfo;

        $this->form->fill([
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'signature' => $userInfo?->signature ? [$userInfo->signature] : [],
            'no_hp' => $userInfo?->no_hp,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Akun')
                    ->description('Perbarui informasi akun Anda di sini.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('username')
                            ->label('Username')
                            ->required()
                            ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule) {
                                return $rule->where('id', '!=', auth()->id());
                            })
                            ->maxLength(255),
                        TextInput::make('password')
                            ->label('Password Baru')
                            ->password()
                            ->dehydrated(false)
                            ->autocomplete('new-password')
                            ->helperText('Kosongkan jika tidak ingin mengganti password'),
                        TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password')
                            ->password()
                            ->same('password')
                            ->dehydrated(false),
                    ])->columns(2),

                Section::make('Informasi Tambahan')
                    ->schema([
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('no_hp')
                            ->label('Nomor HP')
                            ->tel()
                            ->maxLength(20),
                        FileUpload::make('signature')
                            ->label('Tanda Tangan / Signature')
                            ->image()
                            ->imageEditor()
                            ->directory('signatures')
                            ->visibility('public')
                            // Handle logic to store only the path string
                            ->saveUploadedFileUsing(function ($file) {
                                return $file->store('signatures', 'public');
                            }),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $user = auth()->user();

        // Update User
        $userUpdateData = [
            'name' => $data['name'],
            'username' => $data['username'],
        ];

        if (!empty($data['password'])) {
            $userUpdateData['password'] = Hash::make($data['password']);
        }

        $user->update($userUpdateData);

        // Update UserInfo
        // Handle signature array to string conversion
        $signaturePath = null;
        if (!empty($data['signature'])) {
            // FileUpload returns array because of array state
            $signaturePath = is_array($data['signature']) ? array_values($data['signature'])[0] : $data['signature'];
        }

        $user->userInfo()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'no_hp' => $data['no_hp'],
                'signature' => $signaturePath,
            ]
        );

        Notification::make()
            ->title('Akun berhasil diperbarui')
            ->success()
            ->send();
    }
}
