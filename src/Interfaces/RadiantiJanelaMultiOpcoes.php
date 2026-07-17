<?php

declare(strict_types=1);

namespace Axdron\Radianti\Interfaces;

use Adianti\Control\TWindow;
use Adianti\Widget\Base\TElement;
use Adianti\Wrapper\BootstrapFormBuilder;

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
     */
    public function __construct(
        string $mensagem,
        array $opcoes,
        string $titulo = '',
        float $largura = 0.6,
        ?float $altura = null,
        ?string $nomeFormulario = null
    ) {
        $titulo = !empty($titulo) ? $titulo : 'Opções';

        $janela = TWindow::create($titulo, $largura, $altura);

        $formulario = new BootstrapFormBuilder($nomeFormulario ?? 'form_multi_opcoes');

        // Conteúdo da mensagem
        $conteudo = new TElement('div');
        $conteudo->style = 'margin-bottom: 20px; padding: 10px; border-radius: 4px;';
        $conteudo->add($mensagem);

        $formulario->addContent([$conteudo]);

        // Adicionar botões para cada opção
        foreach ($opcoes as $opcao) {
            if (empty($opcao['rotulo']) || empty($opcao['acao'])) {
                throw new \InvalidArgumentException("Cada opção deve ter 'rotulo' e 'acao' definidos.");
            }

            $rotulo = $opcao['rotulo'];
            $acao = $opcao['acao'];
            $icone = $opcao['icone'] ?? null;
            $classeBotao = $opcao['classe'] ?? 'btn btn-default';

            $botao = $formulario->addAction(
                $rotulo,
                $acao,
                $icone
            );
            $botao->class = $classeBotao;
        }

        $janela->add($formulario);
        $janela->show();
    }
}
