<?php
/**
 * Created by SREEJ32H.
 * User: iocod
 * Date: 5/11/17
 * Time: 12:15 AM.
 */

namespace App\Library\Repository\Interfaces;

interface ISubStatusRepository
{
    public function index();
    public function rowData($data);
    public function getAll();
}
