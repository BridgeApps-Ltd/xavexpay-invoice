<?php

namespace Crater\Exceptions;

use Exception;

class DatabaseNotConfiguredException extends Exception
{
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage()
            ], 422);
        }

        return redirect()->route('settings.database')
            ->with('error', $this->getMessage());
    }
} 