DROP VIEW IF EXISTS investor_ach_request_views;
CREATE VIEW investor_ach_request_views AS
  SELECT
  IAR.id,
  IAR.investor_id,
  U.name as Investor,
  IAR.transaction_type,
  IAR.transaction_method,
  IAR.transaction_category,
  IAR.order_id,
  IAR.date,
  IAR.amount,
  IAR.ach_status,
  IAR.ach_request_status,
  IAR.auth_code,
  IAR.reason,
  IAR.status_response,
  IAR.request_ip_address,
  IAR.updated_at,
  IAR.creator_id
  FROM       `investor_ach_requests` AS IAR
  INNER JOIN `users`                 AS U ON U.id = IAR.investor_id;
DROP VIEW IF EXISTS merchant_views;
CREATE VIEW merchant_views AS
  SELECT
  M.id,
  M.active_status,
  M.user_id,
  M.actual_payment_left,
  M.advance_type,
  M.annualized_rate,
  M.balance,
  M.business_en_name,
  M.commission,
  M.complete_percentage,
  M.credit_score,
  M.date_funded,
  M.factor_rate,
  M.old_factor_rate,
  M.first_payment,
  M.funded,
  M.industry_id,
  IFNULL(I.name,'') as Industry,
  M.label,
  M.last_payment_date,
  M.payment_end_date,
  M.payment_pause_id,
  M.last_rcode,
  M.last_status_updated_date,
  M.lender_id,
  M.liquidity,
  M.m_mgmnt_fee,
  M.m_s_prepaid_status,
  M.m_syndication_fee,
  M.mail_send_status,
  M.marketplace_permission,
  M.marketplace_status,
  M.max_participant_fund,
  M.money_request_status,
  M.move_status,
  M.name,
  M.open_item,
  M.paid_count,
  M.pay_off,
  M.payment_amount,
  M.pmnts,
  M.rtr,
  M.source_id,
  M.state_id,
  IFNULL(S.state,'') as State,
  M.sub_status_flag,
  M.sub_status_id,
  IFNULL(SS.state,'') as SubState,
  M.underwriting_fee,
  M.underwriting_status,
  M.origination_fee,
  M.up_sell_commission,
  M.ach_pull,
  M.phone,
  M.cell_phone,
  M.notification_email,
  M.experian_intelliscore,
  M.creator_id,
  M.created_at,
  M.updated_at
  FROM       `merchants`       AS M
  LEFT  JOIN `industries`      AS I  ON I.id  = M.industry_id
  LEFT  JOIN `merchant_source` AS MS ON MS.id = M.source_id
  LEFT  JOIN `us_states`       AS S  ON S.id  = M.state_id
  LEFT  JOIN `us_states`       AS SS ON SS.id = M.sub_status_id;
DROP VIEW IF EXISTS merchant_user_views;
CREATE VIEW merchant_user_views AS
  SELECT
  MU.id,
  MU.merchant_id,
  MV.name as Merchant,
  MV.industry_id,
  MV.Industry,
  MU.user_id as investor_id,
  IFNULL(I.name,'') as Investor,
  I.company,
  MU.creator_id,
  IFNULL(U.name,'') as Creator,
  MV.sub_status_id,
  MV.SubState,
  MU.amount,
  MU.invest_rtr,
  MU.under_writing_fee_per,
  MU.under_writing_fee,
  MU.pre_paid,
  MU.commission_per,
  MU.commission_amount,
  MU.up_sell_commission_per,
  MU.up_sell_commission,
  MU.amount+MU.commission_amount+MU.under_writing_fee+MU.pre_paid+MU.up_sell_commission AS total_investment,
  -- MU.approved_time,
  -- MV.commission,
  MU.deal_name,
  MU.mgmnt_fee,
  round(MU.mgmnt_fee*MU.invest_rtr/100,2) AS  expected_mgmnt_fee_amount,
  -- MU.open_item,
  -- MU.overpayment_status,
  MU.paid_mgmnt_fee,
  round(IF(MU.paid_mgmnt_fee,MU.paid_mgmnt_fee,0)-round(MU.mgmnt_fee*MU.invest_rtr/100,2),2) AS mgmnt_fee_diff,
  -- MU.paid_participant,
  MU.paid_participant_ishare,
  MU.actual_paid_participant_ishare,
  MU.actual_paid_participant_ishare-MU.paid_mgmnt_fee AS net_amount,
  -- MU.paid_syndication_fee,
  MU.mgmnt_fee_percentage,
  MU.paid_principal,
  MU.paid_profit,
  -- MU.requested_time,
  MU.s_prepaid_status,
  IF(MV.funded,MU.amount/MV.funded*100,0) AS share,
  MU.status,
  -- MU.syndication_fee,
  MU.syndication_fee_percentage,
  -- MU.transaction_type,
  MV.funded,
  MV.date_funded,
  MV.factor_rate,
  MV.old_factor_rate,
  MV.underwriting_status,
  MV.active_status,
  MV.label,
  MV.advance_type,
  MV.sub_status_flag,
  MV.lender_id,
  MV.complete_percentage AS merchant_completed_percentate,
  round(MU.amount/MV.funded*100,2) AS investor_share_percentage,
  MU.total_agent_fee,
  round((IF(MU.actual_paid_participant_ishare,(MU.actual_paid_participant_ishare+MU.total_agent_fee),0)-IF(MU.invest_rtr,MU.invest_rtr,0)),2) as user_balance_amount,
  MU.complete_per,
  IF(MU.invest_rtr,round(
      (
          IF(MU.actual_paid_participant_ishare,MU.actual_paid_participant_ishare,0)/
          IF(MU.invest_rtr,MU.invest_rtr,0)
      )*100
      ,2
  ),0) as actual_completed_percentage,
  U.liquidity_exclude,
  MV.last_status_updated_date,
  MU.created_at,
  MU.updated_at,
  MV.created_at as merchant_created_at
  FROM       `merchant_user`   AS MU
  INNER JOIN `merchant_views`  AS MV ON MV.id = MU.merchant_id
  LEFT  JOIN `users`           AS I  ON I.id  = MU.user_id
  LEFT  JOIN `users`           AS U  ON U.id  = MU.creator_id;
