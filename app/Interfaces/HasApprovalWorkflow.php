<?php

namespace App\Interfaces;

interface HasApprovalWorkflow
{
    /**
     * Get the unique type/name for the approval workflow.
     * Use consistent naming, e.g., 'BOQ', 'Sales Order'.
     */
    public static function getApprovalType(): string;

    /**
     * Get the human-readable label for the approval type.
     * e.g., 'BOQ (RAB & Penawaran)'
     */
    public static function getApprovalLabel(): string;
}
