<template>
    <tbody>
        <tr>
            <td>
                <!-- change ACTIVE to active to enable the toggle view -->
                <button
                    class="table-add-bt"
                    :class="{active: showRow}"
                    @click="getDetails"
                    v-loader="loading"
                >
                    <icon v-if="!showRow" :icon="['fas','plus']" />
                    <icon v-else :icon="['fas','minus']" />
                </button>
            </td>
            <td v-if="data.id">
                <!-- Merchant -->
                <router-link :to="{path: '/merchants/' + data.id}">{{ data.name || '--' }}</router-link>
            </td>
            <td v-else>
                {{ data.name || '--' }}
            </td>
            <td>
                <!-- Merchant Id  -->
                {{ data.id || '--' }}
            </td>
            <td>
                <!--  Funded Date  -->
                {{ data.date_funded || '--' }}
            </td>
            <td>
                <!-- Debited -->
                {{ data.debited || '--' }}
            </td>
            <td>
                <!-- Total Payments  -->
                {{ data.participant_share || '--' }}
            </td>
            <td>
                <!--  Management Fee  -->
                {{ data.mgmnt_fee || '--' }}
            </td>
            <td>
                <!--  Net amount  -->
                {{ data.net_participant_payment || '--' }}
            </td>
            <!-- <td> -->
                <!-- Principal -->
                <!-- {{ data.principal || '--' }}
            </td> -->
            <!-- <td> -->
                <!-- Profit -->
                <!-- {{ data.profit || '--' }}
            </td> -->
            <!-- <td> -->
                <!--  Last Rcode  -->
                <!-- {{ data.code || '--' }}
            </td> -->
            <td>
                <!--  Last Payment Date  -->
                {{ data.last_payment_date || '--' }}
            </td>
            <!-- <td> -->
                <!--  Last Payment Amount  -->
                <!-- {{ data.last_payment_amount || '--' }}
            </td>
            <td> -->
                <!--  Participant RTR  -->
                <!-- {{ data.participant_rtr || '--' }}
            </td>
            <td> -->
                <!--  Participant RTR Balance  -->
                <!-- {{ data.participant_rtr_balance || '--' }}
            </td> -->
        </tr>
        <tr
            v-if="showRow"
            class="table-carrier"
        >
            <td colspan="15">
                <table class="inner-data-table">
                    <thead>
                        <tr>
                            <th>Participant</th>
                            <th>Date</th>
                            <th>Debited</th>
                            <th>Participant Share</th>
                            <th>Management fees</th>
                            <th>Net amount</th>
                            <!-- <th>Rcode</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(d,i) in details"
                            :key="i+0.1"
                        >
                            <td>{{ d.participant || '--' }}</td>
                            <td>{{ d.date || '--' }}</td>
                            <td>{{ d.debited || '--' }}</td>
                            <td>{{ d.participant_share || '--' }}</td>
                            <td>{{ d.management_fee || '--' }}</td>
                            <td>{{ d.net_amount || '--' }}</td>
                            <!-- <td>{{ d.rcode || '--' }}</td> -->
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</template>

<script>
    export default {
        name: 'tbody-toggler',
        data() {
            return {
                showRow: false,
                loading: false,
                details: null,
                req:{
                    sDate: '',
                    eDate: ''
                }
            }
        },
        methods:{
            init(){
                const generateDate = (d) => {
                    if (d) {
                        const
                            dt = new Date(d),
                            y = dt.getFullYear(),
                            m = dt.getMonth(),
                            dy = dt.getDate()
                        return `${y}-${m+1}-${dy}`
                    } else return null;
                }
                Object.keys(this.req).forEach(k=>{
                    if(this[k]){
                        this.req[k] = generateDate(this[k]);
                    }
                })
            },
            getDetails(){
                if(this.details) return this.showRow = !this.showRow;
                this.loading = true
                const post = {
                    merchant_id: this.data.id
                },
                { sDate, eDate } = this.req
                if(sDate) post.sDate = sDate
                if(eDate) post.eDate = eDate
                this.$store.dispatch('api/call',{
                    url: '/payment-report-details',
                    post
                }).then((r) => {
                    if(r&&r.status){
                        this.$set(this,'details',r.data);
                        this.showRow = !this.showRow;
                    }else return Promise.reject(r);
                })
                .catch(e=>{
                    console.log(e);
                }).finally(()=>{
                    this.loading = false;
                })
            }
        },
        created(){
            this.init()
        },
        props:['data','sDate','eDate']
    }
</script>

<style
    src="~c/reports/tbodyToggler.scss"
    lang="scss"
    scoped
></style>