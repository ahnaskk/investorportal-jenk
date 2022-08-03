<template>
    <div class="investment-sum-card">
        <!-- projected portfolio value -->
        <div class="data-box">
            <div class="amounts">
                <div class="left">
                    <div class="icon">$</div>
                    <!-- <Progress :transitionDuration="500" :radius="37" :strokeWidth="10" value="50" :strokeColor="'#727BED'">
                        <div class="content blue">{{ }}</div>
                        <div class="circle"></div>
                    </Progress> -->
                </div>
                <div class="right">
                    <div class="title-wrapper">
                        <h3 class="title">Projected Portfolio Value</h3>
                        <tooltip
                            tooltipText="Anticipated distributions combined with any liquidity in your account"
                            class="right"
                            width="200"
                        />
                    </div>
                    <h2 class="amount">
                        {{ data.portfolio_value }}
                    </h2>
                </div>
            </div>
        </div>
        <!-- rtr & anticipated rtr -->
        <div class="data-box">
            <div class="amounts">
                <div class="left">
                    <div class="icon">$</div>
                    <!-- <Progress :transitionDuration="500" :radius="37" :strokeWidth="10" :value="percent(data.anticipated_rtr , data.total_rtr)" :strokeColor="'#4EBE67'">
                        <div class="content green">{{ percent(data.anticipated_rtr , data.total_rtr)}} %</div>
                    </Progress> -->
                    <!-- <div class="title-wrapper">
                        <h3 class="title">Total Invested</h3>
                        <tooltip
                            tooltipText="The Total amount of principal you’ve invested since inception of your portfolio"
                            class="left"
                            width="200"
                        />
                    </div>
                    <h2 class="amount">
                        {{ data.invested_amount }}
                    </h2> -->
                </div>
                <div class="right">
                    <div class="title-wrapper">
                        <h3 class="title">Anticipated RTR</h3>
                        <tooltip
                            tooltipText="The sum of all distributions you're expected to receive"
                            class="right"
                            width="200"
                            :bg="'#727BED'"
                        />
                    </div>
                    <h2 class="amount">
                        {{ data.anticipated_rtr }}
                    </h2>
                </div>
            </div>
        </div>
        <!-- <div class="data-box ">
            <div class="amounts">
                <div class="left">
                    <div class="icon">$</div> -->
                    <!-- <Progress :transitionDuration="500" :radius="37" :strokeWidth="10" :value="percent(data.c_invested_amount,data.invested_amount)" :strokeColor="'#F86995'">
                        <div class="content pink">{{ percent(data.c_invested_amount,data.invested_amount) }} %</div>
                    </Progress> -->
                    <!-- <div class="title-wrapper">
                        <h3 class="title">Cash to Date (CTD)</h3>
                        <tooltip
                            tooltipText="Cash to Date, represents the sum of all cash collected from the inception of your portfolio"
                            class="left"
                            width="200"
                        />
                    </div>
                    <h2 class="amount">
                        {{ data.ctd }}
                    </h2> -->
                <!-- </div>
                <div class="right">
                    <div class="title-wrapper">
                     <h3 class="title">Current Invested</h3>
                    <tooltip
                        tooltipText="Total amount of funds that is invested as of today"
                        class="right"
                        width="200"
                    />
                    </div>
                    <h2 class="amount">
                        {{ data.c_invested_amount }}
                    </h2>
                </div>
            </div>
        </div> -->
        <!-- <div class="data-box profit">
            <div class="amounts">
                <div class="left">
                    <div class="icon">$</div> -->
                    <!-- <Progress :transitionDuration="500" :radius="37" :strokeWidth="10" value="50" :strokeColor="'#727BED'">
                        <div class="content blue">{{ }}</div>
                        <div class="circle"></div>
                    </Progress> -->
                <!-- </div>
                <div class="right">
                    <div class="title-wrapper">
                        <h3 class="title">Profit</h3>
                        <tooltip
                            tooltipText="The sum of all earnings from the inception of your portfolio"
                            class="right"
                            width="200"
                        />
                    </div>
                    <h2 class="amount">
                        {{ data.profit }}
                    </h2>
                </div>
            </div>
        </div> -->
        <!-- total/current invested -->
        <!-- profit/ctd -->

        <!-- profit -->
        <!-- <div class="data-box profit">
            <figure class="icon">
                <img src="@image/icons/dash-profit.svg" alt="">
            </figure>
            <div class="text">
                <h3 class="title">Total Profit</h3>
                <h2 class="amount">
                    {{ data.profit }}
                </h2>
            </div>
        </div> -->
    </div>
</template>

<script>
    import tooltip from '@c/tooltip';
    import Progress from "easy-circular-progress";
    import VueApexCharts from 'vue-apexcharts';
    const strip = v => +v.toString().replace(/[^0-9\.]/g,'');
    export default {
        name: 'investment-sum',
        props:[
            'data'
        ],
        components: {
            tooltip,
            apexchart: VueApexCharts,
            Progress
        },
        data(){
            return{
                colors:['#FFC646','#50BFFE','#4EBE67','#F86995','#727BED'],
                series:[45],
                updateKey:1,
                labels:['a']
            }

        },
        methods:{
            percent(v1,v2){
                [v1,v2] = [ strip(v1), strip(v2) ];
                return (v1/v2*100).toFixed(2);
            }
        },
        mounted(){
      
        },
        computed:{
            chartOptions(){
                return {
                    colors: this.colors,
                    chart: {
                        height: 350,
                         toolbar: {
                            show:false
                         },
                        type: 'radialBar',
                        zoom: {
                            enabled:false
                        }
                    },
                    radialBar: {
                        startAngle: 0,
                        endAngle: 360,
                        offsetX: 0,
                        offsetY: 0,
                    },
                    legend:{
                        show:true,
                        showForSingleSeries: false,
                        position: 'right',
                        labels:{
                            colors:["#4D4F5C80"]
                        },
                        itemMargin: {
                            horizontal: 20,
                            vertical: 0
                        },
                    },
                    markers: {
                        size: 9,
                        colors: this.colors,
                        strokeWidth: 4,
                        strokeOpacity: [1],
                        fillColors: ["#fff"],
                        fillOpacity: [0],
                        shape: "circle",
                        radius: 2,
                        offsetX: 0,
                        offsetY: 0,
                        showNullDataPoints: true,
                        hover: {
                            size: undefined,
                            sizeOffset: 3
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        width:4,
                        lineCap:'round'
                    },
                    tooltip: {
                    },
                    fill: {
                        colors: this.colors,
                    },
                    labels:this.labels,
                    dropShadow: {
                        enabled: true,
                        top: 0,
                        left: 0,
                        blur: 3,
                        opacity: 0.5
                    }
                }
            },
            anticipatedPercentage(){
                return null
            }
        }
    }
</script>

<style
    lang="scss"
    scoped
    src="~c/dashboard/investmentSum.scss"
></style>