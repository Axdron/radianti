<?php

namespace Axdron\Radianti\Services;

use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Exception;
use Shuchkin\SimpleXLSXGen;

class RadiantiPlanilhaService
{


    static public function criarCsvDatagrid(String $nomeArquivoSemExtensao, $datagrid, $registros)
    {
        $arquivo = RadiantiArquivoTemporario::criar($nomeArquivoSemExtensao, 'csv');
        $handle = fopen($arquivo, 'w');
        $columns = $datagrid->getColumns();

        $csvColumns = [];
        foreach ($columns as $column) {
            $csvColumns[] = $column->getLabel();
        }
        fputcsv($handle, $csvColumns, ';');

        foreach ($registros as $registro) {
            $csvColumns = [];
            foreach ($columns as $column) {
                $name = $column->getName();
                $csvColumns[] = $registro->{$name};
            }
            fputcsv($handle, $csvColumns, ';');
        }
        fclose($handle);
        return $arquivo;
    }

    public static function gerarXLSX(string $nomeArquivo, array $matrizDados)
    {
        try {
            if ($matrizDados) {
                $arquivo = RadiantiArquivoTemporario::criar($nomeArquivo, 'xlsx');
                $xlsx = SimpleXLSXGen::fromArray($matrizDados);
                $xlsx->saveAs($arquivo);
                return $arquivo;
            }
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            return false;
        }
    }

    public static function gerarXLSXDatagrid(string $nomeArquivo, TDataGrid|BootstrapDatagridWrapper $datagrid)
    {
        $conteudoDatagrid = $datagrid->getOutputData();
        return self::gerarXLSX($nomeArquivo, $conteudoDatagrid);
    }
}
