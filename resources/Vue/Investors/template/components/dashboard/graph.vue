<template>
    <div class="outer-wrapper">
        <preloader v-if="loading && !chartData" />
        <div class="title-wrapper">
            <h3>Graph</h3>
        </div>
        <div class="graph-container">
            <apexchart
                v-if="chartOptions && chartData"
                type="area"
                height="350"
                :options="chartOptions"
                :series="chartData.dataset"
                :key="updateKey"
            />
        </div>
    </div>
</template>

<script>
    import VueApexCharts from 'vue-apexcharts';
    import { mapState } from 'vuex';
    import moneyFormatter from '@/filters/money';
    export default {
        name: 'graph-component',
        data() {
            return {
                gradient:{
                    shadeIntensity: 1,
                    inverseColors: false,
                    opacityFrom: 0.55,
                    opacityTo: 0.05,
                    stops: [0, 70, 100, 100]
                },
                colors:['#A3A1FB','#54D8FF','#9AF57F'],
                updateKey: 1,
                series: [
                    {
                        name: 'Net Investment',
                        data: [600, 40, 28, 51, 42, 109, 100]
                    },
                    {
                        name: 'RTR',
                        data: [11, 32, 45, 32, 34, 340, 41]
                    },
                    {
                        name: 'CTD',
                        data: [11, 32, 45, 32, 800, 52, 41].reverse()
                    }
                ]
            }
        },
        computed:{
            ...mapState('api',{
                chartData: s => s.dashboardGraph ? s.dashboardGraph.apexChartData : null,
                loading: s=> s.loading.dashboardGraph
            }),
            chartOptions(){
                const chartData = this.chartData;
                if(!chartData) return null;
                else return {
                    colors: this.colors,
                    chart: {
                        height: 350,
                         toolbar: {
                            show:false
                         },
                        type: 'area',
                        zoom: {
                            enabled:false
                        }
                    },
                    legend:{
                        show:true,
                        showForSingleSeries: false,
                        position: 'top',
                        markers: {
                            width: 18,
                            height: 8,
                            radius: 12,
                            customHTML: undefined,
                            offsetX: 200,
                            offsetY: 0,
                            radius:8
                        },
                        labels:{
                            colors:["#4D4F5C80"]
                        },
                        itemMargin: {
                            horizontal: 20,
                            vertical: 0
                        },
                    },
                    markers: {
                        size: 4,
                        colors: this.colors,
                        strokeWidth: 2,
                        strokeOpacity: [1],
                        fillColors: ["#fff"],
                        fillOpacity: [1],
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
                        curve: 'smooth',
                        width:2
                    },
                    xaxis: {
                        type: 'category',
                        categories: chartData.xAxisData
                    },
                    yaxis:{
                        labels:{
                            style:{
                                fontWeight: 600
                            },
                            formatter: (v) => moneyFormatter(v)
                        }
                    },
                    tooltip: {
                    },
                    fill: {
                        type: 'gradient',
                        colors: this.colors,
                        gradient: this.gradient
                    }
                }
            }
        },
        components: {
            apexchart: VueApexCharts
        },
        methods:{
            on_create(){
                this.$store.dispatch('api/getData',{
                    force: false,
                    url: '/investor-chart',
                    field: 'dashboardGraph',
                    transformer: this.apiDataTransformer
                })
                .catch(e=>{
                    this.error = true;
                    this.errorMsg = e.msg || e.message || 'Something went wrong! Please try again later';
                })
            },
            apiDataTransformer(d){
                const xAxisData = Object.values(d.x_data);
                const
                    rtr = [],
                    ctd = [],
                    funded = [];
                d.chart_data.forEach(el=>{
                    rtr.push(el.rtr_month);
                    ctd.push(el.ctd_month);
                    funded.push(el.funded);
                });
                const dataset = [
                    { name: 'RTR', data: rtr },
                   /* { name: 'CTD', data: ctd },*/
                    { name: 'Net Investment', data: funded }
                ];
                d.apexChartData = { dataset, xAxisData };
            }
        }
    }
</script>

<style
    lang="scss"
    scoped
    src="~c/dashboard/graph.scss"
></style>