DROP VIEW IF EXISTS investment_report_views;
CREATE VIEW investment_report_views AS
  SELECT
  MUV.merchant_id,
  MUV.Merchant,
  MUV.industry_id,
  MUV.Industry,
  MUV.investor_id,
  MUV.Investor,
  MUV.sub_status_id,
  MUV.SubState,
  MUV.label,
  MUV.advance_type,
  MUV.sub_status_flag,
  MUV.lender_id,
  MUV.funded,
  MUV.date_funded,
  MUV.up_sell_commission_per,
  MUV.commission_per as commission,
  MUV.underwriting_status,
  MUV.active_status,
  sum(MUV.amount + MUV.commission_amount + MUV.under_writing_fee + MUV.pre_paid+MUV.up_sell_commission) as invested_amount,
  sum(MUV.actual_paid_participant_ishare - MUV.paid_mgmnt_fee ) AS ctd,
  sum(IF(MUV.actual_paid_participant_ishare > MUV.invest_rtr, (MUV.actual_paid_participant_ishare - MUV.invest_rtr )*(1- (MUV.mgmnt_fee)/100 ), 0) ) AS overpayment,
  sum(MUV.commission_amount)               AS commission_amount,
  sum(MUV.up_sell_commission)              AS up_sell_commission,
  sum(MUV.pre_paid)                        AS pre_paid,
  sum(MUV.under_writing_fee)               AS under_writing_fee,
  sum(MUV.invest_rtr * MUV.mgmnt_fee/100)  AS mgmnt_fee,
  sum(MUV.amount)                          AS i_amount,
  sum(MUV.invest_rtr)                      AS i_rtr,
  ROUND((sum(MUV.amount)/MUV.funded)*100,2)AS share_t,
  -- MUV.created_at,
  MUV.merchant_created_at as created_at
  FROM `merchant_user_views`   AS MUV
  WHERE MUV.active_status=1 AND MUV.status IN (1,3)
  GROUP BY MUV.investor_id,MUV.merchant_id;
DROP VIEW IF EXISTS profit_checks;
CREATE VIEW profit_checks AS
      SELECT
      PIN.user_id,
      I.name as Investor,
      MU.amount,
      MU.commission_amount,
      MU.up_sell_commission,
      MU.pre_paid,
      MU.under_writing_fee,
      (MU.amount+MU.commission_amount+MU.pre_paid+MU.under_writing_fee+MU.up_sell_commission) as InvestedAmount,
      PIN.merchant_id,
      M.name as Merchant,
      sum(PIN.mgmnt_fee) as mgmnt_fee,
      sum(PIN.overpayment) as overpayment,
      sum(PIN.principal) as principal,
      sum(PIN.profit) as profit,
      sum(PIN.participant_share) as participant_share,
      sum(PIN.profit)+sum(PIN.principal) as principal_plus_profit,
      sum(PIN.mgmnt_fee+PIN.principal+PIN.profit) as actual_share,
      sum((PIN.mgmnt_fee+PIN.principal+PIN.profit)-PIN.participant_share) as share_diff,
      (MU.amount+MU.commission_amount+MU.pre_paid+MU.under_writing_fee+MU.up_sell_commission)-sum(PIN.principal) as principal_diff,
      sum(PIN.participant_share)-sum(PIN.profit)-sum(PIN.principal)-sum(PIN.mgmnt_fee) as principal_plus_profit_share_diff
      FROM       `payment_investors`  AS PIN
      LEFT  JOIN `users`              AS I  ON I.id       = PIN.user_id
      LEFT  JOIN `merchants`          AS M  ON M.id       = PIN.merchant_id
      INNER JOIN `merchant_user`      AS MU ON MU.user_id = I.id && MU.merchant_id = M.id
      Group BY I.id,M.id;
