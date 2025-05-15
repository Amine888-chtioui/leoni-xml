<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportXmlRequest;
use App\Services\XmlImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class XmlImportController extends Controller
{
    protected $xmlImportService;
    
    /**
     * Constructor
     *
     * @param XmlImportService $xmlImportService
     */
    public function __construct(XmlImportService $xmlImportService)
    {
        $this->xmlImportService = $xmlImportService;
    }
    
    /**
     * Show import form
     *
     * @return View
     */
    public function index(): View
    {
        return view('xml.import');
    }
    
    /**
     * Process import
     *
     * @param ImportXmlRequest $request
     * @return RedirectResponse
     */
    public function store(ImportXmlRequest $request): RedirectResponse
    {
        $file = $request->file('xml_file');
        $originalName = $file->getClientOriginalName();
        $path = $file->path();
        
        $success = $this->xmlImportService->importXml($path, $originalName);
        
        if ($success) {
            return redirect()->route('dashboard')->with('success', 'XML file imported successfully!');
        }
        
        return redirect()->back()->with('error', 'Failed to import XML file. Please check the logs for more details.');
    }
}