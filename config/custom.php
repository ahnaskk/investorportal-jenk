<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Merchant Advance Types
    |--------------------------------------------------------------------------
    |
    | Merchant Advance Types
    |
    */
    'advance_types' => [
        'daily_ach'         => 'Daily ACH',
        'weekly_ach'        => 'Weekly ACH',
        'credit_card_split' => 'Credit Card Split',
        'variable_ach'      => 'Variable ACH',
        'lock_box'          => 'Lock Box',
        'hybrid'            => 'Hybrid',
        'biweekly_ach'      => 'Bi Weekly ACH',
        'monthly_ach'       => 'Monthly ACH',
    ],
    /*
    |--------------------------------------------------------------------------
    | Merchant Advance Types for ACH
    |--------------------------------------------------------------------------
    |
    | Merchant Advance Types for ACH
    |
    */
    'ach_advance_types' => [
        'daily_ach'         => 'Daily ACH',
        'weekly_ach'        => 'Weekly ACH',
        'biweekly_ach'      => 'Bi Weekly ACH',
        'monthly_ach'       => 'Monthly ACH',
    ],

    /*
    |--------------------------------------------------------------------------
    | Holidays
    |--------------------------------------------------------------------------
    | Holiday dates are stored here.
    | Add date in yyyy-mm-dd format,if values are sigle digits add precceding zero.
    */
    'holidays' => [
        '2019-01-01'=>'New Year Day',
        '2019-01-21'=>'Martin Luther King, Jr. Day',
        '2019-02-18'=>'George Washington’s Birthday',
        '2019-05-27'=>'Memorial Day',
        '2019-07-04'=>'Independence Day',
        '2019-09-02'=>'Labor day',
        '2019-10-14'=>'Columbus Day',
        '2019-11-11'=>'Veterans Days',
        '2019-11-28'=>'Thanksgiving Day',
        '2019-12-25'=>'Christmas Day',

        '2020-01-01'=>'New Year Day',
        '2020-01-20'=>'Martin Luther King, Jr. Day',
        '2020-02-17'=>'George Washington’s Birthday',
        '2020-05-25'=>'Memorial Day',
        '2020-07-03'=>'Independence Day',
        '2020-09-07'=>'Labor day',
        '2020-10-12'=>'Columbus Day',
        '2020-11-11'=>'Veterans Days',
        '2020-11-26'=>'Thanksgiving Day',
        '2020-12-25'=>'Christmas Day',

        '2021-01-01'=>'New Year Day',
        '2021-01-18'=>'Martin Luther King, Jr. Day',
        '2021-02-15'=>'George Washington’s Birthday',
        '2021-05-31'=>'Memorial Day',
        '2021-07-05'=>'Independence Day',
        '2021-09-06'=>'Labor day',
        '2021-10-11'=>'Columbus Day',
        '2021-11-11'=>'Veterans Days',
        '2021-11-25'=>'Thanksgiving Day',
        '2021-12-25'=>'Christmas Day',

        '2022-01-01'=>'New Year Day',
        '2022-01-17'=>'Martin Luther King, Jr. Day',
        '2022-02-21'=>'George Washington’s Birthday',
        '2022-05-30'=>'Memorial Day',
        '2022-07-04'=>'Independence Day',
        '2022-09-05'=>'Labor day',
        '2022-10-10'=>'Columbus Day',
        '2022-11-11'=>'Veterans Days',
        '2022-11-24'=>'Thanksgiving Day',
        '2022-12-26'=>'Christmas Day',

        '2023-01-02'=>'New Year Day',
        '2023-01-16'=>'Martin Luther King, Jr. Day',
        '2023-02-20'=>'George Washington’s Birthday',
        '2023-05-29'=>'Memorial Day',
        '2023-07-04'=>'Independence Day',
        '2023-09-04'=>'Labor day',
        '2023-10-09'=>'Columbus Day',
        '2023-11-10'=>'Veterans Days',
        '2023-11-23'=>'Thanksgiving Day',
        '2023-12-25'=>'Christmas Day',

        '2024-01-01'=>'New Year Day',
        '2024-01-15'=>'Martin Luther King, Jr. Day',
        '2024-02-19'=>'George Washington’s Birthday',
        '2024-05-27'=>'Memorial Day',
        '2024-07-04'=>'Independence Day',
        '2024-09-02'=>'Labor day',
        '2024-10-14'=>'Columbus Day',
        '2024-11-11'=>'Veterans Days',
        '2024-11-28'=>'Thanksgiving Day',
        '2024-12-25'=>'Christmas Day',

        '2025-01-01'=>'New Year Day',
        '2025-01-20'=>'Martin Luther King, Jr. Day',
        '2025-02-17'=>'George Washington’s Birthday',
        '2025-05-26'=>'Memorial Day',
        '2025-07-04'=>'Independence Day',
        '2025-09-01'=>'Labor day',
        '2025-10-13'=>'Columbus Day',
        '2025-11-11'=>'Veterans Days',
        '2025-11-27'=>'Thanksgiving Day',
        '2025-12-25'=>'Christmas Day',

    ],

    /*
    |--------------------------------------------------------------------------
    | Unwanted Merchant Sub stauses
    |--------------------------------------------------------------------------
    |
    */
    'unwanted_sub_status' => [
        4,
        18,
        19,
        20,
        22,
        2,
        10,
        11,
        17,
    ],
    /*
    |--------------------------------------------------------------------------
    | Merchant Sub stauses for ACH Filter
    |--------------------------------------------------------------------------
    |except 2,10,11,17
    */
    'ach_sub_status' => [
        1,
        5,
        12,
        13,
        15,
        16,
    ],
    /*
    |--------------------------------------------------------------------------
    | Merchant Unwanted Sub stauses for ACH Credit
    |--------------------------------------------------------------------------
    |except 2,10,11,17
    */
    'unwanted_sub_status_merchant_debit' => [
        4,
        18,
        19,
        20,
        22,
    ],
    /*
    |--------------------------------------------------------------------------
    | ACH Fee types
    |--------------------------------------------------------------------------
    |
    */
    'ach_fee_types' => [
        'ach_rejection' => 'ACH Rejection',
        'nsf' => 'NSF',
        'bank_change' => 'Bank Change',
        'blocked_account' => 'Blocked Account',
        'ach_fee' => 'ACH Fee',
        'default_fee' => 'Default fee',
    ],
    /*
    |--------------------------------------------------------------------------
    | Mail Log Types
    |--------------------------------------------------------------------------
    |
    */
    'mail_log_types' => [
        1 => 'Reconciliation Mail',
        2 => 'Rcode Mail',
        3 => 'Credit Card Payment Mail',
        4 => 'Marketing Offer Mail'
    ],

];