DROP VIEW IF EXISTS fee_report_views;
CREATE VIEW fee_report_views AS
  SELECT
  PIN.merchant_id,
  M.name as Merchant,
  PP.payment_date as date,
  sum(PIN.mgmnt_fee) as fee,
  PP.creator_id as creator_id,
  PIN.created_at as created_at
  FROM       `payment_investors`    AS PIN
  INNER JOIN `participent_payments` AS PP ON PP.id = PIN.participent_payment_id
  INNER JOIN `merchants`            AS M  ON M.id  = PP.merchant_id
  WHERE PIN.mgmnt_fee != 0
  Group BY PP.payment_date,PIN.merchant_id;

DROP VIEW IF EXISTS merchant_fund_detail_views;
CREATE VIEW merchant_fund_detail_views AS
  SELECT
  M.id as merchant_id,
  M.name as Merchant,
  M.funded,
  M.max_participant_fund,
  sum(MU.amount) as investor_funded_amount,
  (M.max_participant_fund-sum(MU.amount)) as investor_funded_amount_diff,
  round(((M.max_participant_fund/M.funded)*100),2) as percentage,
  M.factor_rate,
  M.rtr,
  sum(MU.invest_rtr) as investor_rtr,
  sum(MU.actual_paid_participant_ishare) as investor_paid,
  sum(MU.invest_rtr-MU.actual_paid_participant_ishare) as investor_balance,
  M.complete_percentage,
  count(MU.id) as no_of_investors
  FROM       `merchants`    AS M
  LEFT JOIN merchant_user   AS MU ON MU.merchant_id=M.id AND MU.amount!=0
  WHERE M.complete_percentage != 100
  GROUP BY MU.merchant_id;
  
DROP VIEW IF EXISTS investor_transactions_view;
CREATE VIEW investor_transactions_view AS
  SELECT
  creator_id,
  merchant_id,
  investor_id as user_id,
  account_no,
  amount,
  batch,
  category_notes,
  entity1,
  entity2,
  entity3,
  entity4,
  entity5,
  entity6,
  transaction_category,
  transaction_method,
  transaction_type,
  status,
  maturity_date,
  date,
  created_at
  FROM investor_transactions AS IT
  WHERE IT.status =  1;
  DROP VIEW IF EXISTS all_transactions_views; 
  CREATE VIEW all_transactions_views AS
  SELECT  
  batch,
    id,
    amount,
    investor_id as user_id,
    category_notes,
    transaction_category,
    transaction_method,
    transaction_type,
    account_no,
    null as ach_request_status,
    date
  FROM investor_transactions AS IT
  UNION
    SELECT 
    0 as batch,
    id,
    CASE WHEN transaction_type='debit' THEN amount
  WHEN transaction_type='same_day_debit' THEN amount
  ELSE (0-amount)
  END AS amount,
    investor_id as user_id,
    null as category_notes,
    CASE 
    WHEN (transaction_type='debit' OR transaction_type='same_day_debit') AND ach_request_status=1 
    THEN 44
    WHEN (transaction_type='debit' OR transaction_type='same_day_debit') AND ach_request_status=3 
    THEN 45
    WHEN ach_request_status=1 
    THEN 43
  ELSE 46
  END AS transaction_category,
    transaction_method,
    transaction_type,
    null as account_no,
    ach_request_status,
    date
    FROM investor_ach_requests AS IT1;

DROP VIEW IF EXISTS user_total_credits_view;
CREATE VIEW user_total_credits_view AS
  SELECT
  user_id,
  sum(amount) as total_credits
  FROM investor_transactions_view AS ITV
  GROUP BY ITV.user_id;
DROP VIEW IF EXISTS user_details_liquidity_check_view;
CREATE VIEW user_details_liquidity_check_view AS
  SELECT
  UD.user_id,
  U.name as Investor,
  IF(MU.paid_mgmnt_fee,sum(MU.paid_mgmnt_fee),0) AS paid_mgmnt_fee,
  IF(MU.paid_participant_ishare,sum(MU.paid_participant_ishare),0) AS paid_participant_ishare,
  IF(MU.paid_participant_ishare,sum(MU.paid_participant_ishare-MU.paid_mgmnt_fee),0) AS ctd,
  IF(MU.amount,sum(MU.amount),0) AS total_funded,
  IF(MU.commission_amount,sum(MU.commission_amount),0) AS commission_amount,
  IF(MU.up_sell_commission,sum(MU.up_sell_commission),0) AS upsell_commission_amount,
  IF(MU.under_writing_fee,sum(MU.under_writing_fee),0) AS under_writing_fee,
  IF(MU.pre_paid,sum(MU.pre_paid),0) AS pre_paid,
  IF(UTCV.total_credits,UTCV.total_credits,0) as total_credits,
  (UD.liquidity+UD.liquidity_adjuster) as existing_liquidity, (
      (IF(UTCV.total_credits,UTCV.total_credits,0) + sum(MU.paid_participant_ishare-MU.paid_mgmnt_fee)) - (sum(MU.amount) + sum(MU.commission_amount)+ sum(MU.up_sell_commission)) - sum(MU.pre_paid) - sum(MU.under_writing_fee)
  ) as actual_liquidity,
  ROUND( (
      (IF(UTCV.total_credits,UTCV.total_credits,0) + sum(MU.paid_participant_ishare-MU.paid_mgmnt_fee)) - (sum(MU.amount) + sum(MU.commission_amount)+sum(MU.up_sell_commission)) - sum(MU.pre_paid) - sum(MU.under_writing_fee)
  ) - (UD.liquidity-UD.liquidity_adjuster)
  ,2) as diff
  FROM user_details                 AS UD
  LEFT JOIN merchant_user           AS MU   ON MU.user_id   = UD.user_id AND MU.status != 0
  LEFT JOIN user_total_credits_view AS UTCV ON UTCV.user_id = UD.user_id
  JOIN users AS U ON U.id = UD.user_id
  GROUP BY UD.user_id;
