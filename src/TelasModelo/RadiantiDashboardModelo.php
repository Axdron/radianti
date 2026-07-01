<?php

declare(strict_types=1);

namespace Axdron\Radianti\TelasModelo;

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Base\TScript;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Wrapper\BootstrapFormBuilder;
use Axdron\Radianti\Services\RadiantiNavegacao;

/**
 * Classe abstrata para padronização de Dashboards
 * Fornece estrutura base para criação de dashboards com filtros e indicadores
 */
abstract class RadiantiDashboardModelo extends TPage
{

    protected $filtros = [];
    protected TVBox $container;
    protected TForm $form;

    /**
     * Retorna o título do dashboard
     */
    abstract protected static function getTituloDashboard(): string;

    /**
     * Retorna a descrição/explicação do dashboard
     */
    abstract protected static function getExplicacaoDashboard(): string;

    /**
     * Cria os campos do formulário de filtros
     * @return array Array com os campos do formulário
     * 
     * Exemplo:
     * return [
     *     ['label' => 'Data Inicial', 'campo' => $dataInicial],
     *     ['label' => 'Data Final', 'campo' => $dataFinal],
     * ];
     */
    abstract protected function criarCamposFiltros(): array;

    /**
     * Cria as seções de indicadores do dashboard
     * @return array Array com TElements das seções
     */
    abstract protected function criarSecoes(): array;

    /**
     * Valida e sanitiza os filtros recebidos
     * @param array $param Parâmetros recebidos
     * @return array Filtros validados e sanitizados
     */
    abstract protected function aplicarValidacoesFiltros(array $param): array;

    /**
     * Trata e converte tipos de filtros conforme configurado
     * 
     * @param array $filtros Filtros a tratar
     * @param array $configuracao Array com a configuração de conversão
     *                             Exemplo: ['prestador_id' => 'int', 'especialidade_id' => 'int']
     * @return array Filtros tratados e convertidos
     * 
     * Tipos suportados: 'int', 'float', 'string', 'bool'
     */
    protected function tratarFiltros(array $filtros, array $configuracao): array
    {
        foreach ($configuracao as $campo => $tipo) {
            if (!isset($filtros[$campo]) || empty($filtros[$campo])) {
                continue;
            }

            $valor = $filtros[$campo];

            switch ($tipo) {
                case 'int':
                    $filtros[$campo] = (int) $valor;
                    break;
                case 'float':
                    // Remover pontos e converter vírgula para ponto
                    if (is_string($valor)) {
                        $valor = str_replace('.', '', $valor);
                        $valor = str_replace(',', '.', $valor);
                    }
                    $filtros[$campo] = (float) $valor;
                    break;
                case 'string':
                    $filtros[$campo] = (string) $valor;
                    break;
                case 'bool':
                    $filtros[$campo] = (bool) $valor;
                    break;
            }
        }

        return $filtros;
    }

    /**
     * Construtor do dashboard
     */
    public function __construct($param = [])
    {
        parent::__construct();

        // Aplicar validações de segurança nos filtros desde a construção
        // Isso garante que o dashboard inicie com filtros válidos
        $this->filtros = $this->aplicarValidacoesFiltros($param);

        $this->container = new TVBox();
        $this->container->style = 'width: 100%';

        // Breadcrumb
        $this->container->add(new TXMLBreadCrumb('menu.xml', get_called_class()));

        // Título
        $titulo = new TElement('h2');
        $titulo->add(static::getTituloDashboard());
        $titulo->style = 'margin: 20px 0; color: #2c3e50;';
        $this->container->add($titulo);

        // Explicação (opcional)
        $explicacao = static::getExplicacaoDashboard();
        if (!empty($explicacao)) {
            $divExplicacao = new TElement('div');
            $divExplicacao->class = 'alert alert-info';
            $divExplicacao->add($explicacao);
            $this->container->add($divExplicacao);
        }

        // Formulário de filtros
        $this->container->add($this->criarFormularioFiltros($param));

        // Seções de indicadores
        $indicadoresContainer = new TElement('div');
        $indicadoresContainer->class = 'row';
        $indicadoresContainer->style = 'margin-top: 20px;';

        foreach ($this->criarSecoes() as $secao) {
            $indicadoresContainer->add($secao);
        }

        $this->container->add($indicadoresContainer);

        parent::add($this->container);
    }

    /**
     * Exibe a página do dashboard quando chamada via TAction
     */
    public static function onShow($param = null): void
    {
        new static($param);
    }

