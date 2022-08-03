<template>
    <div class="details-wrapper">

        <h1 class="title">EXHIBIT B</h1>
        <h2 class="title">PARTICIPATION AGREEMENT</h2>
        <p>
            THIS IS THE OFFER AS DESCRIBED IN THE PARTICIPATION AGREEMENT DATED <b>{{
                data.participant_date.split(' ')[0]
            }} </b>
            BETWEEN <b>{{ data.participant }}</b> AND <b>VELOCITY GROUP USA INC.</b>. RELATING TO A MERCHANT AGREEMENT
            DATED <br><b>{{
                data.date_en.split(' ')[0]
            }} </b> BETWEEN LEAD AND <b>{{ data.merchant }}</b>.
        </p>
        <p>
            WHEREAS THE LEAD HAS BEEN REQUESTED BY CLIENT TO PURCHASE CERTAIN FUTURE RECEIVABLES AT THE DESCRIBED TOTAL
            CONTRACT PURCHASE PRICE ("PENDING PURCHASE"), NOW AND THEREFORE THE LEAD HEREBY OFFERS TO SELL TO
            PARTICIPANT A CERTAIN PERCENTAGE INTEREST IN THE PENDING PURCHASE ("PARTICIPATION PERCENT") AS DESCRIBED
            BELOW.
        </p>
        <p>
            PLEASE RETURN A SIGNED COPY OF THIS OFFER TO US, INDICATING WHETHER YOU ACCEPT THIS OFFER. IF YOU ACCEPT THE
            OFFER, PLEASE BE PREPARED TO PAY PARTICIPANT'S PURCHASE PRICE WITHIN TWENTY-FOUR (24) HOURS OF YOUR
            EXECUTION OF THIS FORM OF PARTICIPATION OR YOU WILL LOSE YOUR PARTICIPATION AGREEMENT, AS DESCRIBED IN THE
            PARTICIPATION AGREEMENT.
        </p>
        
        <table>
            <thead>
                <tr>
                    <td width="50%">
                        <label>
                            By :
                        </label>
                        {{ 'VELOCITY GROUP USA INC.' }}
                    </td>
                    <td><label>Date :</label>
                        {{ data.date_en.split(' ')[0] }}
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><b>Funder (Lead) Name</b></td>
                    <td>
                        {{ data.business_en_name }}
                    </td>
                </tr>
                <tr>
                    <td><b>Merchant DBA</b></td>
                    <td>
                        {{ data.participant }}
                    </td>
                </tr>
                <tr>
                    <td><b>Legal Name</b></td>
                    <td>{{ data.business_en_name }}</td>

                </tr>
                <tr>
                    <td><b>Merchant Funding Date:</b></td>
                    <td>
                        {{ data.date_funded }}
                    </td>
                </tr>
                <tr>
                    <td><b>Merchant Cash Advance (Yes/No)</b></td>
                    <td>
                        {{ data.mca }}
                    </td>
                </tr>
                <tr>
                    <td><b>Credit Card Sales Split (Yes/No)</b></td>
                    <td>
                        {{ data.credit_card }}
                    </td>
                </tr>
                <tr>
                    <td><b>Total Contract Purchase Price (Net Investment Amount)</b></td>
                    <td>{{ data.funded }}</td>
                </tr>
                <tr>
                    <td><b>Total Contract Receivable ("RTR")</b></td>
                    <td>{{ data.rtr }}</td>

                </tr>
                <tr>
                    <td><b>Factor Rate</b></td>
                    <td>{{ data.factor_rate }}</td>
                </tr>
                <tr>
                    <!--         <td width="50%"><b>Daily  Payment:</b></td> -->
                    <td><b>Contract Payment Amount</b></td>
                    <td> {{ data.daily_payment }} </td>
                </tr>
                <tr>
                </tr>
                <tr>
                    <td><b>Payment Interval (Daily or Weekly):</b></td>
                    <td>
                        {{ data.advance_type.split('_').join(' ') }}
                    </td>
                </tr>
                <tr>
                    <td><b>Number of Payments</b></td>
                    <td>{{ data.pmnts }}</td>
                </tr>
                <tr>
                    <td><b>Estimated Term (Months)</b></td>
                    <td>{{ data.estimated_term_months }}</td>
                </tr>

                <tr>
                    <td><b>Upfront Broker Commission $</b></td>
                    <td>{{ data.upfront_commission }}</td>
                </tr>
                 <tr>
                    <td><b>Upfront Broker Commission %</b></td>
                    <td>{{ data.upfront_commission_per }}</td>
                </tr>

                 <tr>
                    <td><b>Management Fee $</b></td>
                    <td>{{ data.management_fee }}</td>
                </tr>
                <tr>
                    <td><b>Management Fee %</b></td>
                    <td>{{ data.management_fee_per }}</td>
                </tr>
                <tr>
                    <td><b>Syndication Fee $</b></td>
                    <td>{{ data.m_syndication_fee }}</td>
                </tr>
                <tr>
                    <td><b>Syndication Fee %</b></td>
                    <td>{{ data.m_syndication_fee_per }}</td>
                </tr>
                <tr>
                    <td><b>Underwriting Fee $</b></td>
                    <td>{{ data.underwriting_fee }}</td>
                </tr>
                <tr>
                    <td><b>Underwriting Fee %</b></td>
                    <td>{{ data.underwriting_fee_per }}</td>
                </tr>
                <tr>
                    <td><b>Participant Purchase Price (Funding Amount)</b></td>
                    <td>{{ data.participant_funded_amount }}</td>
                </tr>
                <tr>
                    <td><b>Participant's percentage of Contract</b></td>
                    <td>{{ data.participant_percent }}</td>
                </tr>
                <tr>
                    <td> <b>Participant RTR (Gross)</b></td>
                    <td>{{ data.rtr_gross }}</td>
                </tr>
                <tr>
                    <td><b>Participant RTR (Net)</b></td>
                    <td>{{ data.rtr_net }}</td>
                </tr>
                <tr>
                    <td><b> Participant Payment Amount</b></td>
                    <td>{{ data.payment_amount }}</td>
                </tr>
                <tr>
                    <td><b> Total Amount Due From Participant</b></td>
                    <td>{{ data.duetotal }}</td>
                </tr>
            </tbody>
        </table>

        <p style="margin-top: 50px;">
            THE PARTICIPATION <span title="Return on Investment"><b>ROI</b></span> OFFERED HEREBY IS HIGHLY SPECULATIVE AND INVOLVES A HIGH DEGREE OF RISK. THE LEAD
            DOES NOT MAKE ANY REPRESENTATION REGARDING THE SUITABILITY AND/OR PROSPECTS OF THIS INVESTMENT APART FROM
            THOSE EXPLICITLY OUTLINED IN THE PARTICIPATION AGREEMENT. THIS PARTICIPATION SHOULD NOT BE PURCHASED BY ANY
            INVESTOR WHO CANNOT AFFORD THE LOSS OF THEIR ENTIRE INVESTMENT. </p>
        <p>

            THE INFORMATION CONTAINED IN THIS 'EXHIBIT B: PARTICIPATION AGREEMENT' IS HIGHLY CONFIDENTIAL INFORMATION AS
            DEFINED IN THE CONFIDENTIALITY AGREEMENT EXECUTED ON <b>{{data.date_en.split(' ')[0]}} </b> BETWEEN Velocity Group USA Inc. AND
            <b>{{data.participant}}</b>, WHICH IS INCORPORATED HEREIN BY REFERENCE AND MADE A 'EXHIBIT B'.
        </p>
        <p> ACKNOWLEDGE AND ACCEPTED UNDER THE TERMS AND CONDITIONS OF THE PARTICIPATION AGREEMENT REFERENCED ABOVE.
        </p>

    </div>
</template>

<script>
    export default {
        name: 'agreement-details',
        props:['data']
    }
</script>

<style
    lang="scss"
    scoped
>
    @import '~/mixins.scss';
    .title{
        text-align: center;
        font-size: 30px;
        @include md{
            font-size: 20px;
        }
    }
    .details-wrapper{
        padding: 25px 30px;
        color: #515151;
    }
    table{
        border-spacing: 0;
        border-collapse: collapse;
        width: 100%;
    }
    td{
        padding: 10px 15px;
        border: 1px solid #ddd;
        line-height: inherit;
    }
    p{
        margin: 25px 0 50px;
        font-size: inherit;
        font-size: inherit;
        line-height: inherit;
    }
</style>