DROP VIEW IF EXISTS participent_payments_check_view;
CREATE VIEW participent_payments_check_view AS
  SELECT
  PP.merchant_id,
  M.name AS Merchant,
  M.funded,
  M.max_participant_fund,
  round(M.max_participant_fund/M.funded*100,3) AS percentage,
  M.factor_rate,
  M.rtr AS existing_rtr,
  round((M.funded*M.factor_rate),2) AS actual_rtr,
  round(round((M.funded*M.factor_rate),2)-M.rtr,2) AS rtr_diff,
  M.complete_percentage,
  sum(PP.payment) AS payment,
  round(M.rtr-sum(PP.payment),2) AS balance,
  sum(PP.final_participant_share) AS existing_final_participant_share
  FROM participent_payments AS PP
  JOIN merchants            AS M ON M.id = PP.merchant_id AND M.sub_status_id IN (11,1) AND M.label IN (1,2)
  GROUP BY PP.merchant_id;
DROP VIEW IF EXISTS payment_investors_check_view;
CREATE VIEW payment_investors_check_view AS
  SELECT
  PI.merchant_id as MerchantId,
  sum(PI.participant_share) AS participant_share,
  sum(PI.mgmnt_fee) AS mgmnt_fee,
  sum(PI.participant_share-PI.mgmnt_fee-IF(PI.syndication_fee,PI.syndication_fee,0)) AS actual_final_participant_share,
  IF(PI.syndication_fee,sum(PI.syndication_fee),0) AS syndication_fee
  FROM payment_investors    AS PI
  GROUP BY PI.merchant_id;
DROP PROCEDURE IF EXISTS payment_investors_check_procedure;
CREATE PROCEDURE payment_investors_check_procedure (merchantId INT)
  SELECT
  PI.merchant_id as MerchantId,
  sum(PI.participant_share) AS participant_share,
  sum(PI.mgmnt_fee) AS mgmnt_fee,
  sum(PI.participant_share-PI.mgmnt_fee-IF(PI.syndication_fee,PI.syndication_fee,0)) AS actual_final_participant_share,
  PP.final_participant_share,
  PP.payment as ExistingPayment,
  IF(PI.syndication_fee,sum(PI.syndication_fee),0) AS syndication_fee
  FROM payment_investors    AS PI
  JOIN participent_payments AS PP ON PP.id = PI.participent_payment_id
  JOIN merchants            AS M  ON M.id  = PP.merchant_id
  WHERE PI.merchant_id=merchantId
  GROUP BY PI.merchant_id;
DROP VIEW IF EXISTS company_amount_check_view;
CREATE VIEW company_amount_check_view AS
  SELECT
  MUV.company,
  MUV.merchant_id,
  MUV.Merchant,
  M.funded,
  M.rtr,
  M.factor_rate,
  M.max_participant_fund,
  round(M.max_participant_fund/M.funded*100,3) AS percentage,
  M.complete_percentage,
  sum(MUV.amount) AS actual_amount,
  IF(CA.max_participant,CA.max_participant,0) AS existing_amount,
  round(IF(CA.max_participant,CA.max_participant,0)-sum(MUV.amount),2) AS investor_company_diff
  FROM merchant_user_views  AS MUV
  LEFT JOIN company_amount  AS CA ON CA.company_id = MUV.company AND CA.merchant_id = MUV.merchant_id
  JOIN merchants            AS M  ON M.id = MUV.merchant_id AND M.label IN (1,2)
  GROUP BY MUV.company,MUV.merchant_id;
