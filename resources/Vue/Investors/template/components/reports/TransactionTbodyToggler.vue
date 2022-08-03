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
            <td>
                <!--  Category  -->
                {{ data.transaction_category || '--' }}
            </td>
            <td>
                <!--  Method  -->
                {{ data.TransactionMethod || '--' }}
            </td>
            <td>
                <!--  Type  -->
                {{ data.TransactionType || '--' }}
            </td>
            <td>
                <!-- Amount -->
                {{ data.amount || '--' }}
            </td>
            <td>
                <!-- Date  -->
                {{ data.date || '--' }}
            </td>
            <!-- <td> -->
                <!--  Account  -->
                <!-- {{ data.account_no || '--' }}
            </td> -->
        </tr>
        <!-- <tr v-if="showRow" class="table-carrier">
            <td colspan="5">
                <table class="inner-data-table">
                    <thead>
                        <tr>
                            <th>Investor</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(d,i) in details" :key="i+0.1">
                            <td>{{ d.name || '--' }}</td>
                            <td>{{ d.amount || '--' }}</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr> -->
    </tbody>
</template>

<script>
export default {
    name: 'transaction-tbody-toggler',
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
                    url: '/transaction-details',
                    post: {
                        batch: this.data.batch,
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