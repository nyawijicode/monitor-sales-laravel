<?php

namespace App\Filament\Sales\Resources\BOQResource\Pages;

use App\Filament\Sales\Resources\BOQResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBOQ extends EditRecord
{
    protected static string $resource = BOQResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ViewAction::make(),
        ];
    }

    public function mount(int | string $record): void
    {
        parent::mount($record);

        if ($this->record->hasAnyApprovalAction()) {
            \Filament\Notifications\Notification::make()
                ->title('Akses Ditolak')
                ->body('BOQ yang sudah dalam proses approval tidak dapat diedit. Silakan reset approval terlebih dahulu.')
                ->danger()
                ->send();

            $this->redirect($this->getResource()::getUrl('view', ['record' => $record]));
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        // Recalculate total after items are updated
        $this->record->calculateTotal();
    }
}