DROP VIEW IF EXISTS company_amount_pivot_check_view;
CREATE VIEW company_amount_pivot_check_view AS
  SELECT
  CACV.merchant_id,
  CACV.Merchant,
  CACV.funded,
  CACV.factor_rate,
  CACV.rtr,
  CACV.percentage,
  CACV.complete_percentage,
  SUM(IF(CACV.company = 58,  CACV.actual_amount, 0))          AS Actual_Company58,
  SUM(IF(CACV.company = 58,  CACV.existing_amount, 0))        AS Existing_Company58,
  SUM(IF(CACV.company = 58,  CACV.investor_company_diff, 0))  AS Diff_Company58,
  SUM(IF(CACV.company = 89,  CACV.actual_amount, 0))          AS Actual_Company89,
  SUM(IF(CACV.company = 89,  CACV.existing_amount, 0))        AS Existing_Company89,
  SUM(IF(CACV.company = 89,  CACV.investor_company_diff, 0))  AS Diff_Company89,
  SUM(IF(CACV.company = 284, CACV.actual_amount, 0))          AS Actual_Company284,
  SUM(IF(CACV.company = 284, CACV.existing_amount, 0))        AS Existing_Company284,
  SUM(IF(CACV.company = 284, CACV.investor_company_diff, 0))  AS Diff_Company284,
  CACV.max_participant_fund AS existing_max_participant_fund,
  round(
      SUM(IF(CACV.company = 89 ,CACV.existing_amount, 0)) +
      SUM(IF(CACV.company = 284,CACV.existing_amount, 0))+
      SUM(IF(CACV.company = 58 ,CACV.existing_amount, 0))
      ,2) AS total_company_amount,
      round(CACV.max_participant_fund-(
          SUM(IF(CACV.company = 89 ,CACV.existing_amount, 0)) +
          SUM(IF(CACV.company = 284,CACV.existing_amount, 0))+
          SUM(IF(CACV.company = 58 ,CACV.existing_amount, 0))
      ),2) AS merchant_company_diff,
      round(
          SUM(IF(CACV.company = 89,CACV.investor_company_diff, 0)) +
          SUM(IF(CACV.company = 284,CACV.investor_company_diff, 0))+
          SUM(IF(CACV.company = 58,CACV.investor_company_diff, 0))
          ,2) AS invsetor_company_diff
          FROM company_amount_check_view AS CACV
          GROUP BY CACV.merchant_id;
DROP VIEW IF EXISTS zero_payment_amount_check_view;
CREATE VIEW zero_payment_amount_check_view AS
  SELECT
  M.name AS Merchant,
  sum(payment) as amount,
  merchant_id
  FROM participent_payments AS PP
  JOIN merchants            AS M ON M.id = PP.merchant_id AND M.sub_status_id IN (11,1) AND M.label IN (1,2)
  WHERE final_participant_share = 0 AND payment !=0 AND model='App\ParticipentPayment'
  GROUP BY merchant_id;
DROP VIEW IF EXISTS final_participant_share_check_view;
CREATE VIEW final_participant_share_check_view AS
  SELECT
  M.name AS Merchant,
  PI.participent_payment_id,
  M.id as merchant_id,
  round(M.max_participant_fund/M.funded*100,3) AS percentage,
  PP.payment_date,
  PP.payment,
  round(
      (
          (PP.payment/100)*round(M.max_participant_fund/M.funded*100,3)
      )-(
          round(
              (
                  ( (PP.payment/100)*round(M.max_participant_fund/M.funded*100,3) )*M.m_mgmnt_fee / 100
              )
              ,2
          )
      )
      ,2
  ) as expected_final_participant_share,
  PP.final_participant_share as existing_final_participant_share,
  round(PP.final_participant_share-
      round(
          (
              (PP.payment/100)*round(M.max_participant_fund/M.funded*100,3)
          )-(
              round(
                  (
                      ( (PP.payment/100)*round(M.max_participant_fund/M.funded*100,3) )*M.m_mgmnt_fee / 100
                  )
                  ,2
              )
          )
          ,2
      )
      ,2
  ) as expected_existing_participant_share,
  round(sum(PI.participant_share-PI.mgmnt_fee-IF(PI.syndication_fee,PI.syndication_fee,0)),2) as actual_final_participant_share,
  round(PP.final_participant_share-sum(PI.participant_share-PI.mgmnt_fee-IF(PI.syndication_fee,PI.syndication_fee,0)),2) as diff
  FROM payment_investors    AS PI
  JOIN participent_payments AS PP ON PP.id = PI.participent_payment_id
  JOIN merchants            AS M  ON M.id = PP.merchant_id
  WHERE M.complete_percentage<100 AND M.label IN (1,2)
  GROUP BY PI.participent_payment_id;

DROP VIEW IF EXISTS final_participant_share_grouped_check_view;
CREATE VIEW final_participant_share_grouped_check_view AS
  SELECT
  FPSCV.Merchant,
  FPSCV.participent_payment_id,
  FPSCV.merchant_id,
  FPSCV.percentage,
  sum(FPSCV.payment) as payment,
  sum(FPSCV.expected_final_participant_share) as expected_final_participant_share,
  sum(FPSCV.expected_existing_participant_share) as expected_existing_participant_share,
  sum(FPSCV.existing_final_participant_share) as existing_final_participant_share,
  sum(FPSCV.actual_final_participant_share) as actual_final_participant_share,
  sum(FPSCV.diff) as diff
  FROM final_participant_share_check_view AS FPSCV
  GROUP BY FPSCV.merchant_id;
