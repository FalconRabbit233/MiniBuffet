<?php


namespace MiniBuffet\Service;


use Illuminate\Database\Capsule\Manager;
use MiniBuffet\Exception\EntityNotFoundException;
use MiniBuffet\ServiceBase;

class TableService extends ServiceBase
{
    /**
     * @param int $id
     * @return mixed
     * @throws EntityNotFoundException
     */
    public function findTableById($id)
    {
       $table = Manager::table('besitzen')
            ->where('Tisch', '=', $id)
            ->first();

        if (!$table) {
            throw new EntityNotFoundException("Tisch($id)");
        }

        return $table;
    }
}
