<?php
namespace BulkGate\Extensions\IO;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
interface IConnection
{
    /**
     * @param Request $request
     * @return Response
     */
    public function run(Request $request);
}
