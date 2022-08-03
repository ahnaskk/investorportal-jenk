<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $guarded = [];
    protected $table = 'settings';
    const Revert_CurrentDate = 'current_date';
    const Revert_PaymentDate = 'payment_date';
    public static function TableView($data)
    {
        echo "<table border='1'>";
        echo '<thead>';
        if (isset($data[0])) {
            echo '<tr>';
            foreach ($data[0] as $key => $value) {
                echo "<td>$key</td>";
            }
            echo '</tr>';
        } else {
            foreach ($data as $key => $single) {
                echo '<tr>';
                foreach ($single as $key => $value) {
                    echo "<td>$key</td>";
                }
                echo '</tr>';
                break;
            }
        }
        echo '</thead>';
        echo '<tbody>';
        foreach ($data as $single) {
            echo '<tr>';
            foreach ((array) $single as $key => $value) {
                echo "<td align='right'>".$single[$key] ?? $single->key.'</td>';
            }
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    }
}
