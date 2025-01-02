<?php
namespace BulkGate\Extensions\Hook;

use BulkGate\Extensions\Database;

/**
 * @author Lukáš Piják 2017 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
interface IExtension
{
    /**
     * @param Database\IDatabase $database
     * @param Variables $variables
     * @return void
     */
    public function extend(Database\IDatabase $database, Variables $variables);
}
