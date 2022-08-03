<?php
/**
 * Created by SREEJ32H.
 * User: iocod
 * Date: 3/05/18
 * Time: 4:40 PM.
 */

namespace App\Library\Transformer;

class DefaultTransactionTransformer extends TransformerAbstract
{
    public function transformModel($default)
    {
        return [
            'User'                   => $default->name,
            'Net Zero'                   => $default->net_zero,
            'Default Invested Amount' => $default->default_amount,
            'Default RTR Amount' => $default->collection_amount,
            // 'Default Invested Rate' => round($default->default_amount/$investor_arr[$default->id]*100,10)."%",

        ];
    }
}
