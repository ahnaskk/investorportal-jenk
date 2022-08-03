<?php

namespace App\Providers;

use App\AchRequest;
use App\Bank;
use App\CompanyAmount;
use App\Faq;
use App\InvestorTransaction;
use App\Jobs\UserActivityLogJob;
use App\MarketpalceInvestors;
use App\Merchant;
use App\MerchantBankAccount;
use App\MerchantPaymentTerm;
use App\MerchantUser;
use App\MNotes;
use App\Models\InvestorAchRequest;
use App\ParticipentPayment;
use App\PaymentPause;
use App\TermPaymentDate;
use App\User;
use App\UserActivityLog;
use App\UserDetails;
use App\VelocityFee;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class UserActivityLogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->userLog();
        $this->merchantLog();
        $this->paymentLog();
        $this->investorPayment();
        $this->achLog();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    private function userLog()
    {
        User::created(function ($modelInstance) {
            $type = 'user';
            if (Session::has('user_role')) {
                $type = Session::get('user_role');
            }
            $this->activityLog($modelInstance, $type, 'created');
            Session::forget('user_role');
        });

        User::updated(function ($modelInstance) {
            if ($modelInstance->hasRole('investor')) {
                $this->modelUpdateElements($modelInstance, 'investor');
            } elseif ($modelInstance->hasRole('merchant')) {
                $this->modelUpdateElements($modelInstance, 'user_merchant');
            } elseif ($modelInstance->hasRole('lender')) {
                $this->modelUpdateElements($modelInstance, 'lender');
            } elseif ($modelInstance->hasRole('company')) {
                $this->modelUpdateElements($modelInstance, 'company');
            } else {
                $this->modelUpdateElements($modelInstance, 'user');
            }
        });

        User::deleted(function ($modelInstance) {
            if ($modelInstance->hasRole('investor')) {
                $this->activityLog($modelInstance, 'investor', 'deleted');
            } elseif ($modelInstance->hasRole('lender')) {
                $this->activityLog($modelInstance, 'lender', 'deleted');
            } elseif ($modelInstance->hasRole('company')) {
                $this->activityLog($modelInstance, 'company', 'deleted');
            } else {
                $details['id'] = $modelInstance->id;
                $details['name'] = $modelInstance->name;
                $details['email'] = $modelInstance->email;
                $details['notification_email'] = $modelInstance->notification_email;
                $this->activityLog($modelInstance, 'user', 'deleted', $details);
            }
        });

        UserDetails::created(function ($modelInstance) {
            if (isset($modelInstance->liquidity) && $modelInstance->liquidity) {
                $this->activityLog($modelInstance, 'user_details', 'created');
            }
        });
        UserDetails::updated(function ($modelInstance) {
            $this->modelUpdateElements($modelInstance, 'user_details');
        });
        UserDetails::deleted(function ($modelInstance) {
            if (isset($modelInstance->liquidity) && $modelInstance->liquidity) {
                $this->activityLog($modelInstance, 'user_details', 'deleted');
            }
        });
        Bank::created(function ($modelInstance) {
            $this->activityLog($modelInstance, 'bank_account', 'created');
        });
        Bank::updated(function($modelInstance) {
            $this->modelUpdateElements($modelInstance, 'bank_account');
        });
        Bank::deleted(function ($modelInstance) {
            $this->activityLog($modelInstance, 'bank_account', 'deleted');
        });
        Faq::created(function ($modelInstance) {
            $details['title'] = $modelInstance->title;
            $details['web/app'] = ($modelInstance->app == 1) ? 'App' : 'Web';
            $details['link'] = $modelInstance->link;
            $details['description'] = $modelInstance->description;
            $details['created_at'] = $modelInstance->created_at;
            $this->activityLog($modelInstance, 'faq', 'created', $details);
        });
        Faq::updated(function ($modelInstance) {
            $this->modelUpdateElements($modelInstance, 'faq');
        });
        Faq::deleted(function ($modelInstance) {
            $details['title'] = $modelInstance->title;
            $details['web/app'] = ($modelInstance->app == 1) ? 'App' : 'Web';
            $details['link'] = $modelInstance->link;
            $details['description'] = $modelInstance->description;
            $details['created_at'] = $modelInstance->created_at;
            $this->activityLog($modelInstance, 'faq', 'deleted', $details);
        });
    }

    private function investorPayment()
    {
        InvestorTransaction::created(function ($modelInstance) {
            $this->activityLog($modelInstance, 'investor_transaction', 'created');
        });
        InvestorTransaction::updated(function ($modelInstance) {
            $this->modelUpdateElements($modelInstance, 'investor_transaction');
        });
        InvestorTransaction::deleted(function ($modelInstance) {
            $this->activityLog($modelInstance, 'investor_transaction', 'deleted');
        });
    }

    private function merchantLog()
    {
        Merchant::created(function ($modelInstance) {
            $this->activityLog($modelInstance, 'merchant', 'created');
        });

        Merchant::updated(function ($modelInstance) {
            $this->modelUpdateElements($modelInstance, 'merchant');
        });

        Merchant::deleted(function ($modelInstance) {
            $this->activityLog($modelInstance, 'merchant', 'deleted');
        });

        MerchantUser::created(function ($modelInstance) {
            if ($modelInstance->amount != 0) {
                $this->activityLog($modelInstance, 'merchant_user', 'created');
            }
        });
        MerchantUser::updated(function ($modelInstance) {
            if ($modelInstance->amount != 0) {
                $this->modelUpdateElements($modelInstance, 'merchant_user');
            }
        });
        MerchantUser::deleted(function ($modelInstance) {
            if ($modelInstance->amount != 0) {
                $this->activityLog($modelInstance, 'merchant_user', 'deleted');
            }
        });

        MarketpalceInvestors::created(function ($modelInstance) {
            if ($modelInstance->amount != 0) {
                $this->activityLog($modelInstance, 'merchant_user', 'created');
            }
        });
        MarketpalceInvestors::updated(function ($modelInstance) {
            if ($modelInstance->amount != 0) {
                $this->modelUpdateElements($modelInstance, 'merchant_user');
            }
        });
        MarketpalceInvestors::deleted(function ($modelInstance) {
            if ($modelInstance->amount != 0) {
                $this->activityLog($modelInstance, 'merchant_user', 'deleted');
            }
        });

        /**
         * Company Amount is related to merchant
         */
        CompanyAmount::created(function ($modelInstance) {
            $this->activityLog($modelInstance, 'company_amount', 'created');
        });

        CompanyAmount::updated(function ($modelInstance) {
            $this->modelUpdateElements($modelInstance, 'company_amount');
        });

        CompanyAmount::deleted(function ($modelInstance) {
            $this->modelUpdateElements($modelInstance, 'company_amount', 'deleted');
        });

        MNotes::created(function ($modelInstance) {
            $this->activityLog($modelInstance, 'merchant_note', 'created');
        });
        // Bank details are related to merchant
        MerchantBankAccount::created(function ($modelInstance) {
            $this->activityLog($modelInstance, 'merchant_bank_account', 'created');
        });
        MerchantBankAccount::updated(function ($modelInstance) {
            $this->modelUpdateElements($modelInstance, 'merchant_bank_account');
        });
        MerchantBankAccount::deleted(function ($modelInstance) {
            $this->activityLog($modelInstance, 'merchant_bank_account', 'deleted');
        });
    }

    private function paymentLog()
    {
        ParticipentPayment::created(function ($modelInstance) {
            $this->activityLog($modelInstance, 'payment', 'created');
        });
        ParticipentPayment::updated(function ($modelInstance) {
            $this->modelUpdateElements($modelInstance, 'payment');
        });
        ParticipentPayment::deleted(function ($modelInstance) {
            $this->activityLog($modelInstance, 'payment', 'deleted');
        });
    }

    public function achLog()
    {
        // ACH terms are related to merchant
        MerchantPaymentTerm::created(function ($modelInstance) {
            $this->activityLog($modelInstance, 'merchant_ach_term', 'created');
        });

        MerchantPaymentTerm::updated(function ($modelInstance) {
            $this->modelUpdateElements($modelInstance, 'merchant_ach_term');
        });

        MerchantPaymentTerm::deleted(function ($modelInstance) {
            $this->activityLog($modelInstance, 'merchant_ach_term', 'deleted');
        });
        TermPaymentDate::created(function ($modelInstance) {
            $this->activityLog($modelInstance, 'ach_payment', 'created');
        });

        TermPaymentDate::updated(function ($modelInstance) {
            $this->modelUpdateElements($modelInstance, 'ach_payment');
        });

        TermPaymentDate::deleted(function ($modelInstance) {
            $this->activityLog($modelInstance, 'ach_payment', 'deleted');
        });

        PaymentPause::created(function ($modelInstance) {
            $this->activityLog($modelInstance, 'payment_pause', 'created');
        });

        PaymentPause::updated(function ($modelInstance) {
            $this->activityLog($modelInstance, 'payment_resume', 'created');
        });

        PaymentPause::deleted(function ($modelInstance) {
            $this->activityLog($modelInstance, 'payment_pause', 'deleted');
        });

        AchRequest::created(function ($modelInstance) {
            $this->activityLog($modelInstance, 'ach_request', 'created');
        });

        AchRequest::updated(function ($modelInstance) {
            $this->modelUpdateElements($modelInstance, 'ach_request');
        });

        AchRequest::deleted(function ($modelInstance) {
            $this->activityLog($modelInstance, 'ach_request', 'deleted');
        });

        VelocityFee::created(function ($modelInstance) {
            $this->activityLog($modelInstance, 'velocity_fee', 'created');
        });

        VelocityFee::updated(function ($modelInstance) {
            $this->modelUpdateElements($modelInstance, 'velocity_fee');
        });

        VelocityFee::deleted(function ($modelInstance) {
            $this->activityLog($modelInstance, 'velocity_fee', 'deleted');
        });

        InvestorAchRequest::created(function ($modelInstance) {
            $this->activityLog($modelInstance, 'investor_ach_request', 'created');
        });

        InvestorAchRequest::updated(function ($modelInstance) {
            $this->modelUpdateElements($modelInstance, 'investor_ach_request');
        });

        InvestorAchRequest::deleted(function ($modelInstance) {
            $this->activityLog($modelInstance, 'investor_ach_request', 'deleted');
        });
    }

    public static function modelUpdateElements($modelInstance, $type = 'user')
    {
        $changedElements = $modelInstance->getDirty();

        if (count($changedElements) > 0) {
            $detail = [];
            $delete = false;
            foreach ($changedElements as $changedElement => $newValue) {
                $previousValue = $modelInstance->getOriginal($changedElement);
                /**
                 * If Statuses are using for the deleting purpose not using Laravel Soft Deletes
                 */
                if (1 == 2) {
                    if ($changedElement == 'status' and $newValue == 2) {
                        $delete = true;
                        $this->activityLog($modelInstance, $type, 'deleted');
                    }

                    if (($changedElement == 'status' and $newValue == 2)) {
                        continue;
                    }
                }
                $avoid = [
                    'current_edit_user', 'creator_id', 'creator', 'source_from', 'remember_token',
                    'merchant_permission', 'two_factor_secret', 'two_factor_recovery_codes', 'revert_id', 'model_id',
                    'timezone', 'date_format', 'annualized_rate', 'last_rcode', 'merchant_id', 'id', 'user_id', 'company_id',
                    'last_status_updated_date', 'term_id', 'is_fees', 'ach_request_id', 'fee_type', 'model', 'auth_code',
                    'source_from', 'revert_id', 'position', 'annual_revenue', 'logo', 'liquidity_exclude', 'current_merchant_id', 
                    'final_participant_share', 'old_factor_rate', 'paid_count', 'last_rcode', 'auto_invest', 'paid_participant_ishare', 'actual_paid_participant_ishare',
                    'merchant_id', 'updated_at', 'deleted_at', 'activation'
                ];
                if ($type != 'company') {
                    array_push($avoid, 'company_status');
                }
                if ($type == 'investor_ach_request') {
                    array_push($avoid, 'investor', 'transaction_id');
                }
                if ($type == 'payment') {
                    array_push($avoid, 'amount', 'transaction_type', 'investor_ids');
                }
                if ($type == 'user' || $type == 'investor') {
                    array_push($avoid, 'underwriting_status');
                }
                if ($type == 'velocity_fee') {
                    array_push($avoid, 'order_id');
                }
                if ($type == 'bank_account') {
                    array_push($avoid, 'investor');
                }
                if ($type == 'merchant') {
                    array_push($avoid, 'liquidity', 'payment_pause_id', 'actual_payment_left');
                }
                if (in_array($changedElement, $avoid)) {
                    continue;
                }
                if (($previousValue != '' && $newValue != '' ) && ($previousValue == $newValue)) {
                    continue;
                }
                if ($type == 'payment') {
                    $avoid = ['model_id'];
                    if (in_array($changedElement, $avoid)) {
                        continue;
                    }
                }
                if ($type == 'user') {
                    $avoid = ['timezone', 'date_format'];
                    if (in_array($changedElement, $avoid)) {
                        continue;
                    }
                }
                if ($type == 'ach_request' || $type == 'investor_ach_request') {
                    if ($changedElement == 'response') {
                        continue;
                    }
                }

                if (($previousValue == '' and $newValue == '') || ($previousValue == 'null' and $newValue == 'null')) {
                    continue;
                }
                if (is_string($previousValue) && is_string($newValue) && (strtolower(trim($previousValue)) == strtolower(trim($newValue))))
                {
                    continue;
                }
                $dollar_fields = ['amount', 'payment', 'commission_amount', 'funded', 'payment_amount', 'rtr', 'liquidity', 'under_writing_fee', 'credit_score', 'up_sell_commission', 'invest_rtr'];
                if (in_array($changedElement, $dollar_fields)) {
                    $fromValue = \FFM::dollar($previousValue);
                    $toValue = \FFM::dollar($newValue);
                    if ($fromValue == $toValue) { continue; }
                }
                $percentageFields = ['commission', 'm_mgmnt_fee', 'management_fee', 'm_syndication_fee', 'syndication_fee', 'underwriting_fee', 'experian_intelliscore', 'experian_financial_score', 'origination_fee', 'commission_per', 'syndication_fee_percentage', 'mgmnt_fee', 'brokerage', 'complete_per', 'max_participant_fund_per', 'under_writing_fee_per', 'interest_rate', 'withhold_percentage', 'up_sell_commission_per', 'complete_percentage', 'agent_fee_percentage', 'global_syndication'];
                if (in_array($changedElement, $percentageFields)) {
                    $fromValue = ($previousValue != '') ? \FFM::percent($previousValue) : '';
                    $toValue = ($newValue != '') ? \FFM::percent($newValue) : '';
                    if ($fromValue == $toValue) { continue; }
                }
                if ($type == 'merchant') { //saving Name instead of ID in merchant updation.
                    $detail['merchant_id'] = $modelInstance->id;
                    if ($changedElement == 'sub_status_flag') {
                        $previousValue = self::getName('sub_status_flags', $previousValue, 'name');
                        $newValue = self::getName('sub_status_flags', $newValue, 'name');
                    } elseif ($changedElement == 'advance_type') {
                        $advance_type = Merchant::getAdvanceTypes();
                        if ($previousValue != '' && array_key_exists($previousValue, $advance_type)) {
                            $previousValue = $advance_type[$previousValue];
                        }
                        if ($newValue != '' && array_key_exists($newValue, $advance_type)) {
                            $newValue = $advance_type[$newValue];
                        }
                    } elseif ($changedElement == 'marketplace_status' || $changedElement == 'notify_investors' || $changedElement == 'ach_pull') {
                        if ($previousValue == 1) {
                            $previousValue = 'Yes';
                        } elseif (!$previousValue) {
                            $previousValue = 'No';
                        }
                        if ($newValue == 1) {
                            $newValue = 'Yes';
                        } elseif (!$newValue) {
                            $newValue = 'No';
                        }
                    } elseif (
                        $changedElement == 'sub_status_id' || $changedElement == 'industry_id' ||
                        $changedElement == 'source_id' || $changedElement == 'state_id' ||
                        $changedElement == 'lender_id' || $changedElement == 'label' ||
                        $changedElement == 'underwriting_status' || $changedElement == 'payment_pause_id' ||
                        $changedElement == 'creator_id'
                        ) {
                        if ($changedElement == 'sub_status_id') {
                            $previousValue = self::getName('sub_statuses', $previousValue, 'name');
                            $newValue = self::getName('sub_statuses', $newValue, 'name');
                        } elseif ($changedElement == 'industry_id') {
                            $previousValue = self::getName('industries', $previousValue, 'name');
                            $newValue = self::getName('industries', $newValue, 'name');
                        } elseif ($changedElement == 'source_id') {
                            $previousValue = self::getName('merchant_source', $previousValue, 'name');
                            $newValue = self::getName('merchant_source', $newValue, 'name');
                        } elseif ($changedElement == 'state_id') {
                            $previousValue = self::getName('us_states', $previousValue, 'state');
                            $newValue = self::getName('us_states', $newValue, 'state');
                        } elseif ($changedElement == 'lender_id') {
                            $data = Role::whereName('lender')->first()->users->whereIn('id', [$previousValue, $newValue])->where('active_status', 1);
                            $previousValue = ($prev = collect($data)->firstWhere('id', $previousValue)) ? $prev->name : '';
                            $newValue = ($new = collect($data)->firstWhere('id', $newValue)) ? $new->name : '';
                        } elseif ($changedElement == 'label') {
                            $previousValue = self::getName('label', $previousValue, 'name');
                            $newValue = self::getName('label', $newValue, 'name');
                        } elseif ($changedElement == 'underwriting_status') {
                            $previousValue = json_decode($previousValue, true);
                            $newValue = json_decode($newValue, true);
                            $data = Role::whereName('company')->first()->users->where('company_status',1)->pluck('name', 'id')->toArray();
                            array_unshift($data, '');
                            unset($data[0]);
                            $prev = [];
                            $new = [];
                            foreach ($data as $k => $d) {
                                if ($previousValue && in_array($k, $previousValue)) {
                                    $prev[] = $d;
                                }
                                if ($newValue && in_array($k, $newValue)) {
                                    $new[] = $d;
                                }
                            }
                            $previousValue = implode(',', $prev);
                            $newValue = implode(',', $new);
                        } elseif ($changedElement == 'payment_pause_id') {
                            $previousValue = self::getName('payment_pauses', $previousValue, 'paused_by');
                            $newValue = self::getName('payment_pauses', $newValue, 'paused_by');
                        } elseif ($changedElement == 'creator_id') {
                            $previousValue = self::getName('users', $previousValue, 'name');
                            $newValue = self::getName('users', $newValue, 'name');
                        } elseif ($changedElement == 'money_request_status') {
                            $previousValue = ($previousValue == 1) ? 'Yes' : 'No';
                            $newValue = ($newValue == 1) ? 'Yes' : 'No';
                        }
                        $changedElement = str_replace('_id', '', $changedElement);
                    } elseif ($changedElement == 'm_s_prepaid_status') {
                        $changedElement = 'merchant_syndication_prepaid_status';
                        if ($previousValue == 1) {
                            $previousValue = 'On RTR';
                        } elseif ($previousValue == 2) {
                            $previousValue = 'On Funding Amount';
                        } elseif ($previousValue == 0) {
                            $previousValue = '';
                        }
                        if ($newValue == 1) {
                            $newValue = 'On RTR';
                        } elseif ($newValue == 2) {
                            $newValue = 'On Funding Amount';
                        } elseif ($newValue == 0) {
                            $newValue = '';
                        }
                    } elseif ($changedElement == 'last_rcode') {
                        if ($previousValue) {
                            $previousValue = self::getName('rcode', $previousValue, 'code').' - '.self::getName('rcode', $previousValue, 'description');
                        }
                        if ($newValue) {
                            $newValue = self::getName('rcode', $newValue, 'code').' - '.self::getName('rcode', $newValue, 'description');
                        }
                    } elseif ($changedElement == 'agent_fee_applied') {
                        $previousValue = ($previousValue == 0) ? 'No' : 'Yes';
                        $newValue = ($newValue == 0) ? 'No' : 'Yes';
                    } elseif ($changedElement == 'm_mgmnt_fee') {
                        $changedElement = 'management_fee';
                    }
                    if ($changedElement == 'up_sell_commission' || $changedElement == 'credit_score') {
                        $previousValue = number_format((float) $previousValue, 2);
                        $newValue = number_format((float) $newValue, 2);
                    }
                } elseif ($type == 'user' || $type == 'investor' || $type == 'lender') { // saving name instead of ID in investor updation.
                    if ($changedElement == 'investor_type') {
                        $investor_types = User::getInvestorType();
                        $previousValue = ($previousValue) ? $investor_types[$previousValue] : '';
                        $newValue = ($newValue) ? $investor_types[$newValue] : '';
                    } elseif ($changedElement == 's_prepaid_status') {
                        if ($previousValue == 0) {
                            $previousValue = '';
                        } elseif ($previousValue == 1) {
                            $previousValue = 'On RTR';
                        } elseif ($previousValue == 2) {
                            $previousValue = 'On Funding Amount';
                        }
                        if ($newValue == 0) {
                            $newValue = '';
                        } elseif ($newValue == 1) {
                            $newValue = 'On RTR';
                        } elseif ($newValue == 2) {
                            $newValue = 'On Funding Amount';
                        }
                    } elseif ($changedElement == 'notification_recurence') {
                        $recurrence_types = [1 => 'Weekly', 2 => 'Monthly', 3=>'Daily', 4=>'On Demand'];
                        $previousValue = ($previousValue) ? $recurrence_types[$previousValue] : '';
                        $newValue = ($newValue) ? $recurrence_types[$newValue] : '';
                    } elseif ($changedElement == 'file_type') {
                        $previousValue = ($previousValue == 1) ? 'PDF' : 'CSV';
                        $newValue = ($newValue == 1) ? 'PDF' : 'CSV';
                    } elseif ($changedElement == 'company') {
                        $data = Role::whereName('company')->first()->users->where('company_status',1)->pluck('name', 'id')->toArray();
                        $previousValue = ($previousValue) ? $data[$previousValue] : '';
                        $newValue = ($newValue) ? $data[$newValue] : '';
                    } elseif ($changedElement == 'global_syndication') {
                        $changedElement = 'syndication_fee';
                    } elseif ($changedElement == 'active_status' || $changedElement == 'funding_status') {
                        $previousValue = ($previousValue == 1) ? 'ON' : 'OFF';
                        $newValue = ($newValue == 1) ? 'ON' : 'OFF';
                    } elseif ($changedElement == 'display_value') {
                        $previousValue = ($previousValue == 'mid') ? 'Merchant ID' : 'Name';
                        $newValue = ($newValue == 'mid') ? 'Merchant ID' : 'Name';
                    } elseif (
                        $changedElement == 'whole_portfolio' ||
                        $changedElement == 'auto_generation' ||
                        $changedElement == 'auto_syndicate_payment' ||
                        $changedElement == 'auto_invest'
                        ) {
                            if ($previousValue == 1) {
                                $previousValue = 'Yes';
                            } elseif ($previousValue == 0) {
                                $previousValue = 'No';
                            } else {
                                $previousValue = '';
                            }
                            if ($newValue == 1) {
                                $newValue = 'Yes';
                            } elseif ($newValue == 0) {
                                $newValue = 'No';
                            } else {
                                $newValue = '';
                            }
                    } elseif ($changedElement == 'label') {
                        $previousValue = json_decode($previousValue, true);
                        $newValue = json_decode($newValue, true);
                        $bothValue = array_unique(array_merge((array) $previousValue, (array) $newValue));
                        $prev = [];
                        $new = [];
                        foreach ($bothValue as $b) {
                            if ($previousValue && in_array($b, $previousValue)) {
                                $prev[] = self::getName('label', $b, 'name');
                            }
                            if ($newValue && in_array($b, $newValue)) {
                                $new[] = self::getName('label', $b, 'name');
                            }
                        }
                        $previousValue = implode(',', $prev);
                        $newValue = implode(',', $new);
                    } elseif ($changedElement == 'underwriting_status') {
                        if (gettype($previousValue) == 'string') {
                            $previousValue = str_replace(['[', ']'], '', str_replace('"', '', $previousValue));
                            $previousValue = explode(',', $previousValue);
                        } else {
                            $previousValue = json_decode($previousValue, true);
                        }
                        $data = Role::whereName('company')->first()->users->where('company_status',1)->pluck('name', 'id')->toArray();
                        array_unshift($data, '');
                        unset($data[0]);
                        $prev = [];
                        $new = [];
                        foreach ($data as $k => $d) {
                            if ($previousValue && in_array($k, $previousValue)) {
                                $prev[] = $d;
                            }
                            if ($newValue && in_array($k, $newValue)) {
                                $new[] = $d;
                            }
                        }
                        $previousValue = implode(',', $prev);
                        $newValue = implode(',', $new);
                    }
                    if ($type == 'lender') {
                        if ($changedElement == 'management_fee' || $changedElement == 'underwriting_fee' || $changedElement == 'syndication_fee') {
                            $previousValue = ($previousValue) ? $previousValue : 0;
                            $newValue = ($newValue) ? $newValue : 0;
                        }
                    }
                    $changedElement = str_replace('_id', '', $changedElement);
                } elseif ($type == 'merchant_user') {
                    if (isset($modelInstance->merchant_id)) {
                        $detail['merchant_id'] = $modelInstance->merchant_id;
                    }
                    if (isset($modelInstance->user_id)) {
                        $detail['user_id'] = $modelInstance->user_id;
                    }
                    if ($changedElement == 's_prepaid_status') {
                        if ($previousValue == 0) {
                            $previousValue = '';
                        } elseif ($previousValue == 1) {
                            $previousValue = 'On RTR';
                        } elseif ($previousValue == 2) {
                            $previousValue = 'On Funding Amount';
                        }
                        if ($newValue == 0) {
                            $newValue = '';
                        } elseif ($newValue == 1) {
                            $newValue = 'On RTR';
                        } elseif ($newValue == 2) {
                            $newValue = 'On Funding Amount';
                        }
                    }
                    if ($changedElement == 'status') {
                        $status = ['0' => 'Pending', '1' => 'Approved', '2'=>'Hide', '3'=>'Re-assigned', '4' => 'Rejected'];
                        $previousValue = ($previousValue != '') ? $status[$previousValue] : '';
                        $newValue = ($newValue != '') ? $status[$newValue] : '';
                    }
                } elseif ($type == 'payment') {//payment
                    if (isset($modelInstance->payment_date)) {
                        $detail['payment_date'] = $modelInstance->payment_date;
                    }
                    if ($changedElement == 'rcode') {
                        if ($previousValue) {
                            $previousValue = self::getName('rcode', $previousValue, 'code').' - '.self::getName('rcode', $previousValue, 'description');
                        }
                        if ($newValue) {
                            $newValue = self::getName('rcode', $newValue, 'code').' - '.self::getName('rcode', $newValue, 'description');
                        }
                    }
                    if ($changedElement == 'payment_type') {
                        if (isset($modelInstance->model_id) && isset($modelInstance->mode_of_payment) && $modelInstance->mode_of_payment != 3) {
                            if ($previousValue == 2) {
                                $previousValue = 'Credit';
                            } elseif ($previousValue == 1) {
                                $previousValue = 'Debit';
                            }
                            if ($newValue == 2) {
                                $newValue = 'Credit';
                            } elseif ($newValue == 1) {
                                $newValue = 'Debit';
                            }
                        } else {
                            if ($previousValue == 1) {
                                $previousValue = 'Credit';
                            } elseif ($previousValue == 2) {
                                $previousValue = 'Debit';
                            }
                            if ($newValue == 1) {
                                $newValue = 'Credit';
                            } elseif ($newValue == 2) {
                                $newValue = 'Debit';
                            }
                        }
                    }
                    if ($changedElement == 'mode_of_payment') {
                        $mode = ParticipentPayment::paymentMethodOptions();
                        $previousValue = ($previousValue != '') ? $mode[$previousValue] : '';
                        $newValue = ($newValue != '') ? $mode[$newValue] : '';
                    }
                    if ($changedElement == 'status') {
                        $detail['amount'] = $modelInstance->payment;
                        $status = ParticipentPayment::statusOptions();
                        $previousValue = ($previousValue != '') ? $status[$previousValue] : '';
                        $newValue = ($newValue != '') ? $status[$newValue] : '';
                    }
                    if ($changedElement == 'investor_ids') {
                        continue;
                    }
                } elseif ($type == 'investor_transaction') {
                    if ($changedElement == 'investor_id') {
                        $previousValue = self::getName('users', $previousValue, 'name');
                        $newValue = self::getName('users', $newValue, 'name');
                    }
                    if ($changedElement == 'transaction_type') {
                        $transaction_types = [1=>'Debit', 2=>'Credit'];
                        $previousValue = $transaction_types[$previousValue];
                        $newValue = $transaction_types[$newValue];
                    }
                    if ($changedElement == 'transaction_method') {
                        $method = InvestorTransaction::transactionMethodOptions();
                        $previousValue = ($previousValue != '') ? $method[$previousValue] : '';
                        $newValue = ($newValue != '') ? $method[$newValue] : '';
                    }
                    if ($changedElement == 'transaction_category') {
                        $transaction_categories = \ITran::getAllOptions();
                        $previousValue = ($previousValue != '') ? $transaction_categories[$previousValue] : '';
                        $newValue = ($newValue != '') ? $transaction_categories[$newValue] : '';
                    }
                    if ($changedElement == 'status') {
                        $statuses = InvestorTransaction::statusOptions();
                        $previousValue = ($previousValue != '') ? $statuses[$previousValue] : '';
                        $newValue = ($newValue != '') ? $statuses[$newValue] : '';
                    }
                } elseif ($type == 'merchant_bank_account') {
                    $detail['merchant_id'] = $modelInstance->merchant_id;
                    if ($changedElement == 'default_credit') {
                        if ($previousValue) {
                            $previousValue = ($previousValue == 1) ? 'Yes' : 'No';
                        }
                        if ($newValue) {
                            $newValue = ($newValue == 1) ? 'Yes' : 'No';
                        }
                    }
                    if ($changedElement == 'default_debit') {
                        if ($previousValue) {
                            $previousValue = ($previousValue == 1) ? 'Yes' : 'No';
                        }
                        if ($newValue) {
                            $newValue = ($newValue == 1) ? 'Yes' : 'No';
                        }
                    }
                } elseif ($type == 'merchant_ach_term') {
                    $detail['merchant_id'] = $modelInstance->merchant_id;
                    if ($changedElement == 'start_at') {
                        if (Arr::exists($detail, 'start_at')) {
                            $detail['start_date'] = $detail['start_at'];
                            unset($detail['start_at']);
                        } else {
                            $changedElement = 'start_date';
                        }
                    }
                    if ($changedElement == 'end_at') {
                        if (Arr::exists($detail, 'end_at')) {
                            $detail['end_date'] = $detail['end_at'];
                            unset($detail['end_at']);
                        } else {
                            $changedElement = 'end_date';
                        }
                    }
                } elseif ($type == 'ach_payment') {
                    $detail['merchant_id'] = $modelInstance->merchant_id;
                    if ($changedElement == 'status') {
                        $term_status = TermPaymentDate::statusOptions();
                        if ($previousValue != '' && Arr::exists($term_status, $previousValue)) {
                            $previousValue = $term_status[$previousValue];
                        }
                        if ($newValue != '' && Arr::exists($term_status, $newValue)) {
                            $newValue = $term_status[$newValue];
                        }
                    }
                } elseif ($type == 'payment_pause') {
                    $detail['merchant_id'] = $modelInstance->merchant_id;
                } elseif ($type == 'ach_request') {
                    $detail['merchant_id'] = $modelInstance->merchant_id;
                    $detail['order_id'] = $modelInstance->order_id;
                    $detail['payment_date'] = $modelInstance->payment_date;
                    $statuses = [0 => 'Processing', 1 => 'Settled', -1 => 'Returned'];
                    if ($changedElement == 'ach_status') {
                        $previousValue = ($previousValue != '') ? $statuses[$previousValue] : '';
                        $newValue = ($newValue != '') ? $statuses[$newValue] : '';
                    }
                    if ($changedElement == 'payment_status') {
                        $previousValue = ($previousValue != '') ? $statuses[$previousValue] : '';
                        $newValue = ($newValue) ? $statuses[$newValue] : '';
                    }
                } elseif ($type == 'velocity_fee') {
                    $detail['merchant_id'] = $modelInstance->merchant_id;
                    $detail['fee_type'] = $modelInstance->fee_type;
                    $detail['order_id'] = $modelInstance->order_id;
                    if ($changedElement == 'status') {
                        $term_status = TermPaymentDate::statusOptions();
                        if ($previousValue != '' && Arr::exists($term_status, $previousValue)) {
                            $previousValue = $term_status[$previousValue];
                        }
                        if ($newValue != '' && Arr::exists($term_status, $newValue)) {
                            $newValue = $term_status[$newValue];
                        }
                    }
                } elseif ($type == 'investor_ach_request') {
                    $detail['investor'] = $modelInstance->investor_id;
                    if ($changedElement == 'transaction_method') {
                        $methods = InvestorAchRequest::transactionMethodOptions();
                        $previousValue = ($previousValue != '') ? $methods[$previousValue] : '';
                        $newValue = ($newValue != '') ? $methods[$newValue] : '';
                    }
                    if ($changedElement == 'transaction_category') {
                        $category = InvestorAchRequest::transactionCategoryOptions();
                        $previousValue = ($previousValue != '') ? $category[$previousValue] : '';
                        $newValue = ($newValue != '') ? $category[$newValue] : '';
                    }
                    if ($changedElement == 'ach_request_status') {
                        $status = InvestorAchRequest::achRequestStatusOptions();
                        $previousValue = ($previousValue != '') ? $status[$previousValue] : '';
                        $newValue = ($newValue != '') ? $status[$newValue] : '';
                    }
                    if ($changedElement == 'ach_status') {
                        $status = InvestorAchRequest::achStatusOptions();
                        $previousValue = ($previousValue != '') ? $status[$previousValue] : '';
                        $newValue = ($newValue != '') ? $status[$newValue] : '';
                    }
                } elseif ($type == 'bank_account') {
                    if (isset($modelInstance->investor_id)) {
                        $detail['investor'] = self::getName('users', $modelInstance->investor_id, 'name');
                    }
                    if ($changedElement == 'acc_number') {
                        $changedElement = 'account_number';
                        $previousValue = ($previousValue != '') ? \FFM::mask_cc($previousValue) : '';
                        $newValue = ($newValue != '') ? \FFM::mask_cc($newValue) : ''; 
                    }
                    if ($changedElement == 'name') {
                        $changedElement = 'bank_name';
                    }
                    if ($changedElement == 'type') {
                        $changedElement = 'bank_type';
                    }
                    if ($changedElement == 'default_debit' || $changedElement == 'default_credit') {
                        if ($previousValue != '') {
                            $previousValue = ($previousValue == 1) ? 'Yes' : 'No';
                        }
                        if ($newValue != '') {
                            $newValue = ($newValue == 1) ? 'Yes' : 'No';
                        }
                    }
                } elseif ($type == 'company') {
                    if ($changedElement == 'merchant_permission') { 
                        $previousValue = ($previousValue == 1) ? 'Yes' : 'No';
                        $newValue = ($newValue == 1) ? 'Yes' : 'No';
                    }
                    if ($changedElement == 'syndicate') {
                        $previousValue = ($previousValue == 1) ? 'Yes' : 'No';
                        $newValue = ($newValue == 1) ? 'Yes' : 'No';
                    }
                    if ($changedElement == 'company_status') {
                        $previousValue = ($previousValue == 1) ? 'ON': 'OFF';
                        $newValue = ($newValue == 1) ? 'ON' : 'OFF';
                    }
                } elseif ($type == 'company_amount') {
                    if (isset($modelInstance->company_id)) {
                        $detail['company'] = self::getName('users', $modelInstance->company_id, 'name');
                    }
                    if ($changedElement == 'max_participant') {
                        $previousValue = ($previousValue <= 0) ? 0 : $previousValue;
                        $newValue = ($newValue <= 0) ? 0 : $newValue;
                    }
                } elseif ($type == 'faq') {
                    $detail['title'] = $modelInstance->title;
                    if ($changedElement == 'app') {
                        $changedElement = 'web/app';
                        $previousValue = ($previousValue == 1) ? 'App' : 'Web';
                        $newValue = ($newValue == 1) ? 'App' : 'Web'; 
                    }
                }

                if ($changedElement == 'underwriting_fee') {
                    $previousValue = ($previousValue) ? $previousValue: 0;
                    $newValue = ($newValue) ? $newValue: 0;
                }

                if ($previousValue == $newValue) {continue;}
                $detail[$changedElement] = [
                    'from'  => $previousValue,
                    'to'    => $newValue,
                ];
            }

            if (count($detail) > 0 and ! $delete) {
                if (is_array($detail)) {
                    $is_array = 0;
                    foreach ($detail as $details) {
                        if (is_array($details)) {
                            $is_array = 1;
                        }
                    }
                }
                if ($is_array) {
                    self::activityLog($modelInstance, $type, 'updated', $detail);
                }
            }
        }
    }

    public static function activityLog($object, $type, $action = 'updated', $detail = [], $creator = null)
    {
        $crm = isset($_REQUEST['PHP_AUTH_USER']) ? $_REQUEST['PHP_AUTH_USER'] : '';

        $crm_user = DB::table('user_has_roles')->where('role_id', User::CRM_ROLE)->select('model_id')->first();

        if (Auth::user() || $crm || $creator) {
            if ($action == 'deleted' or $action == 'created') {
                if (count($detail) <= 0) {
                    $detail = $object->toArray();
                }

                if (Arr::exists($detail, 'creator_id')) {
                    unset($detail['creator_id']);
                }

                if (Arr::exists($detail, 'company_id')) {
                    $detail['company'] = self::getName('users', $detail['company_id'], 'name');
                }

                if (Arr::exists($detail, 'company_id')) {
                    unset($detail['company_id']);
                }

                if (Arr::exists($detail, 'deal_name')) {
                    unset($detail['deal_name']);
                }

                if (Arr::exists($detail, 'mgmnt_fee_percentage')) {
                    unset($detail['mgmnt_fee_percentage']);
                }

                if (Arr::exists($detail, 'open_item')) {
                    unset($detail['open_item']);
                }

                if (Arr::exists($detail, 'requested_time')) {
                    unset($detail['requested_time']);
                }
                if (Arr::exists($detail, 'approved_time')) {
                    unset($detail['approved_time']);
                }

                if (Arr::exists($detail, 'id')) {
                    unset($detail['id']);
                }
                if ($type == 'user' || $type == 'investor' || $type == 'lender') { //Saving Name instead of ID in investor creation
                    $investor_types = User::getInvestorType();
                    $recurrence_types = [1 => 'Weekly', 2 => 'Monthly', 3=>'Daily', 4=>'On Demand'];
                    $company = Role::whereName('company')->first()->users->where('company_status',1)->pluck('name', 'id')->toArray();
                    if (Arr::exists($detail, 'notification_recurence')) {
                        $detail['notification_recurence'] = ($detail['notification_recurence']) ? $recurrence_types[$detail['notification_recurence']] : null;
                    }
                    if (Arr::exists($detail, 'investor_type')) {
                        $detail['investor_type'] = ($detail['investor_type']) ? $investor_types[$detail['investor_type']] : null;
                    }
                    if (Arr::exists($detail, 'file_type')) {
                        if ($detail['file_type'] == 1) {
                            $detail['file_type'] = 'PDF';
                        } elseif ($detail['file_type'] == 2) {
                            $detail['file_type'] = 'CSV';
                        }
                    }
                    if (Arr::exists($detail, 'company') && isset($detail['company'])) {
                        $detail['company'] = $company[$detail['company']];
                    }
                    if (Arr::exists($detail, 'auto_generation')) {
                        $detail['auto_generation'] = ($detail['auto_generation'] == 1) ? 'Yes' : 'No';
                    }
                    if (Arr::exists($detail, 'funding_status')) {
                        $detail['funding_status'] = ($detail['funding_status'] == 1) ? 'Yes' : 'No';
                    }
                    if (Arr::exists($detail, 'whole_portfolio')) {
                        $detail['whole_portfolio'] = ($detail['whole_portfolio'] == 1) ? 'Yes' : 'No';
                    }
                    if (Arr::exists($detail, 'auto_syndicate_payment')) {
                        $detail['auto_syndicate_payment'] = ($detail['auto_syndicate_payment'] == 1) ? 'Yes' : 'No';
                    }
                    if (Arr::exists($detail, 'active_status')) {
                        $detail['active_status'] = ($detail['active_status'] == 1) ? 'ON' : 'OFF';
                    }
                    if (Arr::exists($detail, 'auto_invest')) {
                        $detail['auto_invest'] = ($detail['auto_invest'] == 1) ? 'Yes' : 'No';
                    }
                    if (Arr::exists($detail, 'label')) {
                        $label = json_decode($detail['label'], true);
                        $new_label = [];
                        if ($label) {
                            foreach ($label as $b) {
                                $new_label[] = self::getName('label', $b, 'name');
                            }
                        }
                        $detail['label'] = implode(',', $new_label);
                    }
                    if (Arr::exists($detail, 'global_syndication')) {
                        $detail['syndication_fee'] = null;
                        $detail['syndication_fee'] = $detail['global_syndication'];
                        unset($detail['global_syndication']);
                    }
                    if (Arr::exists($detail, 'display_value')) {
                        $detail['display_value'] = ($detail['display_value'] == 'mid') ? 'Merchant ID' : 'Name';
                    }
                    if (Arr::exists($detail, 'source_from')) {
                        unset($detail['source_from']);
                    }
                    if ($type == 'lender') {
                        if (Arr::exists($detail, 'management_fee') && (!$detail['management_fee'])) {
                            $detail['management_fee'] = 0;
                        }
                        if (Arr::exists($detail, 'syndication_fee') && (!$detail['syndication_fee'])) {
                            $detail['syndication_fee'] = 0;
                        }
                        if (Arr::exists($detail, 'underwriting_fee') && (!$detail['underwriting_fee'])) {
                            $detail['underwriting_fee'] = 0;
                        }
                    }
                }elseif ($type == 'company') {
                    if (Arr::exists($detail, 'syndicate')) {
                        $detail['syndicate'] = ($detail['syndicate'] == 1) ? 'Yes' : 'No'; 
                    }
                    if (Arr::exists($detail, 'merchant_permission')) {
                        $detail['merchant_permission'] = ($detail['merchant_permission'] == 1) ? 'Yes' : 'No';
                    }
                    if (Arr::exists($detail, 'company_status')) {
                        $detail['company_status'] = ($detail['company_status'] == 1) ? 'ON' : 'OFF';
                    }
                    if (Arr::exists($detail, 'brokerage')) {
                        $detail['brokerage'] = ($detail['brokerage']) ? $detail['brokerage'] : 0;
                    } else {
                        $detail['brokerage'] = 0;
                    }
                } elseif ($type == 'merchant') {  //Merchant creation
                    if (Arr::exists($detail, 'sub_status_flag')) {
                        $detail['sub_status_flag'] = self::getName('sub_status_flags', $detail['sub_status_flag'], 'name');
                    }
                    if (Arr::exists($detail, 'sub_status_id')) {
                        $detail['sub_status_id'] = self::getName('sub_statuses', $detail['sub_status_id'], 'name');
                    }
                    if (Arr::exists($detail, 'industry_id')) {
                        $detail['industry_id'] = self::getName('industries', $detail['industry_id'], 'name');
                    }
                    if (Arr::exists($detail, 'source_id')) {
                        $detail['source_id'] = self::getName('merchant_source', $detail['source_id'], 'name');
                    }
                    if (Arr::exists($detail, 'state_id')) {
                        $detail['state_id'] = self::getName('us_states', $detail['state_id'], 'state');
                    }
                    if (Arr::exists($detail, 'lender_id')) {
                        $data = Role::whereName('lender')->first()->users->where('id', $detail['lender_id'])->where('active_status', 1)->first();
                        $detail['lender_id'] = ($data) ? $data->name : '';
                    }
                    if (Arr::exists($detail, 'label')) {
                        $detail['label'] = self::getName('label', $detail['label'], 'name');
                    }
                    if (Arr::exists($detail, 'marketplace_status')) {
                        $detail['marketplace_status'] = ($detail['marketplace_status'] == 1) ? 'Yes' : 'No';
                    }
                    if (Arr::exists($detail, 'm_mgmnt_fee')) {
                        $detail['management_fee'] = null;
                        $detail['management_fee'] = $detail['m_mgmnt_fee'];
                        unset($detail['m_mgmnt_fee']);
                    }
                    if (Arr::exists($detail, 'm_s_prepaid_status')) {
                        $detail['merchant_syndication_prepaid_status'] = '';
                        if ($detail['m_s_prepaid_status'] == 1) {
                            $detail['merchant_syndication_prepaid_status'] = 'On RTR';
                        } elseif ($detail['m_s_prepaid_status'] == 2) {
                            $detail['merchant_syndication_prepaid_status'] = 'On Funding Amount';
                        }
                        unset($detail['m_s_prepaid_status']);
                    }
                    if (Arr::exists($detail, 'agent_fee_applied')) {
                        $agent_fee = '';
                        if ($detail['agent_fee_applied'] == 0) {
                            $agent_fee = 'No';
                        } elseif ($detail['agent_fee_applied'] == 1) {
                            $agent_fee = 'Yes';
                        }
                        $detail['agent_fee_applied'] = $agent_fee;
                    }
                    if (Arr::exists($detail, 'pay_off')) {
                        $detail['pay_off'] = ($detail['pay_off'] == 1) ? 'Yes' : 'No';
                    }
                    if (Arr::exists($detail, 'money_request_status')) {
                        $detail['money_request_status'] = ($detail['money_request_status'] == 1) ? 'Yes' : 'No';
                    }
                    if (Arr::exists($detail, 'notify_investors')) {
                        $detail['notify_investors'] = ($detail['notify_investors'] == 1) ? 'Yes' : 'No';
                    }
                    if (Arr::exists($detail, 'ach_pull')) {
                        $detail['ach_pull'] = ($detail['ach_pull'] == 1) ? 'Yes' : 'No';
                    }
                    if (Arr::exists($detail, 'creator_id')) {
                        $detail['creator_id'] = UserActivityLog::getNameFromDB('users', $detail['creator_id'], 'name');
                    }
                    if (Arr::exists($detail, 'advance_type')) {
                        $advance_type = Merchant::getAdvanceTypes();
                        if (array_key_exists($detail['advance_type'], $advance_type)) {
                            $detail['advance_type'] = $advance_type[$detail['advance_type']];
                        }
                    }

                    if (Arr::exists($detail, 'commission')) {
                        $detail['commission'] = number_format((float) $detail['commission'], 2);
                    }
                    if (Arr::exists($detail, 'experian_intelliscore')) {
                        if ($detail['experian_intelliscore'] != '') {
                            $detail['experian_intelliscore'] = number_format((float) $detail['experian_intelliscore'], 2);
                        }
                    }
                    if (Arr::exists($detail, 'max_participant_fund')) {
                        $detail['max_participant_fund'] = number_format((float) $detail['max_participant_fund'], 2);
                    }
                    if (Arr::exists($detail, 'actual_payment_left')) {
                        unset($detail['actual_payment_left']);
                    }
                } elseif ($type == 'merchant_user' || $type == 'merchant_investor') { //investor merchant creation.
                    if (Arr::exists($detail, 'investor')) {
                        $investor = $detail['investor'];
                        if ($investor) {
                            $detail['investor_name'] = $investor['name'];
                            unset($detail['investor']);
                        }
                    }

                    if (Arr::exists($detail, 'mgmnt_fee_percentage')) {
                        $investor = $detail['mgmnt_fee_percentage'];
                        if ($investor) {
                            unset($detail['mgmnt_fee_percentage']);
                        }
                    }

                    if (Arr::exists($detail, 'id')) {
                        $investor = $detail['id'];
                        if ($investor) {
                            unset($detail['id']);
                        }
                    }

                    if (Arr::exists($detail, 'creator_id')) {
                        $investor = $detail['creator_id'];
                        if ($investor) {
                            unset($detail['creator_id']);
                        }
                    }
                    if (Arr::exists($detail, 'paid_participant_ishare')) {
                        unset($detail['paid_participant_ishare']);
                    }

                    if (Arr::exists($detail, 'merchant_id')) {
                        $detail['merchant_name'] = self::getName('merchants', $detail['merchant_id'], 'name');
                        if (Arr::exists($detail, 'merchant') && gettype($detail['merchant'])) {
                            unset($detail['merchant']);
                        }
                    }

                    if (Arr::exists($detail, 'transaction_type')) {
                        $detail['transaction_type'] = 'ACH Works';
                    }
                    if (Arr::exists($detail, 'status')) {
                        $status = ['0' => 'Pending', '1' => 'Approved', '2'=>'Hide', '3'=>'Re-assigned', '4' => 'Rejected'];
                        $detail['status'] = ($detail['status'] != '') ? $status[$detail['status']] : '';
                    }
                    if (Arr::exists($detail, 'paid_mgmnt_fee') == false) {
                        $detail['paid_mgmnt_fee'] = 0;
                    }
                } elseif ($type == 'payment') { //payment creation.
                    if (Arr::exists($detail, 'rcode')) {
                        if ($detail['rcode']) {
                            $detail['rcode'] = self::getName('rcode', $detail['rcode'], 'code').' - '.self::getName('rcode', $detail['rcode'], 'description');
                        } else {
                            $detail['rcode'] = '';
                        }
                    }
                    if (Arr::exists($detail, 'transaction_type')) {
                        unset($detail['transaction_type']);
                    }
                    if (Arr::exists($detail, 'is_payment')) {
                        unset($detail['is_payment']);
                    }

                    if (Arr::exists($detail, 'payment_type')) {
                        if (Arr::exists($detail, 'merchant_id')) {
                            $detail['payment_type'] = ($detail['payment_type'] == 1) ? 'Credit' : 'Debit';
                        } else {
                            $detail['payment_type'] = ($detail['payment_type'] == 2) ? 'Credit' : 'Debit';
                        }
                    }
                    if (Arr::exists($detail, 'merchant_id') && Arr::exists($detail, 'agent_fee_percentage')) {
                        $merchant = Merchant::where('id', $detail['merchant_id'])->first();
                        if ($merchant && $merchant->agent_fee_applied == 0) {
                            unset($detail['agent_fee_percentage']);
                        }
                    }
                    if (Arr::exists($detail, 'model') && $detail['model'] == "App\\InvestorTransaction" && Arr::exists($detail, 'agent_fee_percentage')) {
                        unset($detail['agent_fee_percentage']);
                    }
                    if (Arr::exists($detail, 'mode_of_payment')) {
                        $mode = ParticipentPayment::paymentMethodOptions();
                        $mode_of_payment = $detail['mode_of_payment'];
                        $detail['mode_of_payment'] = ($mode_of_payment != '') ? $mode[$mode_of_payment] : '';
                    }
                    if (Arr::exists($detail, 'investor_ids')) {
                        unset($detail['investor_ids']);
                    }
                } elseif ($type == 'investor_transaction') {
                    if (Arr::exists($detail, 'investor_id')) {
                        $detail['investor_id'] = self::getName('users', $detail['investor_id'], 'name');
                    }
                    if (Arr::exists($detail, 'transaction_type')) {
                        $transaction_types = [1=>'Debit', 2=>'Credit'];
                        $detail['transaction_type'] = $transaction_types[$detail['transaction_type']];
                    }
                    if (Arr::exists($detail, 'transaction_method')) {
                        $method = InvestorTransaction::transactionMethodOptions();
                        $detail['transaction_method'] = $method[$detail['transaction_method']];
                    }
                    if (Arr::exists($detail, 'transaction_category')) {
                        $transaction_categories = \ITran::getAllOptions();
                        $detail['transaction_category'] = $transaction_categories[$detail['transaction_category']];
                    }
                    if (Arr::exists($detail, 'merchant_id')) {
                        unset($detail['merchant_id']);
                    }
                } elseif ($type == 'merchant_bank_account') {
                    if (Arr::exists($detail, 'default_debit') && $detail['default_debit']) {
                        $detail['default_debit'] = ($detail['default_debit'] == 1) ? 'Yes' : 'No';
                    }
                    if (Arr::exists($detail, 'default_credit') && $detail['default_credit']) {
                        $detail['default_credit'] = ($detail['default_credit'] == 1) ? 'Yes' : 'No';
                    }
                } elseif ($type == 'merchant_ach_term') {
                    if (Arr::exists($detail, 'start_at')) {
                        $detail['start_date'] = $detail['start_at'];
                        unset($detail['start_at']);
                    }
                    if (Arr::exists($detail, 'end_at')) {
                        $detail['end_date'] = $detail['end_at'];
                        unset($detail['end_at']);
                    }
                    if (Arr::exists($detail, 'advance_type')) {
                        $advance_type = Merchant::getAdvanceTypes();
                        if (array_key_exists($detail['advance_type'], $advance_type)) {
                            $detail['advance_type'] = $advance_type[$detail['advance_type']];
                        }
                    }
                } elseif ($type == 'ach_payment') {
                    if (Arr::exists($detail, 'status')) {
                        $term_status = TermPaymentDate::statusOptions();
                        $detail['status'] = $term_status[$detail['status']];
                    }
                } elseif ($type == 'ach_request') {
                    if (Arr::exists($detail, 'ach_status') == false) {
                        $detail['ach_status'] = 0;
                    }
                    if (Arr::exists($detail, 'ach_request_status') == false) {
                        $detail['ach_request_status'] = 0;
                    }
                    if (Arr::exists($detail, 'payment_status') == false) {
                        $detail['payment_status'] = 0;
                    }
                } elseif ($type == 'investor_ach_request') {
                    if (Arr::exists($detail, 'transaction_method')) {
                        $methods = InvestorAchRequest::transactionMethodOptions();
                        $detail['transaction_method'] = $methods[$detail['transaction_method']];
                    }
                    if (Arr::exists($detail, 'investor_id')) {
                        $detail['investor_id'] = self::getName('users', $detail['investor_id'], 'name');
                    }
                    if (Arr::exists($detail, 'bank_id')) {
                        $detail['bank_name'] = self::getName('bank_details', $detail['bank_id'], 'name');
                        unset($detail['bank_id']);
                    }
                    if (Arr::exists($detail, 'transaction_category')) {
                        $category = InvestorAchRequest::transactionCategoryOptions();
                        $detail['transaction_category'] = $category[$detail['transaction_category']];
                    }
                    if (Arr::exists($detail, 'ach_request_status')) {
                        $status = InvestorAchRequest::achRequestStatusOptions();
                        $detail['ach_request_status'] = $status[$detail['ach_request_status']];
                    }
                    if (Arr::exists($detail, 'ach_status')) {
                        $status = InvestorAchRequest::achStatusOptions();
                        $detail['ach_status'] = $status[$detail['ach_status']];
                    }
                } elseif ($type == 'bank_account') {
                    if (Arr::exists($detail, 'investor_id')) {
                        $detail['investor'] = self::getName('users', $detail['investor_id'], 'name');
                    }
                    if (Arr::exists($detail, 'acc_number')) {
                        $detail['account_number'] = \FFM::mask_cc($detail['acc_number']);
                        unset($detail['acc_number']);
                    }
                    if (Arr::exists($detail, 'name')) {
                        $detail['bank_name'] = $detail['name'];
                        unset($detail['name']);
                    }
                    if (Arr::exists($detail, 'type')) {
                        $detail['bank_type'] = $detail['type'];
                        unset($detail['type']);
                    }
                    if (Arr::exists($detail, 'default_debit') && $detail['default_debit'] != '') {
                        if ($detail['default_debit'] == 0) {
                            $detail['default_debit'] = 'No';
                        } else if ($detail['default_debit'] == 1) {
                            $detail['default_debit'] = 'Yes';
                        }
                    }
                    if (Arr::exists($detail, 'default_credit') && $detail['default_credit'] != '') {
                        if ($detail['default_credit'] == 0) {
                            $detail['default_credit'] = 'No';
                        } else if ($detail['default_credit'] == 1) {
                            $detail['default_credit'] = 'Yes';
                        }                    
                    }
                } elseif ($type == 'company_amount') {
                    if (!Arr::exists($detail, 'max_participant')) {
                        $detail['max_participant'] = 0;
                    }
                }
                if (Arr::exists($detail, 'underwriting_status')) {
                    $value = json_decode($detail['underwriting_status'], true);
                    $data = Role::whereName('company')->first()->users->where('company_status',1)->pluck('name', 'id')->toArray();
                    array_unshift($data, '');
                    unset($data[0]);
                    $new = [];
                    foreach ($data as $k => $d) {
                        if ($value && in_array($k, $value)) {
                            $new[] = $d;
                        }
                    }
                    $detail['underwriting_status'] = implode(',', $new);
                }
                if (Arr::exists($detail, 's_prepaid_status')) {
                    if ($detail['s_prepaid_status'] == 0) {
                        $detail['s_prepaid_status'] = '';
                    } elseif ($detail['s_prepaid_status'] == 1) {
                        $detail['s_prepaid_status'] = 'On RTR';
                    } elseif ($detail['s_prepaid_status'] == 2) {
                        $detail['s_prepaid_status'] = 'On Funding Amount';
                    }
                }
            }
            if (Auth::user()) {
                $auth_user = Auth::user()->id;
            } elseif ($creator) {
                $auth_user = $creator;
            } else {
                $auth_user = $crm_user->model_id;
            }
            UserActivityLogJob::dispatch($auth_user, $type, $action, $object->id, $detail);
        }
    }

    public static function saveActivityLog(int $userId, string $type, string $action, int $objectId = 0, array $details = [])
    {
        $investor_id = null;
        $merchant_id = null;
        // Adding investor ID and merchant ID to table
        try {
            switch ($type) {
                case 'user':
                case 'investor':
                case 'company':
                case 'lender':
                case 'user_details':
                case 'bank_account':
                case 'user_merchant':
                    if ($type == 'user_details') {
                        $user_details = UserDetails::where('id', $objectId)->first();
                        if ($user_details) {
                            $investor_id = $user_details->user_id;
                        } else {
                            if (array_key_exists('user_id', $details)) {
                                $investor_id = $details['user_id'];
                            }
                        }
                        break;
                    } elseif ($type == 'bank_account') {
                        $bank = Bank::where('id', $objectId)->first();
                        if ($bank) {
                            $investor_id = $bank->investor_id;
                        } elseif (array_key_exists('investor_id', $details)) {
                            $investor_id = $details['investor_id'];
                        }
                        if (array_key_exists('investor_id', $details)) {
                            unset($details['investor_id']);
                        }
                        break;
                    } elseif ($type == 'user_merchant') {
                        $merchant_id = Merchant::where('user_id', $objectId)->value('id');
                        break;
                    } else {
                        $investor_id = $objectId;
                        break;
                    }
                    break;
                case 'investor_transaction':
                    $transaction = InvestorTransaction::where('id', $objectId)->first();
                    if ($transaction) {
                        $investor_id = $transaction->investor_id;
                        break;
                    } else {
                        if (array_key_exists('investor_id', $details)) {
                            $investor_id = User::withTrashed()->where('name', $details['investor_id'])->value('id');
                            break;
                        }
                    }
                    break;
                case 'merchant':
                    $merchant = Merchant::withTrashed()->where('id', $objectId)->first();
                    $merchant_id = ($merchant) ? $merchant->id : null;
                    break;
                case 'merchant_user':
                    $merchant_user = MerchantUser::where('id', $objectId)->first();
                    if ($merchant_user) {
                        $investor_id = $merchant_user->user_id;
                        $merchant_id = $merchant_user->merchant_id;
                    } else {
                        if (array_key_exists('investor_name', $details)) {
                            $investor_id = User::withTrashed()->where('name', $details['investor_name'])->value('id');
                        } elseif (array_key_exists('user_id', $details)) {
                            $investor_id = $details['user_id'];
                            unset($details['user_id']);
                        }
                        if (array_key_exists('merchant_id', $details)) {
                            $merchant_id = $details['merchant_id'];
                        } elseif (array_key_exists('merchant_name', $details)) {
                            $merchant_id = Merchant::withTrashed()->where('name', $details['merchant_name'])->value('id');
                        }
                    }
                    break;
                case 'company_amount':
                    $company_amount = CompanyAmount::where('id', $objectId)->first();
                    if ($company_amount) {
                        $merchant_id = $company_amount->merchant_id;
                    } elseif (array_key_exists('merchant_id', $details)) {
                        $merchant_id = $details['merchant_id'];
                    }
                    break;
                case 'merchant_note':
                    $note = MNotes::where('id', $objectId)->first();
                    $merchant_id = ($note) ? $note->merchant_id : null;
                    break;
                case 'merchant_bank_account':
                    $bank = MerchantBankAccount::where('id', $objectId)->first();
                    if ($bank) {
                        $merchant_id = $bank->merchant_id;
                    } elseif (array_key_exists('merchant_id', $details)) {
                        $merchant_id = $details['merchant_id'];
                    }
                    break;
                case 'payment':
                    $payment = ParticipentPayment::where('id', $objectId)->first();
                    if ($payment) {
                        if ($payment->merchant_id) {
                            $merchant_id = $payment->merchant_id;
                        } elseif ($payment->merchant_id == 0) {
                            $investor_transaction = InvestorTransaction::where('id', $payment->model_id)->first();
                            if ($investor_transaction) {
                                $investor_id = $investor_transaction->investor_id;
                            }
                        }
                    } else {
                        //deleting payment
                        if (array_key_exists('merchant_id', $details) && $details['merchant_id']) {
                            $merchant_id = $details['merchant_id'];
                        } elseif (array_key_exists('merchant_id', $details) && ($details['merchant_id'] == 0) && array_key_exists('model_id', $details)) {
                            $investor_id = UserActivityLog::where(['object_id' => $details['model_id'], 'type' => 'investor_transaction'])->value('investor_id');
                        }
                    }
                    break;
                case 'merchant_ach_term':
                    $term = MerchantPaymentTerm::where('id', $objectId)->first();
                    if ($term) {
                        $merchant_id = $term->merchant_id;
                    } else {
                        if (array_key_exists('merchant_id', $details)) {
                            $merchant_id = $details['merchant_id'];
                        }
                    }
                    break;
                case 'ach_payment':
                    $ach_payment = TermPaymentDate::where('id', $objectId)->first();
                    if ($ach_payment) {
                        $merchant_id = $ach_payment->merchant_id;
                    } elseif (array_key_exists('merchant_id', $details)) {
                        $merchant_id = $details['merchant_id'];
                    }
                    break;
                case 'payment_pause':
                case 'payment_resume':
                    $pause = PaymentPause::where('id', $objectId)->first();
                    if ($pause) {
                        $merchant_id = $pause->merchant_id;
                    } elseif (array_key_exists('merchant_id', $details)) {
                        $merchant_id = $details['merchant_id'];
                    }
                    break;
                case 'ach_request':
                    $ach = AchRequest::where('id', $objectId)->first();
                    if ($ach) {
                        $merchant_id = $ach->merchant_id;
                    } elseif (array_key_exists('merchant_id', $details)) {
                        $merchant_id = $details['merchant_id'];
                    }
                    break;
                case 'velocity_fee':
                    $fee = VelocityFee::where('id', $objectId)->first();
                    if ($fee) {
                        $merchant_id = $fee->merchant_id;
                    } elseif (array_key_exists('merchant_id', $details)) {
                        $merchant_id = $details['merchant_id'];
                    }
                    break;
                case 'investor_ach_request':
                    $investor_ach = InvestorAchRequest::where('id', $objectId)->first();
                    if ($investor_ach) {
                        $investor_id = $investor_ach->investor_id;
                    } elseif (array_key_exists('investor_id', $details)) {
                        $investor_id = $details['investor_id'];
                    }
                    break;
                default:
                    $investor_id = null;
                    $merchant_id = null;
                    break;
            }
        } catch (\Throwable $th) {
            //throw $th;
        }

        UserActivityLog::create([
            'type' => $type,
            'action' => $action,
            'object_id' => $objectId,
            'user_id' => $userId,
            'detail' => json_encode($details),
            'investor_id' => $investor_id,
            'merchant_id' => $merchant_id,
        ]);
    }

    public static function makeLogTypeToRoleName($user, $roleName = 'investor')
    {
        if (Auth::user()) {
            $log = UserActivityLog::where('object_id', $user->id)
                ->where('user_id', Auth::user()->id)
                ->where('action', 'created')
                ->where('type', 'user')
                ->first();
            if ($log) {
                $log->update([
                    'type' => 'investor',
                ]);
            }
        }
    }

    public static function loginAttemptEvent($user)
    {
        if ($user) {
            $roleName = DB::table('user_has_roles')
                ->where('model_id', $user->id)
                ->join('roles', 'roles.id', '=', 'user_has_roles.role_id')
                ->value('name');

            $detail = [
               // 'id'    => $user->id,
                'role'  => $roleName,
                'name'  => $user->name,
                'ip'    => request()->ip(),
                'login_date'=> date('Y-m-d H:i:s'),
            ];

            UserActivityLog::create([
                'type' => 'login',
                'action' => 'created',
                'user_id' => $user->id,
                'object_id' => $user->id,
                'detail' => json_encode($detail),
                'investor_id' => ($roleName == 'investor') ? $user->id : null,
            ]);
        }
    }

    public static function getName($table_name, $id, $label)
    {
        if (gettype($id) != 'array') {
            $id = [$id];
        }

        return DB::table($table_name)->whereIn('id', $id)->select($label)->value($label);
    }
}
