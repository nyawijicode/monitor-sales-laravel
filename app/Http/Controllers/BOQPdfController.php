<?php

namespace App\Http\Controllers;

use App\Models\BOQ;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class BOQPdfController extends Controller
{
    public function preview($id)
    {
        $boq = BOQ::with([
            'visit.customer.city.province',
            'items',
            'user',
            'company',
            'persetujuan.approvers.user', // Load approvers with their user data
        ])->findOrFail($id);

        // Check authorization
        if ($boq->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $company = $boq->company;
        $location = $company?->city ?? 'Semarang';

        $pdf = Pdf::loadView('pdf.boq-pdf', [
            'boq' => $boq,
            'type' => 'user', // Preview always shows user version (no signatures)
            'company' => $company,
            'location' => $location,
        ]);

        $safeFilename = str_replace('/', '-', $boq->boq_number);
        return $pdf->stream('preview-' . $safeFilename . '.pdf');
    }

    public function download($id, $type)
    {
        // Validate type
        if (!in_array($type, ['user', 'internal'])) {
            abort(400, 'Invalid download type');
        }

        $boq = BOQ::with([
            'visit.customer.city.province',
            'items',
            'user',
            'company',
            'persetujuan.approvers.user', // Load approvers with their user data
        ])->findOrFail($id);

        // Check authorization
        if ($boq->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Check if internal download requires approval
        if ($type === 'internal' && !$boq->isFullyApproved()) {
            abort(403, 'BOQ belum disetujui semua approver');
        }

        $company = $boq->company;
        $location = $company?->city ?? 'Semarang';

        $pdf = Pdf::loadView('pdf.boq-pdf', [
            'boq' => $boq,
            'type' => $type,
            'company' => $company,
            'location' => $location,
        ]);

        $safeBoqNumber = str_replace('/', '-', $boq->boq_number);
        $filename = $safeBoqNumber . '-' . $type . '.pdf';

        return $pdf->download($filename);
    }
}
