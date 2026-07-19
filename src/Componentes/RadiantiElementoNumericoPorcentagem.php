<?php

namespace Axdron\Radianti\Componentes;

/**
 * Elemento Numérico para Porcentagem
 * 
 * 2 casas decimais com separador brasileiro (vírgula)
 */
class RadiantiElementoNumericoPorcentagem extends RadiantiElementoNumerico
{
    public function __construct(string $nome, $snAtualizaAoEfetuarPost = true, $snPreenchimentoEsquerdaParaDireita = false, $snPermiteNegativo = false)
    {
        parent::__construct(
            $nome,
            casasDecimais: 2,
            separadorDecimais: ',',
            separadorMilhares: '.',
            snAtualizaAoEfetuarPost: $snAtualizaAoEfetuarPost,
            snPreenchimentoEsquerdaParaDireita: $snPreenchimentoEsquerdaParaDireita,
            snPermiteNegativo: $snPermiteNegativo
        );
    }
}
