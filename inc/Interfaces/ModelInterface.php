<?php
namespace NewsParserPlugin\Interfaces;

/**
 * Interface for Models class.
 *
 *
 * @package  Interfaces
 * @author   Evgeniy S.Zalevskiy <2600@ukr.net>
 * @license  MIT
 */
interface ModelInterface
{
    public function create($id,$options);
    public function update($id,$options);
    public function findByID($id);
}
