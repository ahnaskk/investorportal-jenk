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
                    stops: [0, 100]
                },
                colors:['#FA5440','#54D8FF','#9AF57F'],
                updateKey: 1,
            }
        },
        computed:{
            ...mapState('api',{
                chartData: s => s.merchantGraph ? s.merchantGraph.apexChartData : null,
                loading: s=> s.loading.merchantGraph
            }),
            chartOptions(){
                const chartData = this.chartData;
                if(!chartData) return null;
                return {
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
            apiDataTransformer(d){
                const xAxisData = Object.values(d.x_data);
                const
                    funded = [];
                d.chart_data.forEach(el=>{
                    funded.push(el.funded);
                });
                const dataset = [
                    { name: 'Net Investment', data: funded }
                ];
                d.apexChartData = { dataset, xAxisData };
            }
        },
        created(){
            this.$store.dispatch('api/getData',{
                force: false,
                url: '/merchant-graph',
                field: 'merchantGraph',
                transformer: this.apiDataTransformer
            })
            .catch(e=>{
                this.error = true;
                this.errorMsg = e.msg || e.message || 'Something went wrong! Please try again later';
            })
        }
    }
</script>

<style
    lang="scss"
    scoped
    src="~c/dashboard/graph.scss"
></style>