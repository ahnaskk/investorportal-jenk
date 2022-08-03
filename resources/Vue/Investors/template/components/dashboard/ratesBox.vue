<template>
    <div class="rates">
        <ul class="invst-info list rate">
            <li
                class="info-li"
                v-for="(item,i) in [
                    /*{
                        icon: '%',
                        title: 'Blended ROI',
                        field: 'blended_rate',
                        tooltip: 'Annualized ROI rate of your current portfolio',
                        width: 200,
                        bg:'#EEECFF'
                    },*/
                    {
                        icon: '%',
                        title: 'Anticipated ROI',
                        field: 'roi',
                        tooltip: '',
                        width: 200,
                        bg:'#FFF7EC'
                    },
                    {
                        icon: '%',
                        title: 'Average Completion',
                        field: 'average_completion',
                        tooltip: '',
                        width: 200,
                        bg:'#F2FFEC'
                    },
                   /* {
                        icon: '%',
                        title: 'Default Rate',
                        field: 'default_percentage',
                        tooltip: 'The loss of principal investment divided by your total invested amount',
                        width: 200,
                        bg:'#F2FFEC'
                    },*/
                    {
                        icon: '$',
                        title: 'Paid to Date',
                        field: 'paid_to_date',
                        tooltip: 'Total of all distributions paid out',
                        width: 200,
                        bg:'#ECFEFF'
                    }
                ]"
                :key="i"
            >
                <div class="rate-box">
                    <div class="icon" :style="{'background-color':item.bg}">
                        {{ item.icon }}
                    </div>
                    <div class="text">
                        <div class="field-title">
                            <h3 class="title">
                                {{ item.title }}
                            </h3>
                            <tooltip
                                v-if="item.tooltip"
                                :tooltipText="item.tooltip"
                                class="left"
                                :width="item.width"
                            />
                        </div>
                        <h2 class="amount">
                            {{
                                data[item.field] || '--'
                            }}
                        </h2>
                    </div>
                </div>
                <span class="hr" ></span>
            </li>
        </ul>
    </div>
</template>

<script>
    import tooltip from '@c/tooltip'
    export default {
        name: 'rates-box',
        props:['data'],
        components:{
            tooltip
        }
    }
</script>

<style
    lang="scss"
    scoped
    src="~c/dashboard/ratesBox.scss"
></style>