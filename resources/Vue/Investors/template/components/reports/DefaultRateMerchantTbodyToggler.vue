<template>
    <tbody>
        <tr>
            <td>
                <!-- Id -->
                {{ data.id || '--' }}
            </td>
            <td v-if="data.id">
                <!-- Merchant  -->
                <router-link :to="{path: '/merchants/' + data.id}">{{ data.name || '--' }}</router-link>
            </td>
            <td v-else>
                {{ data.name || '--' }}
            </td>
            <td>
                <!-- Default Date -->
                {{ data.date_funded || '--' }}
            </td>
            <td>
                <!-- Default Date -->
                {{ data.last_status_updated_date || '--' }}
            </td>
            <td>
                <!-- Default Invested Amount  -->
                {{ data.default_amount || '--' }}
            </td>
            <td>
                <!-- Default RTR Amount  -->
                {{ data.investor_rtr || '--' }}
            </td>
        </tr>
    </tbody>
</template>

<script>
export default {
    name: 'default-rate-merchant-tbody-toggler',
    data() {
        return {
            showRow: false,
            loading: false,
            details: null
        }
    },
    methods: {
    },
    props: ['data']
}
</script>

<style src="~c/reports/tbodyToggler.scss" lang="scss" scoped></style>