DROP PROCEDURE IF EXISTS final_participant_share_grouped_check_procedure;
CREATE PROCEDURE final_participant_share_grouped_check_procedure (merchantId INT)
  SELECT
  M.name AS Merchant,
  PI.participent_payment_id,
  M.id as merchant_id,
  round(M.max_participant_fund/M.funded*100,3) AS percentage,
  PP.payment_date,
  PP.payment,
  round(
      (
          (PP.payment/100)*round(M.max_participant_fund/M.funded*100,3)
      )-(
          round(
              (
                  ( (PP.payment/100)*round(M.max_participant_fund/M.funded*100,3) )*M.m_mgmnt_fee / 100
              )
              ,2
          )
      )
      ,2
  ) as expected_final_participant_share,
  PP.final_participant_share as existing_final_participant_share,
  round(PP.final_participant_share-
      round(
          (
              (PP.payment/100)*round(M.max_participant_fund/M.funded*100,3)
          )-(
              round(
                  (
                      ( (PP.payment/100)*round(M.max_participant_fund/M.funded*100,3) )*M.m_mgmnt_fee / 100
                  )
                  ,2
              )
          )
          ,2
      )
      ,2
  ) as expected_existing_participant_share,
  round(sum(PI.participant_share-PI.mgmnt_fee-IF(PI.syndication_fee,PI.syndication_fee,0)),2) as actual_final_participant_share,
  round(PP.final_participant_share-sum(PI.participant_share-PI.mgmnt_fee-IF(PI.syndication_fee,PI.syndication_fee,0)),2) as diff
  FROM payment_investors    AS PI
  JOIN participent_payments AS PP ON PP.id = PI.participent_payment_id
  JOIN merchants            AS M  ON M.id = PP.merchant_id
  where PP.merchant_id=merchantId
  GROUP BY PI.participent_payment_id;

DROP VIEW IF EXISTS investor_share_check_view;
CREATE VIEW investor_share_check_view AS
  SELECT
  merchant_id,
  Merchant,
  investor_id,
  Investor,
  complete_per,
  actual_completed_percentage,
  round(IF(complete_per,complete_per,0)-IF(actual_completed_percentage,actual_completed_percentage,0),2) as diff
  FROM `merchant_user_views`
  WHERE investor_id !=504 AND round(IF(complete_per,complete_per,0)-IF(actual_completed_percentage,actual_completed_percentage,0),2) !=0
  -- GROUP BY merchant_id,investor_id ORDER BY `diff` ASC
  ;
DROP VIEW IF EXISTS merchants_fund_amount_check_view;
CREATE VIEW merchants_fund_amount_check_view AS
  SELECT
  merchant_id,
  Merchant,
  investor_id,
  Investor,
  merchant_completed_percentate,
  round(SUM(amount),2) AS amount,
  round(SUM(invest_rtr),2) AS invest_rtr,
  round(SUM(under_writing_fee),2) AS under_writing_fee,
  round(SUM(pre_paid),2) AS pre_paid,
  round(SUM(commission_amount),2) AS commission_amount,
  round(SUM(up_sell_commission),2) AS upsell_commission_amount,
  round(SUM(total_investment),2) AS total_investment,
  round(SUM(expected_mgmnt_fee_amount),2) AS expected_mgmnt_fee_amount,
  round(SUM(paid_mgmnt_fee),2) AS paid_mgmnt_fee,
  round(SUM(mgmnt_fee_diff),2) AS mgmnt_fee_diff,
  round(SUM(paid_participant_ishare),2) AS paid_participant_ishare,
  round(SUM(user_balance_amount),2) AS user_balance_amount
  FROM `merchant_user_views`
  GROUP BY merchant_id;
DROP VIEW IF EXISTS company_amount_views;
CREATE VIEW company_amount_views AS
  SELECT
  CA.company_id,
  C.name AS Company,
  CA.merchant_id,
  M.name AS Merchant,
  M.funded,
  M.max_participant_fund,
  IF(CA.max_participant,round(CA.max_participant*100/M.max_participant_fund,2),0) AS company_share,
  CA.max_participant
  FROM company_amount  AS CA
  JOIN merchants       AS M  ON M.id = CA.merchant_id
  JOIN users           AS C  ON C.id = CA.company_id;

DROP VIEW IF EXISTS investment_amount_check_view;
CREATE VIEW investment_amount_check_view AS
  SELECT
  MUV.merchant_id,
  MUV.Merchant,
  MUV.investor_id,
  MUV.Investor,
  MUV.merchant_completed_percentate,
  M.factor_rate,
  round(SUM(MUV.amount),2) AS actual_amount,
  round(FLOOR(MUV.amount)) AS floor_amount,
  round(SUM(MUV.amount)-FLOOR(MUV.amount),2) AS diff_amount,
  round(SUM(MUV.invest_rtr),2) AS actual_invest_rtr,
  round(SUM(FLOOR(MUV.amount)*M.factor_rate),2) AS floor_invest_rtr,
  round(SUM(MUV.invest_rtr-FLOOR(MUV.amount)*M.factor_rate),2) AS diff_invest_rtr
  FROM `merchant_user_views` as MUV
  JOIN merchants AS M ON M.id = MUV.merchant_id AND M.sub_status_id IN (1) AND M.label IN (1,2) AND MUV.merchant_completed_percentate<100
  GROUP BY merchant_id,investor_id;
