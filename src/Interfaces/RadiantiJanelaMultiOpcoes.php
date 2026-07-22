<?php

declare(strict_types=1);

namespace Axdron\Radianti\Interfaces;

use Adianti\Control\TWindow;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Base\TScript;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Form\TButton;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TForm;

/**
 * RadiantiJanelaMultiOpcoes
 * 
 * Cria uma janela com múltiplas opções de botões, cada uma disparando uma ação específica.
 * Diferente do TQuestion que permite apenas 2 opções, esta classe é escalável.
 * 
 * @package Axdron\Radianti\Interfaces
 */
class RadiantiJanelaMultiOpcoes
{
    /**
     * Construtor
     * 
     * @param string $mensagem Mensagem a ser exibida
     * @param array $opcoes Array de opções, formato:
     *                        [
     *                            [
     *                                'rotulo' => 'Texto do botão',
     *                                'acao' => TAction,
     *                                'icone' => 'fa:icon' (opcional),
     *                                'classe' => 'btn btn-primary' (opcional, padrão: btn btn-default)
     *                            ],
     *                            ...
     *                        ]
     * @param string $titulo Título da janela
     * @param float $largura Largura da janela em percentual (0-1)
     * @param float $altura Altura da janela em percentual (0-1, null para auto)
     * @param string|null $nomeFormulario Nome customizado do formulário
     * @param bool $fecharAposSelecao Se true, fecha a janela após selecionar uma opção
     */
    public function __construct(
        string $mensagem,
        array $opcoes,
        string $titulo = '',
        float $largura = 0.6,
        ?float $altura = null,
        ?string $nomeFormulario = null,
        bool $fecharAposSelecao = true
    ) {
        $titulo = !empty($titulo) ? $titulo : 'Opções';

        $janela = TWindow::create($titulo, $largura, $altura);

        $formulario = new TForm($nomeFormulario ?? 'form_multi_opcoes');
        $formulario->class = 'form-horizontal';

        $vbox = new TVBox;
        $vbox->style = 'width: 100%; padding: 20px; align-items: center; text-align: center;';

        // Conteúdo da mensagem
        $label = new TLabel($mensagem);
        $label->style = 'font-size: 14px; margin-bottom: 20px; color: #666; text-align: center;';
        $vbox->add($label);

        // Adicionar botões para cada opção
        foreach ($opcoes as $opcao) {
            if (empty($opcao['rotulo']) || empty($opcao['acao'])) {
                throw new \InvalidArgumentException("Cada opção deve ter 'rotulo' e 'acao' definidos.");
            }

            $rotulo = $opcao['rotulo'];
            $acao = $opcao['acao'];
            $icone = $opcao['icone'] ?? null;
            $classeBotao = $opcao['classe'] ?? 'btn btn-default';

            $botao = new TButton('btn_' . uniqid());
            $botao->setLabel($rotulo);
            if ($icone) {
                $botao->setImage($icone);
            }
            $botao->class = $classeBotao;
            $botao->style = 'margin: 10px; padding: 15px; width: 300px; text-align: center;';


            if ($fecharAposSelecao) {
                $botao->onclick = "$('#" . $janela->id . "').remove();  __adianti_load_page('{$acao->serialize()}')";
            } else {
                $botao->setAction($acao);
            }

            // Adicionar botão ao formulário
            $formulario->addField($botao);
            $vbox->add($botao);
        }

        $formulario->add($vbox);

        $janela->add($formulario);
        $janela->show();
    }
}