    /**
     * Cria o formulário de filtros
     */
    private function criarFormularioFiltros($param = []): TForm
    {
        $this->form = new TForm('form_filtros_' . get_called_class());
        $this->form->class = 'tform';

        $formBuilder = new BootstrapFormBuilder('form_filtros_' . get_called_class());
        $formBuilder->setFormTitle('Filtros');

        // Adicionar campos dinâmicos
        $campos = $this->criarCamposFiltros();
        foreach ($campos as $campo) {
            $label = new TLabel($campo['label']);
            if (!empty($param[$campo['campo']->getName()])) {
                $campo['campo']->setValue($param[$campo['campo']->getName()]);
            }
            $formBuilder->addFields([$label], [$campo['campo']]);
        }

        // Botão aplicar filtros
        $acao = new TAction([get_called_class(), 'validarFormulario']);
        $formBuilder->addAction('Aplicar Filtros', $acao, 'fas:filter');

        $this->form->add($formBuilder);
        // Definir valores com os filtros já validados e processados
        $this->form->setData((object) $this->filtros);

        return $this->form;
    }

    /**
     * Valida o formulário e aplica os filtros
     * Segue o padrão do RadiantiRelatorioModelo
     */
    public function validarFormulario($param): void
    {
        $dadosFormulario = $this->form->getData();
        try {
            $this->form->validate();
        } catch (\Exception $e) {
            new TMessage('error', $e->getMessage());
            return;
        } finally {
            $this->form->setData($dadosFormulario);
        }

        // Aplicar validações nos filtros
        $this->filtros = $this->aplicarValidacoesFiltros((array) $dadosFormulario);
    }

    // ==================== MÉTODOS DE CRIAÇÃO DE COMPONENTES ====================

    /**
     * Cria uma seção de indicadores com título e borda
     * 
     * @param string $titulo Título da seção (pode incluir ícone HTML)
     * @param string $corBorda Cor da borda inferior (hex ou nome de cor CSS)
     * @param string $classe Classe CSS adicional para a seção
     * @return TElement Container da seção
     */
    protected function criarSecaoDashboard(string $titulo, string $corBorda = '#3498db', string $classe = 'col-md-12', ?string $explicacao = null): TElement
    {
        $secao = new TElement('div');
        $secao->class = $classe;
        $secao->style = 'margin-bottom: 30px;';

        // Título da seção
        $tituloElement = new TElement('h3');
        $tituloElement->add($titulo);
        $tituloElement->style = "color: #34495e; margin-bottom: 10px; border-bottom: 2px solid {$corBorda}; padding-bottom: 10px;";
        $secao->add($tituloElement);

        // Explicação/label adicional (ex: dados D-1)
        if (!empty($explicacao)) {
            $explicacaoElement = new TElement('div');
            $explicacaoElement->add($explicacao);
            $explicacaoElement->style = 'margin-bottom: 15px;';
            $secao->add($explicacaoElement);
        }

        return $secao;
    }

    /**
     * Cria um card de indicador padronizado
     * 
     * @param string $titulo Título do indicador
     * @param string $valor Valor principal a exibir
     * @param string $descricao Descrição adicional
     * @param string $icone Classe do ícone FontAwesome
     * @param string $cor Cor do card (primary, success, danger, warning, info, secondary)
     * @param string $colClass Classes de coluna Bootstrap (ex: col-md-3)
     * @param TAction|null $acao Ação a ser executada ao clicar no card (opcional)
     * @return TElement Card HTML do Bootstrap
     */
    protected function criarCardIndicador(
        string $titulo,
        string $valor,
        string $descricao,
        string $icone,
        string $cor = 'primary',
        string $colClass = 'col-md-4',
        ?TAction $acao = null
    ): TElement {
        $card = new TElement('div');
        $card->class = $colClass . ' mb-4';

        $cardBody = new TElement('div');
        $cardBody->class = 'card-body';

        $row = new TElement('div');
        $row->class = 'row no-gutters align-items-center';

        $colText = new TElement('div');
        $colText->class = 'col mr-2';

        $tituloElement = new TElement('div');
        $tituloElement->class = "text-xs font-weight-bold text-{$cor} text-uppercase mb-1";
        $tituloElement->add($titulo);

        $valorElement = new TElement('div');
        $valorElement->class = 'h5 mb-0 font-weight-bold text-gray-800';
        $valorElement->add($valor);

        $descricaoElement = new TElement('small');
        $descricaoElement->class = 'text-muted';
        $descricaoElement->add($descricao);

        $colText->add($tituloElement);
        $colText->add($valorElement);
        $colText->add($descricaoElement);

        $colIcon = new TElement('div');
        $colIcon->class = 'col-auto';

        $iconElement = new TElement('i');
        $iconElement->class = $icone . " fa-2x text-{$cor}";
        $iconElement->style = 'opacity: 0.3;';

        $colIcon->add($iconElement);

        $row->add($colText);
        $row->add($colIcon);
        $cardBody->add($row);

        // Se houver ação, criar um link envolvendo o card
        if ($acao) {
            $url = $acao->serialize();
            $link = new TElement('a');
            $link->class = "card border-left-{$cor}";
            $link->style = 'border-left-width: 4px!important; height: 120px; cursor: pointer; transition: transform 0.2s; text-decoration: none; display: flex; flex-direction: column; justify-content: center;';
            $link->{'generator'} = 'adianti';
            $link->href = $url;
            $link->onmouseover = "this.style.transform='scale(1.02)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.1)';";
            $link->onmouseout = "this.style.transform='scale(1)'; this.style.boxShadow='';";
            $link->add($cardBody);
            $card->add($link);
        } else {
            $cardInner = new TElement('div');
            $cardInner->class = "card border-left-{$cor}";
            $cardInner->style = 'border-left-width: 4px!important; height: 120px;';
            $cardInner->add($cardBody);
            $card->add($cardInner);
        }

        return $card;
    }

