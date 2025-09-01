<?php

namespace Axdron\Radianti\TraitsCadastro;

trait RadiantiTraitFormatacaoDetalhes
{

    abstract protected static function getNomeDetalhe(): string;

    protected static function formatarNomeDetalhe($snMinusculo = false)
    {
        $nomeDetalhe = get_called_class()::getNomeDetalhe();

        $nomeSemCarateresEspeciais = self::desformataCaracteresEspeciais($nomeDetalhe);

        if ($snMinusculo) {
            $nomeMinusculo = strtolower($nomeSemCarateresEspeciais);
            $nomeComUnderline = str_replace(' ', '_', $nomeMinusculo);
            return $nomeComUnderline;
        }

        $nomeSemEspacos = str_replace(' ', '', $nomeSemCarateresEspeciais);

        return $nomeSemEspacos;
    }

    protected static function getPrefixoDetalhe()
    {
        return 'detalhe_' . self::formatarNomeDetalhe(true) . '_';
    }

    static function getNomeCampo(string $nomeCampo)
    {
        return self::getPrefixoDetalhe() . $nomeCampo;
    }


    private static function desformataCaracteresEspeciais($valor)
    {
        $caracteresEspeciais = ['&', 'ç', 'Ç', 'ã', 'Ã'];
        $caracteresSubstitutos = ['E', 'c', 'C', 'a', 'A'];
        return str_replace($caracteresEspeciais, $caracteresSubstitutos, $valor);
    }
}