DROP VIEW IF EXISTS investment_amount_grouped_check_view;
CREATE VIEW investment_amount_grouped_check_view AS
  SELECT
  merchant_id,
  Merchant,
  merchant_completed_percentate,
  factor_rate,
  round(sum(actual_amount),2) AS actual_amount,
  round(sum(floor_amount),2) AS floor_amount,
  round(sum(diff_amount),2) AS diff_amount,
  round(sum(actual_invest_rtr),2) AS actual_invest_rtr,
  round(sum(floor_invest_rtr),2) AS floor_invest_rtr,
  round(sum(diff_invest_rtr),2)AS diff_invest_rtr
  FROM `investment_amount_check_view`
  GROUP BY merchant_id;
DROP VIEW IF EXISTS penny_investment_check_view;
CREATE VIEW penny_investment_check_view AS
  SELECT
  id,
  merchant_id,
  Merchant,
  investor_id,
  Investor,
  merchant_completed_percentate,
  amount,
  invest_rtr,
  under_writing_fee,
  pre_paid,
  commission_amount,
  up_sell_commission,
  total_investment,
  expected_mgmnt_fee_amount,
  paid_mgmnt_fee,
  mgmnt_fee_diff,
  paid_participant_ishare,
  user_balance_amount
  FROM `merchant_user_views`
  WHERE amount <= 1 AND amount !=0;
DROP VIEW IF EXISTS investor_ach_transaction_views;
CREATE VIEW investor_ach_transaction_views AS
  SELECT
  IT.id,
  IT.investor_id,
  IT.merchant_id,
  U.name,
  IT.amount,
  IT.transaction_category,
  IT.transaction_type,
  IT.maturity_date,
  IT.date,
  IT.created_at,
  IT.updated_at,
  IT.status,
  IT.transaction_method,
  U.company as company,
  U.investor_type,
  IT.creator_id,
  IT.category_notes,
  IT.account_no,
  'it' as table_name 
  FROM `investor_transactions` AS IT
  LEFT JOIN users AS U ON U.id=IT.investor_id 
UNION
  SELECT
  IA.id,
  IA.investor_id,
  null as merchant_id,
  U1.name,
  CASE WHEN transaction_type='debit' THEN amount
  WHEN transaction_type='same_day_debit' THEN amount
  ELSE (0-amount)
  END AS amount,
  transaction_category,
  CASE WHEN transaction_type='debit'OR transaction_type='same_day_debit' THEN 2
  ELSE 1
  END AS transaction_type,
  null as maturity_date,
  IA.date,
  IA.created_at,
  IA.updated_at,
  2 as status,
  IA.transaction_method,
  U1.company as company,
  U1.investor_type,
  IA.creator_id,
  null as category_notes,
  BD.acc_number as account_no,
  'ach' as table_name
  FROM `investor_ach_requests` AS IA
  LEFT JOIN users AS U1 ON U1.id=IA.investor_id
  LEFT JOIN bank_details AS BD ON BD.id=IA.bank_id
  WHERE IA.order_id !=''
  AND IA.ach_request_status IN (1);
DROP VIEW IF EXISTS `manual_liquidity_log_views`;
CREATE VIEW `manual_liquidity_log_views` AS
  SELECT 
  MLL.date,
  MLL.user_id as investor_id,
  U.name AS Investor,
  U.company as company_id,
  C.name AS Company,
  MLL.liquidity,
  MLL.creator_id,
  MLL.created_at
  FROM `manual_liquidity_logs` AS MLL
  INNER JOIN `users`  AS U ON U.id  = MLL.user_id
  INNER JOIN `users`  AS C ON C.id  = U.company;
