<?php

namespace Axdron\Radianti\Services;

use PDO;

/**
 * RadiantiConnectionService
 *
 * Classe para preparar conexões com banco de dados MySQL, de forma a suportar unix_socket,
 * necessário para o funcionamento do Adianti Framework em ambientes como o Google App Engine.
 *
 * Fornece utilitário para construir a DSN e instanciar um PDO com as opções de charset e timezone.
 *
 * Uso: esta classe foi projetada para ser utilizada por `Adianti\Database\TConnection` na preparação
 * de conexões (por exemplo, em ambientes que precisam de suporte a unix_socket).
 *
 * @package Axdron\Radianti\Services
 * @see \Adianti\Database\TConnection
 */
class RadiantiConnectionService
{
    /**
     * Prepara e retorna uma conexão PDO para MySQL.
     *
     * @param string $host Host do banco ou caminho do unix socket quando $snConexaoSocket = true.
     * @param string $name Nome do banco de dados (dbname).
     * @param string $user Usuário do banco.
     * @param string $pass Senha do banco.
     * @param string $char Charset desejado ('ISO', 'utf8mb4' ou outro).
     * @param string|null $zone Timezone a ser aplicado via SET time_zone (ex: '+00:00').
     * @param string $opts String adicional para DSN (ex: ;... ).
     * @param int $port Porta do MySQL (padrão 3306).
     * @param bool $snConexaoSocket Se true monta a DSN com unix_socket em vez de host:port.
     * @return PDO Instância de PDO configurada para acesso ao banco.
     * @throws \PDOException Se ocorrer erro na criação da conexão.
     */
    public function prepararConexaoMysql($host, $name, $user, $pass, $char, $zone, $opts,  $port = 3306, $snConexaoSocket = false)
    {
        $stringHost = $snConexaoSocket ? "unix_socket={$host}" : "host={$host};port={$port}";

        if ($char == 'ISO') {
            $options = array();

            if ($zone) {
                $options = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '{$zone}'");
            }

            $conn = new PDO("mysql:{$stringHost};dbname={$name}{$opts}", $user, $pass, $options);
        } elseif ($char == 'utf8mb4') {
            $zone = $zone ? ";SET time_zone = '{$zone}'" : "";

            $conn = new PDO("mysql:{$stringHost};dbname={$name}{$opts}", $user, $pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4{$zone}"));
        } else {
            $zone = $zone ? ";SET time_zone = '{$zone}'" : "";

            $conn = new PDO("mysql:{$stringHost};dbname={$name}{$opts}", $user, $pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8{$zone}"));
        }

        return $conn;
    }
}