    /**
     * Cria um container de cards (row)
     * 
     * @return TElement Container row do Bootstrap
     */
    protected function criarContainerCards(): TElement
    {
        $container = new TElement('div');
        $container->class = 'row';
        return $container;
    }

    /**
     * Adiciona container de cards a uma seção
     * 
     * @param TElement $secao Seção onde adicionar
     * @param TElement $container Container de cards
     * @return void
     */
    protected function adicionarCardsNaSecao(TElement $secao, TElement $container): void
    {
        $secao->add($container);
    }

    /**
     * Cria um card de erro/alerta
     * 
     * @param string $mensagem Mensagem de erro
     * @param string $tipo Tipo de alerta (danger, warning, info)
     * @return TElement Card de erro
     */
    protected function criarCardErro(string $mensagem, string $tipo = 'danger'): TElement
    {
        $erro = new TElement('div');
        $erro->class = "alert alert-{$tipo} col-md-12";
        $erro->add('<i class="fas fa-exclamation-circle"></i> ' . $mensagem);
        return $erro;
    }

    /**
     * Cria um card informativo
     * 
     * @param string $mensagem Mensagem informativa
     * @return TElement Card informativo
     */
    protected function criarCardInfo(string $mensagem): TElement
    {
        return $this->criarCardErro($mensagem, 'info');
    }

    // ==================== MÉTODOS DE FORMATAÇÃO ====================

    /**
     * Formata valor monetário
     * 
     * @param float|null $valor Valor a formatar
     * @param bool $incluirSimbolo Se deve incluir R$
     * @return string Valor formatado
     */
    protected function formatarValor(?float $valor, bool $incluirSimbolo = true): string
    {
        $valor = $valor ?? 0.0;
        $valorFormatado = number_format($valor, 2, ',', '.');
        return $incluirSimbolo ? "R$ {$valorFormatado}" : $valorFormatado;
    }

    /**
     * Formata número inteiro
     * 
     * @param int|null $numero Número a formatar
     * @return string Número formatado
     */
    protected function formatarNumero(?int $numero): string
    {
        $numero = $numero ?? 0;
        return number_format($numero, 0, ',', '.');
    }

    /**
     * Formata percentual
     * 
     * @param float $percentual Percentual a formatar
     * @param bool $incluirSinal Se deve incluir sinal + para positivos
     * @return string Percentual formatado
     */
    protected function formatarPercentual(float $percentual, bool $incluirSinal = false): string
    {
        $sinal = ($incluirSinal && $percentual > 0) ? '+' : '';
        return $sinal . number_format($percentual, 2, ',', '.') . '%';
    }

    /**
     * Determina cor baseada em crescimento (positivo/negativo)
     * 
     * @param float $valor Valor para avaliar
     * @return string Nome da cor (success, danger, secondary)
     */
    protected function determinarCorCrescimento(float $valor): string
    {
        if ($valor > 0) return 'success';
        if ($valor < 0) return 'danger';
        return 'secondary';
    }

    /**
     * Determina ícone baseado em crescimento (seta para cima/baixo)
     * 
     * @param float $valor Valor para avaliar
     * @return string Classe do ícone
     */
    protected function determinarIconeCrescimento(float $valor): string
    {
        if ($valor > 0) return 'fas fa-arrow-up';
        if ($valor < 0) return 'fas fa-arrow-down';
        return 'fas fa-minus';
    }

    /**
     * Abre o dashboard em uma nova guia do navegador
     * @param array $param Parâmetros para filtros iniciais
     */
    public static function abrir($param = [])
    {
        $stringClasse = get_called_class();
        if (!empty($param)) {
            $stringParametros = http_build_query($param);
            $stringClasse .= "&{$stringParametros}";
        }

        try {
            RadiantiNavegacao::abrirNovaGuia($stringClasse);
        } catch (\Exception $e) {
            // Fallback simples se RadiantiNavegacao não estiver disponível
            TScript::create("window.open('index.php?class={$stringClasse}', '_blank');");
        }
    }
}
