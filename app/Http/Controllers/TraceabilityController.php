<?php
namespace App\Http\Controllers;

use App\Services\TraceabilityService;

class TraceabilityController extends Controller
{
    protected $traceabilityService;

    public function __construct(TraceabilityService $traceabilityService)
    {
        $this->traceabilityService = $traceabilityService;
    }

    public function traceFromReport($id)
    {
        $result = $this->traceabilityService->traceFromReport($id);
        return response()->json($result);
    }

    public function traceFromSupplier($id)
    {
        $result = $this->traceabilityService->traceFromSupplier($id);
        return response()->json($result);
    }
}
