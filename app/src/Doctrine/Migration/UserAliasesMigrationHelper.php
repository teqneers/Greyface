<?php

namespace App\Doctrine\Migration;

use Ramsey\Uuid\Uuid;

/**
 * Trait UserAliasesMigrationHelper
 *
 * @package App\Doctrine\Migration
 */
trait UserAliasesMigrationHelper
{

    /**
     * @param array $alias
     */
    private function addUserAlias(array $alias): void
    {

        $this->addSql(
            <<<'SQL'
INSERT INTO tq_aliases(id, user_id, alias_name)
VALUES (:id, :user_id, :alias_name)
SQL
            ,
            [
                'id' => (string)Uuid::uuid4(),
                'user_id' => $alias['user_id'],
                'alias_name' => $alias['alias_name']
            ],
            [
                'id' => 'string',
                'user_id' => 'string',
                'alias_name' => 'string'
            ]
        );
    }

    /**
     * @param array $aliases
     */
    private function addUserAliases(array $aliases): void
    {
        foreach ($aliases as $alias) {
            $this->addUserAlias($alias);
        }
    }

}
