<template>
    <section class="info-sum-row">
      <div class="info-col overview">
        <!-- 2/4 -->
        <div class="content-box bg">
          <rates :data="data" />
        </div>
      </div>
      <div class="info-col detail">
        <!-- 1/4 -->
        <div class="content-box bg">
          <infoCard
            :icon="require('@image/icons/date-icon.svg').default"
            label="Date Funded"
            tooltipText="The date the merchant received their funds "
            :date="data.date_funded"
          >
            <div
              v-for="(item,i) in [
                {
                  icon: '#',
                  label: 'Factor Rate',
                  value: data.factor_rate,
                  color: '#EEECFF',
                  tooltipText: 'The cost of capital represented as a fraction after the 1.'
                },
                {
                  icon: '%',
                  label: 'Annualized ROI',
                  value: data.annualised_rate,
                  color: '#FFF7EC',
                  tooltipText: 'The Return on Investment based on an annual perspective'
                },
                {
                  icon: '%',
                  label: 'Participant Share',
                  value: data.participant_share,
                  color: '#F2FFEC',
                  tooltipText: 'Your participation investment percentage in this deal'
                },
                {
                  icon: '$',
                  label: 'Paid Management Fee',
                  value: data.management_fee_paid,
                  color: '#ECFEFF',
                  tooltipText: 'Fee paid to company for managing business on their behalf'                  
                }
              ]"
              :key="i"
              class="tile-box"
              :class="{last:i==3}"
            >
              <tile
                :icon="item.icon"
                :label="item.label"
                :value="item.value"
                :color="item.color"
                :tooltipText="item.tooltipText"
                type="contain"
              />
            </div>
          </infoCard>
        </div>
      </div>
      <div class="info-col detail">
        <!-- 1/4 -->
        <div class="content-box bg">
          <infoCard
            :icon="require('@image/icons/date-icon.svg').default"
            label="Last Successful Payment Date"
            tooltipText="Last date the merchant made a payment successfully"
            :date="data.last_payment_date"
          >
            <div
              v-for="(item,i) in [
                {
                  icon: '$',
                  label: 'Payment Amount',
                  value: data.payment_amount,
                  color: '#EEECFF',
                  tooltipText: 'Amount of money (in dollars) the merchant agrees to pay back either daily or weekly'
                },
                {
                  icon: '#',
                  label: 'Payments',
                  value: data.pmnts,
                  color: '#FFF7EC',
                  tooltipText: 'Number of payments that the merchant agreed to pay to satisfy advance'
                },
                {
                  icon: '#',
                  label: 'Actual Payment Made',
                  value: data.payment_made,
                  color: '#FFF7EC',
                  tooltipText: 'Number of payments the merchant has made to date'
                },
                {
                  icon: '#',
                  label: 'Payment Remaining',
                  value: data.payment_remaining,
                  color: '#EEECFF',
                  tooltipText: 'Number of remaining payments merchant needs to make to fulfill advance'
                }
              ]"
              :key="i"
              class="tile-box"
              :class="{last:i==3}"
            >
              <tile
                :icon="item.icon"
                :label="item.label"
                :value="item.value"
                :color="item.color"
                :tooltipText="item.tooltipText"
              />
            </div>
          </infoCard>
        </div>
      </div>
      <div class="info-col detail">
        <!-- 1/4 -->
        <div class="content-box bg">
          <infoCard
            :icon="require('@image/icons/rtr-icon.svg').default"
            label="Advance Type"
            tooltipText="Advance Type"
            :date="data.advance_type"
          >
            <div
              v-for="(item,i) in [
                {
                  icon: '%',
                  label: 'Commission',
                  value: data.commission_per,
                  color: '#EEECFF',
                  tooltipText: 'Cost of deal acquisition'
                },
                {
                  icon: '$',
                  label: 'Syndication Fee',
                  value: data.syndication_fee,
                  color: '#FFF7EC',
                  tooltipText: 'One time fee for syndication'
                },
                /*{
                  icon: '$',
                  label: 'Underwriting Fee',
                  value: data.under_writing_fee,
                  color: '#F2FFEC',
                  tooltipText: 'One time fee for underwriting'
                },*/
                {
                  icon: '$',
                  label: 'Total Management Fee',
                  value: data.mgmnt_fee,
                  color: '#ECFEFF',
                  tooltipText: 'Total management fee projected for completed advance'
                }
              ]"
              :key="i"
              class="tile-box"
              :class="{last:i==3}"
            >
              <tile
                :icon="item.icon"
                :label="item.label"
                :value="item.value"
                :color="item.color"
                :tooltipText="item.tooltipText"
              />
            </div>
          </infoCard>
        </div>
      </div>
    </section>
</template>

<script>
import rates from './sub/rates';
import infoCard from './sub/infoCard';
import tile from './sub/infoTile';
export default {
  name: 'info-section',
  data(){
    return {
    }
  },
  components:{
    rates,
    infoCard,
    tile
  },
  props:['data']
}
</script>

<style
  lang="scss"
  scoped
  src="~c/merchantDashboard/infoSection.scss"
></style>