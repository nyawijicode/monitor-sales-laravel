<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use ReflectionClass;

class ApprovalHelper
{
    /**
     * Scan app/Models directory for classes utilizing HasApprovalWorkflow interface.
     * Returns array mapping Type => Label.
     */
    public static function getApprovalTypes(): array
    {
        $types = [];
        $modelPath = app_path('Models');

        if (!File::exists($modelPath)) {
            return $types;
        }

        $files = File::allFiles($modelPath);

        foreach ($files as $file) {
            $className = 'App\\Models\\' . $file->getFilenameWithoutExtension();

            if (class_exists($className)) {
                $reflection = new ReflectionClass($className);

                if ($reflection->implementsInterface(\App\Interfaces\HasApprovalWorkflow::class) && !$reflection->isAbstract()) {
                    $types[$className::getApprovalType()] = $className::getApprovalLabel();
                }
            }
        }

        return $types;
    }
}
