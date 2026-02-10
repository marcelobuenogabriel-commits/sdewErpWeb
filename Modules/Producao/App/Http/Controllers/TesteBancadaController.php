<?php

namespace Modules\Producao\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Producao\App\Models\PLCAPP03;
use Modules\Producao\App\Models\TKAEPDG;
use SebastianBergmann\Type\FalseType;

class TesteBancadaController extends Controller
{


    protected $tapp03;

    public function __construct()
    {
        $this->tapp03 = new PLCAPP03();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('producao::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('producao::create');
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
        return view('producao::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('producao::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    public function validarTestesOrdem($numorp)
    {
        $infoteste = $this->tapp03->gettestsordem($numorp); // Busca informações de testes na tabela
        $validateste = 0; // Variável indicadora de validação
        $mensagens = []; // Mensagens de retorno ao usuário
        $validatestemotor = TRUE;
        $validatestesensor = true;
        $validatestevalvula = true;

        // Etapa 1: Verificar necessidade de testes automáticos
        $validateste = $this->checkTestesAutomaticos($numorp);
        // Se não há necessidade de testes, permitir passar
        if ($validateste == 0) {
            return [
                "mensagens" => "Nenhum teste necessário! Validação OK.",
                "status" => True];

        }

        // Etapa 2: Verificar se os testes já foram realizados
        if ($infoteste->count() > 0) {
            // Testes encontrados, validar os resultados
            $tabela = $this->getTabelaName(session('bench')); // Tabela gerada pela bancada ativa
            $resulttest = $this->tapp03->selectordem($numorp, $tabela); // Seleciona registros de testes


            foreach ($infoteste as $infteste) {
                // Validar motores
                if ($infteste->QTDMOTOR > 0) {
                    $message = $this->verificaTesteMotor($resulttest, $infteste->QTDMOTOR, $validateste);
                    $validatestemotor = $message['status'];
                    $mensagens[] = $message['message'];
                }

                // Validar sensores
                if ($infteste->QTDSENSOR > 0) {
                    $message = $this->verificaTesteSensor($resulttest, $infteste->QTDSENSOR, $validateste);
                    $validatestesensor = $message['status'];
                    $mensagens[] = $message['message'];
                }

                // Validar válvulas
                if ($infteste->QTDVALVULA > 0) {
                    $message = $this->verificaTesteValvula($resulttest, $infteste->QTDVALVULA, $validateste);
                    $validatestevalvula = $message['status'];
                    $mensagens[] = $message['message'];
                }
            }

            // Após validar tudo, permitir que passe se não houver falhas
            if ($validatestemotor && $validatestesensor && $validatestevalvula) {
                $mensagens[] = "Testes já realizados e aprovados.";
                return [
                   "mensagens" => implode("<br>", $mensagens),
                    "status" => TRUE
                ];
            }
        }

        // Etapa 3: Testes não realizados ou resultados insatisfatórios
        $mensagens[] = "Ordem enviada para o quadro de testes. Aguarde a realização dos testes.";
        $this->checkTestesAutomaticos($numorp); // Insere a ordem na tabela para realização dos testes

        return [
            "mensagens" => implode("<br>", $mensagens),
            "status" => False
        ];
    }

    private function verificaTesteMotor($resulttest, $qtdmotor, &$validateste)
    {
        $testmotorfailed = 0;
        $motorsOn = 0;

        // Loop pelos motores
        for ($i = 1; $i <= $qtdmotor; $i++) {
            $field = 'STATUS_MOTOR_0' . $i;

            foreach ($resulttest as $result) {
                if ($result->$field == 2) {
                    $testmotorfailed = 1; // Falha detectada
                } elseif ($result->$field == 1) {
                    $motorsOn++; // Motor validado
                }
            }
        }

        if ($testmotorfailed || $motorsOn == 0) {
            $validateste = 1;
            return [
                "message" => "Falha no teste de motor!",
                "status" => false
            ];
        }

        return [
            "message" => "Motor testado e validado com sucesso.",
            "status" => true
        ];
    }

    private function verificaTesteSensor($resulttest, $qtdsensor, &$validateste)
    {
        $testsensorfailed = 0;
        $sensorsOn = 0;

        // Loop pelos sensores
        for ($i = 1; $i <= $qtdsensor; $i++) {
            $field = 'STATUS_SENSOR_0' . $i;

            foreach ($resulttest as $result) {
                if ($result->$field == 2) {
                    $testsensorfailed = 1; // Falha detectada
                } elseif ($result->$field == 1) {
                    $sensorsOn++; // Sensor validado
                }
            }
        }

        if ($testsensorfailed || $sensorsOn == 0) {
            $validateste = 1;
            return [
                "message" => "Falha no teste de sensor!",
                "status" => false
            ];
        }

        return [
            "message" => "Sensores testados e validados com sucesso.",
            "status" => true
            ];

    }

    private function verificaTesteValvula($resulttest, $qtdvalvula, &$validateste)
    {
        $testvalvulafailed = 0;
        $valvesOn = 0;
        $testeconfirmfailed = 0;
        $validConfirmations = 0;

        // Loop pelas válvulas
        for ($i = 1; $i <= $qtdvalvula; $i++) {
            $field = 'STATUS_VALVULA_0' . $i;

            foreach ($resulttest as $result) {
                if ($result->$field == 2) {
                    $testvalvulafailed = 1; // Falha detectada
                } elseif ($result->$field == 1) {
                    $valvesOn++; // Válvula validada
                }
            }
        }

        // Confirmação do teste
        foreach ($resulttest as $result) {
            if ($result['TESTE_VALVULA_OK'] == 2) {
                $testeconfirmfailed = 1; // Falha nas confirmações
            } elseif ($result['TESTE_VALVULA_OK'] == 1) {
                $validConfirmations++;
            }
        }

        if (($testvalvulafailed == 0 && $valvesOn == 0) || ($testeconfirmfailed == 0 && $validConfirmations == 0)) {
            $validateste = 1;
            return [
                "message" => "Falha no teste de válvula ou ausência da confirmação!",
                "status" => false
            ];
        } elseif ($testvalvulafailed == 1 || $testeconfirmfailed == 1) {
            $validateste = 1;
            return [
                "message" => "Falha no teste de válvula!",
                "status" => false
            ];
        }

        return [
            "message" => "Válvulas testadas e validadas com sucesso.",
            "status" => true
        ];
    }

    private function getTabelaName($bench)
    {
        // Retorna o nome da tabela com base na bancada
        return str_replace('BA', '', $bench);
    }

    public function checkTestesAutomaticos($numorp)
    {
        $tkaepdg = new TKAEPDG();
        $tplcapp = new PLCAPP03();

        $qtdmotor = 0;
        $qtdsensormotor = 0;
        $qtdvalvula = 0;
        $qtdsensor = 0;
        $testebancada = 0;

        $resultmotor = $tkaepdg->checkmotor($numorp);

        if (!empty($resultmotor->usu_desper)) {
            $arraycheck = explode("|", $resultmotor->usu_desper);

            foreach ($arraycheck as $array) {
                $array = str_replace(" ", "", $array);

                if (strpos($array, "STPA1=") !== false) {
                    $qtdmotor = (int) substr($array, strpos($array, '=') + 1);
                }

                if ($qtdmotor === 0 && (strpos($array, "MRA3=") !== false || strpos($array, "MRA1=") !== false)) {
                    $qtdmotor = (int) substr($array, strpos($array, '=') + 1);
                }
            }

            if ($qtdmotor > 0) {
                $resultsensormotor = $tkaepdg->checksensormotor($numorp);
                $qtdsensormotor = isset($resultsensormotor->QTDSENSOR) ? (int) $resultsensormotor->QTDSENSOR : 0;
            }

            $testebancada = 1;

        } else {
            $resultsensor = $tkaepdg->checkvalvulas($numorp);

            if (!empty($resultsensor->usu_desper)) {
                $arraycheck = explode("|", $resultsensor->usu_desper);

                foreach ($arraycheck as $array) {
                    $array = str_replace(" ", "", $array);

                    if (strpos($array, "STPA1=") !== false) {
                        $qtdvalvula = (int) substr($array, strpos($array, '=') + 1);
                        $qtdsensor = $qtdvalvula;
                    }
                }

                $testebancada = 1;
            }
        }

        $tplcapp->deletebench(session('bench')); // Remove entradas antigas para a ordem

        // Inserir na tabela caso os testes sejam necessários e ainda não tenham sido registrados
        if ($testebancada === 1) {
            $tplcapp->inserttest(
                $numorp,
                session('bench'),
                $qtdmotor,
                $qtdsensormotor,
                $qtdvalvula,
                $qtdsensor
            );

        }

        return $testebancada; // Retorna se foi necessário criar testes ou não
    }

}
