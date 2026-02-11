<?php

namespace Modules\Sefaz\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\FunctionCenter\App\Http\Controllers\FunctionCenterController;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Tools;

class SefazController extends Controller
{

    protected $certificate;

    protected $pw_certificate;

    protected $to;

    protected $content;

    public function __construct()
    {
        $this->middleware('auth');
        $this->certificate = Storage::get('certificate/KSA.pfx');
        $this->pw_certificate = 'Knapp2025';
        $this->to = 'nfe.fiscal@knapp.com.br';
        $this->content = 'functioncenter::emails.sendEmailSefaz';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('sefaz::index');
    }

    /**
     * Show the form for creating a new resource.
     *     *
     */
    public function create()
    {
        return view('sefaz::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $chave = $id;

        $configJson = [
            'atualizacao' => '2015-10-02 06:01:21',
            'razaosocial' => 'KNAPP SUDAMERICA LOGISTICA AUTOMACAO LTDA',
            'cnpj' => '02322789000122',
            'siglaUF' => 'PR',
            'schemes' => 'PL_009_V4',
            'versao' => '4.00',
            'tpAmb' => 1
        ];

        try{
            $tools = new Tools(json_encode($configJson), Certificate::readPfx($this->certificate, $this->pw_certificate));
            $tools->model("55");
            $tools->setEnvironment(1);
            $response = $tools->sefazDownload($chave);

            $stz = new Standardize($response);
            $std = $stz->toStd();

            if($std->cStat != 138){
                return [
                    'code' => '500',
                    'msg' => 'Documento não retornado'. $std->cStat .' '. $std->xMotivo
                ];
            }

            $zip = $std->loteDistDFeInt->docZip;
            $xml = gzdecode(base64_decode($zip));
            Storage::put("xml/". $id . ".xml", $xml);
            $path_file = Storage::path("xml/". $id .".xml");

            $webservice = new FunctionCenterController();
            $webservice->sendEmail("Nota Fiscal ".$chave, $this->to, '', $this->content, $path_file);

            return [
                'code' => '200',
                'msg' => 'Nota fiscal em processamento'
            ];

        } catch (\Exception $e){
            return [
                'code' => '500',
                'msg' => $e->getMessage()
            ];
        }

        return view('sefaz::show');
    }
}
