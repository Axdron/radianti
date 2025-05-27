<?php

namespace Axdron\Radianti\Services;

use Adianti\Database\TConnection;
use Adianti\Database\TTransaction;
use SessionHandlerInterface;

/**
 * RadiantiGerenciadorSessoes
 * A classe implementa o gerenciamento de sessões através de um banco de dados.
 * É importante que a tabela 'sessions' já exista no banco de dados com as colunas 'id', 'access' e 'data'.
 * A coluna 'id' deve ser do tipo VARCHAR(32), 'access' do tipo INT e 'data' do tipo LONGTEXT.
 */
class RadiantiGerenciadorSessoes implements SessionHandlerInterface
{

    public function open(string $savePath, string $sessionName): bool
    {
        if (TConnection::open('sample')) {
            return true;
        }
        return false;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string | false
    {
        TTransaction::openFake('sample');
        $conn = TTransaction::get();
        $sql = "SELECT data FROM sessions WHERE id = :id";
        $sth = $conn->prepare($sql);
        $sth->bindValue(':id', $id);

        $sth->execute();
        $consulta = $sth->fetchAll();
        TTransaction::close();

        if (isset($consulta[0][0])) {
            return $consulta[0][0];
        } else {
            return "";
        }
    }

    public function write(string $id, string $data): bool
    {
        TTransaction::open('sample');
        $conn = TTransaction::get();
        $access = time();
        $sql = "REPLACE INTO sessions VALUES (:id, :access, :data)";
        $sth = $conn->prepare($sql);
        $sth->bindValue(':id', $id);
        $sth->bindValue(':access', $access);
        $sth->bindValue(':data', $data);
        $consulta = $sth->execute();

        TTransaction::close();

        if ($consulta) {
            return true;
        }
        return false;
    }

    public function destroy(string $id): bool
    {

        TTransaction::open('sample');
        $conn = TTransaction::get();
        $sql = "DELETE FROM sessions WHERE id =  :id";
        $sth = $conn->prepare($sql);
        $sth->bindValue(':id', $id);
        $consulta = $sth->execute();
        TTransaction::close();
        if ($consulta) {
            return true;
        }

        // Return False  
        return false;
    }

    public function gc(int $maxlifetime): int | false
    {
        $old = time() - $maxlifetime;

        $sql = "DELETE FROM sessions WHERE access < :old";


        TTransaction::open('sample');
        $conn = TTransaction::get();
        $sth = $conn->prepare($sql);
        $sth->bindValue(':old', $old);
        $consulta = $sth->execute();
        TTransaction::close();

        if ($consulta) {
            return true;
        }

        return false;
    }
}
