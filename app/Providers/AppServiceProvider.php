<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
       //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Dispatcher $events): void
    {
        // Em ambientes de desenvolvimento, limpe o cache de views em cada requisição
        if ($this->app->environment('local')) {
            // Limpa o cache do Finder de views
            view()->flushFinderCache();
        }

        /*
         * Força HTTPS route redirect
         */
        URL::forceScheme('https');

        /*
         * Valida as permissões dos usuários
         * Consulta no sistema ERP quais grupos estão liberados no SGU
         */
        Gate::before(function (User $user, $ability){
            return $user->hasPermissionTo($user, $ability);
        });

        $events->listen(BuildingMenu::class, function (BuildingMenu $event) {

            /**   Menu Estoque */
            $event->menu->add([
                'key' => 'Estoque',
                'text' => 'Estoque',
                'icon' => 'fas fa-cubes',
                'can' => ['Administrador', 'Estoque']
            ]);

            $event->menu->addIn('Estoque', [
                'key' => 'Picking',
                'text' => 'Picking',
                'icon' => 'fas fa-upload'
            ]);

            $event->menu->addIn('Picking', [
                'text' => 'Picking - Produção',
                'url' => 'picking/' . 4460,
                'icon' => '',
                'can' => ['Administrador', '1073741978']
            ]);

            $event->menu->addIn('Picking', [
                'text' => 'Picking - Dispach',
                'url' => 'dispatch/' . 5850,
                'icon' => '',
                'can' => ['Administrador', 'Estoque']
            ]);

            $event->menu->addIn('Picking', [
                'text' => 'Picking - C-Parts',
                'url' => 'cparts/' . 7600,
                'icon' => '',
                'can' => ['Administrador', 'Estoque']
            ]);

            $event->menu->addIn('Picking', [
                'text' => 'Picking - Insumos',
                'url' => 'insumos/' . 1,
                'icon' => '',
                'can' => ['Administrador', 'Estoque']
            ]);

            $event->menu->addIn('Picking', [
                'text' => 'Excluir Pallet',
                'url' => 'pallet',
                'icon' => '',
                'can' => ['Administrador', 'Estoque']
            ]);

            $event->menu->addIn('Estoque', [
                'text' => 'Inventário',
                'url' => 'inventario',
                'icon' => '',
                'can' => ['Administrador', 'Recebimento']
            ]);

            $event->menu->addIn('Estoque', [
                'key' => 'ReportRecebimento',
                'text' => 'Relatórios',
                'icon' => 'fas fa-print'
            ]);

            $event->menu->addIn('ReportRecebimento', [
                'text' => 'Etiqueta OC - (Por OC)',
                'url' => 'report/OC0001',
                'icon' => ''
            ]);

            $event->menu->addIn('ReportRecebimento', [
                'text' => 'Etiqueta OC - (Por Item)',
                'url' => 'report/OC0002',
                'icon' => ''
            ]);

            $event->menu->addIn('ReportRecebimento', [
                'text' => 'Imprimir Movimentação - 5850',
                'url' => 'report/OC0003',
                'icon' => ''
            ]);

            $event->menu->addIn('ReportRecebimento', [
                'text' => 'Etiqueta Estoque - PRBR014',
                'url' => 'report/OC0004',
                'icon' => ''
            ]);

            $event->menu->addIn('ReportRecebimento', [
                'text' => 'Etiqueta Estoque - PRBR006',
                'url' => 'report/OC0005',
                'icon' => ''
            ]);

            $event->menu->addIn('ReportRecebimento', [
                'text' => 'Etiqueta Estoque - PRBR007',
                'url' => 'report/OC0006',
                'icon' => ''
            ]);

            $event->menu->addIn('ReportRecebimento', [
                'text' => 'Etiqueta Lista - PRBR007',
                'url' => 'report/OC0007',
                'icon' => ''
            ]);
            /**   Fim - Menu Estoque */

            /**   Menu Recebimento */
            $event->menu->add([
                'key' => 'Recebimento',
                'text' => 'Recebimento',
                'icon' => 'fas fa-truck-loading',
                'can' => ['Administrador', 'Recebimento']
            ]);

            $event->menu->addIn('Recebimento', [
                'key' => 'entradanf',
                'text' => 'Entrada de NF',
                'icon' => 'fas fa-file',
                'can' => ['Administrador', 'Recebimento']
            ]);

            $event->menu->addIn('Recebimento', [
                'key' => 'prefatura',
                'text' => 'Pré-Fatura',
                'icon' => 'fas fa-file',
                'can' => ['Administrador', 'Recebimento']
            ]);

            $event->menu->addIn('entradanf', [
                'text' => 'Recebimento',
                'url' => 'recebimento',
                'icon' => '',
                'can' => ['Administrador', 'Recebimento']
            ]);

            $event->menu->addIn('entradanf', [
                'text' => 'Conferência',
                'url' => 'conferencia',
                'icon' => ''
            ]);

            $event->menu->addIn('entradanf', [
                'text' => 'Conferência Importação',
                'url' => 'conferenciaimp',
                'icon' => ''
            ]);

            $event->menu->addIn('entradanf', [
                'text' => 'Entradas Futuras',
                'url' => 'recebimento/conferencia',
                'icon' => ''
            ]);

            $event->menu->addIn('entradanf', [
                'text' => 'Impressão de Pallet',
                'url' => 'conferencia/pallet',
                'icon' => ''
            ]);

            $event->menu->addIn('prefatura', [
                'text' => 'Geração de Embalagens',
                'url' => 'recebimento/prefatura',
                'icon' => '',
                'can' => ['Administrador', 'Recebimento']
            ]);

            $event->menu->addIn('Recebimento', [
                'text' => 'Lista',
                'url' => 'lista',
                'icon' => '',
            ]);
            /**   Fim - Menu Recebimento */

            /**   Menu Financeiro */
            $event->menu->add([
                'key' => 'Financeiro',
                'text' => 'Financeiro',
                'icon' => 'fas fa-dollar-sign',
                'can' => ['Administrador', 'Financeiro']
            ]);

            $event->menu->addIn('Financeiro', [
                'key' => 'Invoices',
                'text' => 'Invoices',
                'icon' => 'fas fa-file-invoice-dollar'
            ]);

            $event->menu->addIn('Invoices', [
                'text' => 'Contratos',
                'url' => 'financeiro/invoice/contratos',
                'icon' => '',
            ]);

            $event->menu->addIn('Invoices', [
                'text' => 'Pedidos',
                'url' => 'financeiro/invoice/pedidos',
                'icon' => '',
                'can' => ['Administrador', 'Financeiro']
            ]);
            /**   Fim - Menu Financeiro */

            /**   Menu Produção */
            $event->menu->add([
                'key' => 'Producao',
                'text' => 'Produção',
                'icon' => 'fas fa-industry',
                'can' => ['Administrador', 'Producao']
            ]);

            $event->menu->addIn('Producao', [
                'text' => 'Movimentação',
                'icon' => 'fas fa-file',
                'url' => 'producao/handlingunit'
            ],[
                'text' => 'Agregação',
                'icon' => 'fas fa-file',
                'url' => 'producao/agregationorder'
            ]);
            /**   Fim - Menu Produção */

            /**   Menu Importação */
            $event->menu->add([
                'key' => 'Importacao',
                'text' => 'Importação',
                'icon' => 'fas fa-truck-loading',
                'can' => ['Administrador', 'Importacao']
            ]);

            $event->menu->addIn('Importacao', [
                'text' => 'Invoice',
                'icon' => 'fas fa-file-invoice-dollar',
                'url' => 'importacao'
            ]);
            /**   Fim - Menu Importação */

            /**   Menu Gráficos */
            $event->menu->add([
                'key' => 'Graficos',
                'text' => 'Gráficos',
                'icon' => 'fas fa-chart-line',
                'can' => ['Administrador', 'Graficos']
            ]);

            $event->menu->addIn('Graficos', [
                'key' => 'recursoshumanos',
                'text' => 'Recursos Humanos',
                'icon' => 'fas fa-users',
                'can' => ['Administrador', 'Graficos RH']
            ]);

            $event->menu->addIn('recursoshumanos', [
                'text' => 'Headcount',
                'icon' => 'fas fa-file-invoice-dollar',
                'url' => 'kpi/headcount',
            ]);

            $event->menu->addIn('recursoshumanos', [
                'text' => 'Divergências de Sistemas',
                'icon' => 'fas fa-file-invoice-dollar',
                'url' => 'kpi/systemcomparison',
            ]);

            $event->menu->addIn('recursoshumanos', [
                'text' => 'Comparação Salarial',
                'icon' => 'fas fa-file-invoice-dollar',
                'url' => 'kpi/salarycomparison',
            ]);
            /**   Fim - Menu Recursos Humanos */

        });
    }
}
