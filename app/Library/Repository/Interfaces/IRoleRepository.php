<?php
/**
 * Created by SREEJ32H.
 * User: iocod
 * Date: 3/11/17
 * Time: 10:32 AM.
 */

namespace App\Library\Repository\Interfaces;

interface IRoleRepository
{
    public function allInvestors();

    public function countInvestors();

    public function allAdminUsers();

    public function allBranchManager();

    public function allCollectionUser();

    public function allSubAdmin();

    public function allUsers();

    public function lenderReport($lenders, $industries, $merchants);
}