DROP VIEW IF EXISTS `manual_r_t_r_balance_log_views`;
CREATE VIEW `manual_r_t_r_balance_log_views` AS
  SELECT 
  MLL.date,
  MLL.user_id as investor_id,
  U.name AS Investor,
  U.company as company_id,
  C.name AS Company,
  MLL.rtr_balance,
  MLL.rtr_balance_default,
  MLL.total,
  MLL.details,
  MLL.creator_id,
  MLL.created_at
  FROM `manual_r_t_r_balance_logs` AS MLL
  INNER JOIN `users`  AS U ON U.id  = MLL.user_id
  INNER JOIN `users`  AS C ON C.id  = U.company;
  
  DROP VIEW IF EXISTS `transaction_views`;
  CREATE VIEW `transaction_views` AS
  SELECT 
  T.id,
  T.date,
  T.merchant_id,
  IF(T.merchant_id,M.name,'') AS Merchant,
  T.amount AS credit,
  0        AS debit,
  T.status,
  T.model,
  T.model_id,
  T.created_by,
  T.updated_by,
  T.created_at,
  T.updated_at
  FROM `transactions`    AS T
  LEFT JOIN `merchants` AS M ON M.id = T.merchant_id
  WHERE amount >= 0
  UNION
  SELECT 
  T.id,
  T.date,
  T.merchant_id,
  IF(T.merchant_id,M.name,'') AS Merchant,
  0           AS credit,
  T.amount*-1 AS debit,
  T.status,
  T.model,
  T.model_id,
  T.created_by,
  T.updated_by,
  T.created_at,
  T.updated_at
  FROM `transactions`    AS T
  LEFT JOIN `merchants` AS M ON M.id = T.merchant_id
  WHERE amount < 0;
  DROP VIEW IF EXISTS `participent_payment_views`;
  CREATE VIEW `participent_payment_views` AS
  SELECT
  T.id,
  T.payment_date AS date,
  IF(T.merchant_id,T.merchant_id,'') AS merchant_id,
  IF(T.merchant_id,M.name,'') AS Merchant,
  IF(IT.investor_id,IT.investor_id,'') AS investor_id,
  IF(IT.investor_id,U.name,'') AS Investor,
  IF(IT.investor_id,U.name,M.name) AS AccountHead,
  T.payment AS amount,
  IF(T.payment>=0,T.payment   ,0) AS credit,
  IF(T.payment<0 ,T.payment*-1,0) AS debit,
  T.status,
  T.payment_type,
  T.transaction_type,
  T.mode_of_payment,
  T.model,
  T.model_id,
  T.reason,
  T.creator_id,
  T.created_at,
  T.updated_at
  FROM `participent_payments`    AS T
  LEFT JOIN `merchants` AS M ON M.id = T.merchant_id
  LEFT JOIN `investor_transactions` AS IT ON IT.id = T.model_id
  LEFT JOIN `users` AS U ON U.id = IT.investor_id;
  DROP VIEW IF EXISTS `payment_investors_views`;
  CREATE VIEW `payment_investors_views` AS
  SELECT
  PI.id,
  PI.merchant_id,
  MUV.Merchant,
  MUV.active_status,
  PI.user_id,
  MUV.Investor,
  MUV.invest_rtr,
  MUV.mgmnt_fee as investor_management_fee,
  PP.payment_date,
  PP.payment,
  PP.final_participant_share,
  PI.investment_id,
  PI.participent_payment_id,
  PI.participant_share,
  PI.actual_participant_share,
  PI.mgmnt_fee,
  PI.participant_share-PI.mgmnt_fee AS net_amount,
  PI.syndication_fee,
  PI.actual_overpayment,
  PI.overpayment,
  PI.balance,
  PI.principal,
  PI.profit
  FROM payment_investors    AS PI
  JOIN participent_payments AS PP  ON PP.id=PI.participent_payment_id
  JOIN merchant_user_views  AS MUV ON MUV.investor_id=PI.user_id AND MUV.merchant_id=PI.merchant_id;

  DROP VIEW IF EXISTS `merchant_liquidity_log_views`;
  CREATE VIEW `merchant_liquidity_log_views` AS
  SELECT
  LIQ.id,
  LIQ.liquidity_change,
  LIQ.member_type,
  LIQ.merchant_id,
  LIQ.member_id,
  LIQ.description,
  LIQ.created_at,
  U.id as user_id,
  U.company,
  U.creator_id,
  LIQ.creator_id as liquidity_creator,
  LIQ.batch_id,
  LIQ.name_of_deal,
  LIQ.investor_id,
  LIQ.final_liquidity,
  LIQ.aggregated_liquidity,
  M.deleted_at as merchant_deleted_at,
  M.name as merchant_name
  FROM liquidity_log  AS LIQ
  JOIN merchants AS M  ON M.id=LIQ.merchant_id AND M.active_status = 1
  JOIN users  AS U ON U.id=LIQ.member_id
  WHERE LIQ.liquidity_change != 0 AND LIQ.member_type = 'investor' ORDER BY LIQ.id DESC;

  DROP VIEW IF EXISTS `liquidity_log_views`;
  CREATE VIEW `liquidity_log_views` AS
  SELECT
  LIQ.id,
  LIQ.liquidity_change,
  LIQ.member_type,
  LIQ.merchant_id,
  LIQ.member_id,
  LIQ.description,
  LIQ.created_at,
  U.id as user_id,
  U.company,
  U.creator_id,
  LIQ.creator_id as liquidity_creator,
  U.name as user_name,
  LIQ.batch_id,
  LIQ.name_of_deal,
  LIQ.investor_id,
  LIQ.final_liquidity,
  LIQ.aggregated_liquidity,
  M.deleted_at as merchant_deleted_at,
  M.name as merchant_name,
  M.label
  FROM liquidity_log  AS LIQ
  LEFT JOIN merchants AS M  ON M.id=LIQ.merchant_id
  LEFT JOIN users  AS U ON U.id=LIQ.member_id
  WHERE LIQ.liquidity_change != 0 AND LIQ.member_type = 'investor' ORDER BY LIQ.id DESC;
