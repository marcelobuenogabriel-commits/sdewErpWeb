<?php

namespace Modules\Recebimento\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Recebimento\App\Models\Recebimento;
use Modules\Sefaz\App\Http\Controllers\SefazController;
use Modules\WebService\App\Http\Controllers\WebServiceController;

class RecebimentoController extends Controller
{

    protected $table;

    protected $date;

    protected $web_service;

    public function __construct()
    {
        $this->middleware('auth');
        $this->table = new Recebimento();
        $this->web_service = new WebServiceController();
        $this->date = date('Y-m-d');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {   
        $listaNotas = $this->getNotas();

        return view('recebimento::recebimento.index', compact('listaNotas'), [
            'title' => 'Recebimento',
            'description' => 'Lançamento de Notas Fiscais',
            'descriptionPage' => 'Notas Fiscais - Disponíveis para Lançamento',
        ]);
    }

    public function store(Request $request)
    {
        $chvNfc = $request->numChv;

        // Remove espaços e caracteres não numéricos
        $cnpj = preg_replace('/\D/', '', $chvNfc);

        // O CNPJ está entre as posições 7 e 20 (base 1)
        $cnpj = substr($cnpj, 6, 14);
        
        /*
         * Procura a Nota fiscal pela Chave informada
         */
        if ($cnpj == '02322789000122') {
            $nfe = $this->table->findNfeImportacao($chvNfc);
        } else {
            $nfe = $this->table->findNfe($chvNfc);
        }

        if(empty($nfe)) {

            $sefaz = new SefazController();

            $result_sefaz = $sefaz->show($chvNfc);

            if($result_sefaz['code'] == '200') {
                $return = [
                    'code' => 200,
                    'msg' => $result_sefaz['msg']
                ];
            } else {
                $return = [
                    'code' => 500,
                    'msg' => $result_sefaz['msg']
                ];
            }

        } else if($nfe == '2') {
            $return = [
                'code' => 500,
                'msg' => 'Nota não faz parte do processo de Entrada.'
            ];

        } else {
            /*
            *   Verifica se a Nota Fiscal atende as Condições
            *
            *       Ordem de compra campo xPed XML
            *       Sequência da OC campo nItemPed XML
            */
            $numOcp = $nfe->NUMOCP;
            $seqIpo = $nfe->SEQIPO;

            /*
            *   Atualiza o campo USU_SITNFC para = 1 (Preparação)
            */
            if ($cnpj == '02322789000122') {
                $update_sit = $this->table->updateSitPrepImportacao($nfe);
            } else {
                $update_sit = $this->table->updateSitApr($chvNfc);
            }
            
            if($update_sit > 0) {
                if ($numOcp == 0 || $seqIpo == 0) {
                    $return = [
                        'code' => 202,
                        'msg' => 'Ordem de Compra e/ou Sequência do Produto não localizados no XML.'
                    ];
                } else {
                    $return = [
                        'code' => 200,
                        'msg' => 'Nota liberada.'
                    ];
                }
            } else {
                $return = [
                    'code' => 500,
                    'msg' => 'Situação não atualizada, contate o administrador do sistema.'
                ];
            }
        }

        return \response()->json($return);
    }

    public function printTag(Request $request)
    {
        $numOcp = $request->numOcp;
        $chvNfc = $request->chvNfc;
        $numNfc = $request->numNfc;
        $codFor = $request->codFor;

        if($numOcp == NULL || $chvNfc == NULL || $numNfc == NULL) {
            return [
                'code' => 500,
                'msg' => 'Sem informações necessárias para impressão. Contate o administrador.'
            ];
        }

        try {
            $returnXML = $this->table->findXml($chvNfc);
        } catch (\Throwable $th) {
            return [
                'code' => 500,
                'msg' => 'Erro ao buscar XML da Nota Fiscal. Contate o administrador.'
            ];
        }

        if (count($returnXML) > 0) {
            foreach ($returnXML as $value) {
                $returnWebService = $this->web_service->printerTag($value);
            }
        } else {
            $returnNfe = $this->table->findNfeNacional($numNfc, $codFor);

            // Se não encontrar o XML, tenta buscar a NFe Nacional
            if (!empty($returnNfe)) {
                foreach ($returnNfe as $value) {
                    $returnWebService = $this->web_service->printerTag($value);
                }
                
            } else {
                return [
                    'code' => 500,
                    'msg' => 'Nota Fiscal não localizada. Contate o administrador.'
                ];
            }
        }
        
        //$return_msg_web_service = $this->web_service->returnWebService($return_web_service);

        return \response()->json($returnWebService);
    }

    public function closeNfc(Request $request)
    {
        $chvNfc = $request->chvNfc;
        $numNfc = $request->numNfc;
        $codFor = $request->codFor;

        if($chvNfc == NULL) {
            $msg_return = [
                'code' => 500,
                'msg' => 'Sem informações necessárias para impressão. Contate o administrador.'
            ];
        }

        // Remove espaços e caracteres não numéricos
        $cnpj = preg_replace('/\D/', '', $chvNfc);

        // O CNPJ está entre as posições 7 e 20 (base 1)
        $cnpj = substr($cnpj, 6, 14);
        
        /*
         * Procura a Nota fiscal pela Chave informada
         */
        
        if ($cnpj == '02322789000122') {
            try {
                $this->table->closeNfcImportacao($numNfc, $codFor);
            } catch (\Throwable $th) {
                return $msg_return = ['code' => 500, 'msg' => 'Erro ao fechar a nota!'];
            }

            try {
                $this->table->closeIpcImportacao($numNfc, $codFor);
            } catch (\Throwable $th) {
                return $msg_return = ['code' => 500, 'msg' => 'Erro ao fechar a nota!'];
            }

            return $msg_return = ['code' => 200, 'msg' => 'Nota fechada com sucesso!'];
        } else {
            try {
               $this->table->closeIpc($chvNfc);
            } catch (\Throwable $th) {
                return $msg_return = ['code' => 500, 'msg' => 'Erro ao fechar a nota!'];
            }

            try {
                $this->table->closeNfc($chvNfc);
            } catch (\Throwable $th) {
                return $msg_return = ['code' => 500, 'msg' => 'Erro ao fechar a nota!'];
            }

            return $msg_return = ['code' => 200, 'msg' => 'Nota fechada com sucesso!'];
        }

        return \response()->json($msg_return);
    }

    public function changeOcp(Request $request)
    {
        $numOcp = $request->numOcp;
        $codFor = $request->codForOc;
        $numNfc = $request->numNfcOc;
        $chvNfc = $request->chvNfc;

        if($numOcp == NULL || $codFor == NULL || $numNfc == NULL || $chvNfc == NULL) {
            $msg_return = [
                'code' => 500,
                'msg' => 'Sem informações necessárias para impressão. Contate o administrador.'
            ];
        }

        $is_valid = $this->table->isValidOrdem($numOcp, $codFor);

       
        if(!empty($is_valid)) {

            $update_oc = $this->table->updateXmlOc($numOcp, $chvNfc);

            if($update_oc > 0) {
                return redirect()->route('adiciona_oc', [
                    'numocp' => $numOcp,
                    'chvnfc' => $chvNfc,
                    'numnfc' => $numNfc
                ]);
            }
        } else {
            return redirect()->back()->with('error', 'Ordem de Compra não validada. Por favor verificar se o Código do Fornecedor da Nota é o mesmo utilizado na OC.
                                                        Código Fornecedor da Nota Fiscal : '.$codFor);
        }
    }

    public function adicionaOc(Request $request)
    {
        // Assim você pega os valores enviados por GET (query string)
        $numOcp = $request->query('numocp');
        $chvNfc = $request->query('chvnfc');
        $numNfc = $request->query('numnfc');

        // Ou, de forma mais simples (funciona para GET e POST):
        // $numOcp = $request->input('numocp');
        // $chvNfc = $request->input('chvnfc');
        // $numNfc = $request->input('numnfc');

        if($numOcp == NULL || $chvNfc == NULL || $numNfc == NULL) {
            return redirect()->back()->with('error', 'Sem informações necessárias para impressão. Contate o administrador.');
        }

        try {
            $listaXml = $this->table->getItensXml($chvNfc);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Erro ao buscar XML da Nota Fiscal. Contate o administrador.');
        }

        try {
            $listaOcp = $this->table->getItensrdemCompra($numOcp);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Erro ao buscar Ordem de Compra. Contate o administrador.');
        }       

        return view('recebimento::recebimento.adicionaoc', compact('listaXml', 'listaOcp'), [
            'title' => 'Notas Fiscais',
            'description' => 'Adicionar Ordem de Compra',
        ]);
    }

    public function updateOcp(Request $request)
    {
        foreach ($request->all('itens') as $key => $item) {
            foreach ($item as $key2 => $value) {
                // Verifica se os campos necessários foram preenchidos
                if($value['chvnel'] == NULL || $value['numnfc'] == NULL || $value['seqipc'] == NULL || $value['seqipo'] == NULL) {
                    return $msg_return = ['code' => 500, 'msg' => 'Sem informações necessárias para atualização. Contate o administrador.'];
                }
            }
        }

        try {
            foreach ($request->all('itens') as $key => $item) {
                foreach ($item as $key2 => $value) {
                    // Verifica se os campos necessários foram preenchidos
                    $this->table->updateXmlOcp($value['chvnel'], $value['numnfc'], $value['seqipc'], $value['seqipo']);
                }
            }
            
            $msg_return = ['code' => 200, 'msg' => 'XML da Nota Fiscal atualizado com sucesso!'];
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Erro ao atualizar XML da Nota Fiscal. Contate o administrador.');
        }

        return \response()->json($msg_return);
        
    }
 

    public function removeNfc(Request $request) {
        $chvNfc = $request->chvNfc;
        $codFor = $request->codFor;
        $numNfc = $request->numNfc;

        if($chvNfc == NULL) {
            $msg_return = [
                'code' => 500,
                'msg' => 'Sem informações necessárias para remoção. Contate o administrador.'
            ];
        }

        $removeNfc = $this->table->removeNfc($chvNfc);

        if($removeNfc > 0) {

            dd(1);
            $msg_return = [
                'code' => 500,
                'msg' => 'Erro ao remover a nota, contate o administrador.'
            ];
        } else {
            $removeNfcImport = $this->table->removeNfcImportacao($numNfc, $codFor);
            
            if($removeNfcImport > 0) {
                $msg_return = [
                    'code' => 200,
                    'msg' => 'Nota removida com sucesso!'
                ];
            } else {
                $msg_return = [
                    'code' => 500,
                    'msg' => 'Erro ao remover a nota, contate o administrador.'
                ];
            }
        }

        return \response()->json($msg_return);
    }

    public function getNotas()
    {
        $auxCount = 1;
        $numNfc = 0;
        $codFor = 0;
        $arrayNota = [];
        $perNull = 0;
        $perNotNull = 0;
        $numOcp = 0;
        $chvNfc = 0;
        $seqIpo = 0;
        $nomFor = 0;

        $listaNotas = $this->table->getNotas();
        $tamanhoArray = count($listaNotas);
        
        if ($tamanhoArray > 0) {
            foreach ($listaNotas as $value) {
                
                if ($numNfc == 0 && $tamanhoArray > 1) {
                    if ($value->USU_USUALT == NULL) {
                        $perNull++;
                    } else {
                        $perNotNull++;
                        $perNull++;
                    }
                } else if ($tamanhoArray == 1) {
                    if ($value->USU_USUALT == NULL) {
                        $perNull++;
                    } else {
                        $perNotNull++;
                        $perNull++;
                    }

                    $percentualProgress = ($perNotNull / $perNull) * 100;

                    $codFor = $value->CODFOR;
                    $nomFor = $value->APEFOR;
                    $numNfc = $value->NUMNFC;
                    $numOcp = $value->NUMOCP;
                    $chvNfc = $value->CHVNEL;
                    $seqIpo = $value->SEQIPO;

                    $arrayNota[] = [
                        'NumNfc' => $numNfc,
                        'NumOcp' => $numOcp,
                        'ChvNfc' => $chvNfc,
                        'SeqIpo' => $seqIpo,
                        'DadosGerais' => [
                            'Fornecedor' => $nomFor,
                            'CodFor' => $codFor,
                            'Percentual' => number_format($percentualProgress, 2)
                        ]
                    ];
                } else if ($value->NUMNFC == $numNfc && $auxCount <> $tamanhoArray) {
                    if ($value->USU_USUALT == NULL) {
                        $perNull++;
                    } else {
                        $perNotNull++;
                        $perNull++;
                    }
                } else if ($auxCount == $tamanhoArray && $value->NUMNFC == $numNfc) {
                    if ($value->USU_USUALT == NULL) {
                        $perNull++;
                    } else {
                        $perNotNull++;
                        $perNull++;
                    }

                    $percentualProgress = ($perNotNull / $perNull) * 100;
                    $arrayNota[] = [
                        'NumNfc' => $numNfc,
                        'NumOcp' => $numOcp,
                        'ChvNfc' => $chvNfc,
                        'SeqIpo' => $seqIpo,
                        'DadosGerais' => [
                            'Fornecedor' => $nomFor,
                            'CodFor' => $codFor,
                            'Percentual' => number_format($percentualProgress, 2)
                        ]
                    ];

                    $perNull = 0;
                    $perNotNull = 0;
                    $percentualProgress = 0;

                } else {
                    $percentualProgress = ($perNotNull / $perNull) * 100;

                    $arrayNota[] = [
                        'NumNfc' => $numNfc,
                        'NumOcp' => $numOcp,
                        'ChvNfc' => $chvNfc,
                        'SeqIpo' => $seqIpo,
                        'DadosGerais' => [
                            'Fornecedor' => $nomFor,
                            'CodFor' => $codFor,
                            'Percentual' => number_format($percentualProgress, 2)
                        ]
                    ];
                    
                    $perNull = 0;
                    $perNotNull = 0;
                    $percentualProgress = 0;

                    if ($value->USU_USUALT == NULL) {
                        $perNull++;
                    } else {
                        $perNotNull++;
                        $perNull++;
                    }
                }

                $codFor = $value->CODFOR;
                $nomFor = $value->APEFOR;
                $numOcp = $value->NUMOCP;
                $seqIpo = $value->SEQIPO;
                $auxNumNfc = $numNfc;
                $numNfc = $value->NUMNFC;
                $chvNfc = $value->CHVNEL;
                $auxCount++;
            }

            if ($value->USU_USUALT == NULL) {
                $perNull++;
            } else {
                $perNotNull++;
                $perNull++;
            }

            $percentualProgress = ($perNotNull / $perNull) * 100;

            if ($auxNumNfc <> $value->NUMNFC) {
                $arrayNota[] = [
                    'NumNfc' => $numNfc,
                    'NumOcp' => $numOcp,
                    'ChvNfc' => $chvNfc,
                    'SeqIpo' => $seqIpo,
                    'DadosGerais' => [
                        'Fornecedor' => $nomFor,
                        'CodFor' => $codFor,
                        'Percentual' => number_format($percentualProgress, 2)
                    ]
                ];
            }
        }

        return $arrayNota;
    }

    public function conferencia()
    {
        /*
         * Consulta as notas disponíveis para Lançamento
         */
        $interval = new \DateInterval('P100D');
        $date_time = new \DateTime($this->date);

        $date = $date_time->sub($interval);

        $notasSDE = $this->table->findNotas($date->format('Y-m-d'));
        
        return view('recebimento::recebimento.acompanhamento', [
            'title' => 'Conferência de Notas Fiscais',
            'description' => 'Conferência de Notas Fiscais em Aberto',
            'descriptionPage' => 'Notas Fiscais - Conferência',
            'notasSDE' => $notasSDE
        ]);
    }
}
