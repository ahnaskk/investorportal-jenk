<?php

namespace App\Library\Repository\Interfaces;

interface ISubStatusFlagRepository
{
    public function getAll();
    public function index();
    public function rowData($data);

}
