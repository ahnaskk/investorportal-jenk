<template>
    <tbody>
        <tr>
            <!-- <td> -->
            <!-- change ACTIVE to active to enable the toggle view -->
            <!-- <button class="table-add-bt" :class="{active: showRow}" @click="getDetails" v-loader="loading">
                    <icon v-if="!showRow" :icon="['fas','plus']" />
                    <icon v-else :icon="['fas','minus']" />
                </button>
            </td> -->
            <td v-if="data.merchant_id">
                <!-- Merchant -->
               <router-link :to="{path: '/merchants/' + data.merchant_id}"> {{ data.Merchant || '--' }}</router-link>
            </td>
            <td v-else>
                {{ data.Merchant || '--' }}
            </td>
            <td>
                <!--  Funded Date  -->
                {{ data.date_funded || '--' }}
            </td>
            <td>
                <!--  Share (%) -->
                {{ data.share_t || '--' }}
            </td>
            <td>
                <!-- Funded Amount  -->
                {{ data.i_amount || '--' }}
            </td>
            <td>
                <!--  RTR  -->
                {{ data.i_rtr || '--' }}
            </td>
            <td>
                <!--  Management Fee  -->
                {{ data.mgmnt_fee || '--' }}
            </td>
            <!-- <td> -->
                <!--  Commission  -->
                <!-- {{ data.commission_amount || '--' }}
            </td> -->
            <td>
                <!-- Syndication Fee -->
                {{ data.pre_paid || '--' }}
            </td>
            <!-- <td> -->
                <!--  Under Writing Fee  -->
                <!-- {{ data.under_writing_fee || '--' }}
            </td> -->
            <td>
                <!-- Total Invested -->
                {{ data.invested_amount || '--' }}
            </td>
            <!-- <td> -->
                <!--  Participant RTR  -->
                <!-- {{ data.created_at || '--' }}
            </td> -->
        </tr>
        <!-- <tr v-if="showRow" class="table-carrier">
            <td colspan="13">
                <table class="inner-data-table">
                    <thead>
                        <tr>
                            <th>Investor</th>
                            <th>Net Investment</th>
                            <th>Commission</th>
                            <th>Underwriting Fee</th>
                            <th>Prepaid Amount</th>
                            <th>Totals</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(d,i) in details" :key="i+0.1">
                            <td>{{ d.Investor || '--' }}</td>
                            <td>{{ d.amount || '--' }}</td>
                            <td>{{ d.commission_amount || '--' }}</td>
                            <td>{{ d.under_writing_fee || '--' }}</td>
                            <td>{{ d.pre_paid || '--' }}</td>
                            <td>{{ d.total_investment || '--' }}</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr> -->
    </tbody>
</template>

<script>
export default {
    name: 'investment-tbody-toggler',
    data() {
        return {
            showRow: false,
            loading: false,
            details: null
        }
    },
    methods: {
        getDetails() {
            if (this.details) return this.showRow = !this.showRow;
            this.loading = true;
            this.$store.dispatch('api/call', {
                    url: '/investor-details',
                    post: {
                        merchant_id: this.data.merchant_id,
                        sDate: this.sDate,
                        eDate: this.eDate,
                    }
                }).then((r) => {
                    if (r && r.status) {
                        this.$set(this, 'details', r.data.data);
                        this.showRow = !this.showRow;
                    } else return Promise.reject(r);
                })
                .catch(e => {
                    console.log(e);
                }).finally(() => {
                    this.loading = false;
                })
        }
    },
    props: ['data']
}
</script>

<style src="~c/reports/tbodyToggler.scss" lang="scss" scoped></style>