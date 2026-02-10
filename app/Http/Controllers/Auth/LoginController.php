<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
//use Modules\WebService\App\Services\SeniorSoapService;
use Modules\WebService\App\Http\Controllers\WebServiceController;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    protected $webService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->webService = new WebServiceController();
    }

    public function login(\Illuminate\Http\Request $request)
    {
        $content = $this->webService->consultarUsuario(['user'=> $request->user, 'password'=> $request->password, 'auth_type'=>'wsse', 'soap_url' => 'sapiens_Synccom_senior_g5_co_ger_cad_usuario?wsdl']);

        if (is_null($content['erroExecucao']) || $content['erroExecucao'] == '') {

            $user = DB::table('R999USU')
            ->join('R910USU', 'R999USU.CODUSU', '=', 'R910USU.CODENT')
            ->where('nomusu', '=', $request->user)->get();

            if(Auth::loginUsingId(['codusu' => $user[0]->codusu])) {
                $request->session()->put(['name' => $user[0]->nomcom]);
                $request->session()->regenerate();

                return redirect()->intended('home');
            }
        } else {
            return redirect()->back()->with('error', 'Usuário ou senha inválidos, tente novamente!');
        }
    }
}
