<template>
    <div class="outer-wrapper">
        <preloader v-if="loading && !chartData" />
        <div class="title-wrapper">
            <h3>Invested Industries</h3>
            <!-- <a>View All</a> -->
        </div>
        <div class="graph-container">
            <apexchart
                v-if="chartOptions && chartData"
                type="donut"
                width="100%"
                :key="updateKey2"
                :options="chartOptions"
                :series="series"
                class="radial"
            />
            <div class="legend">
                <div class="legend-wrapper"
                v-for="(label,index) in graphVars"
                :key="index"
                :class="{'strike-off':hiddenIndexes.includes(label.index)}"
                @click.prevent="toggleLegend(label)"
                >
                    <span class="indication"
                        :style="{'border-color' : colorsMask[label.index]}"
                    >
                    </span>
                    <span>{{label.status_name}}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import VueApexCharts from 'vue-apexcharts';
    import { mapState } from 'vuex';
    import moneyFormatter from '@/filters/money';
    export default {
        name: 'radial-graph-component',
        props:[
            'data'
        ],
        data() {
            return {
                gradient:{
                    shadeIntensity: 1,
                    inverseColors: false,
                    opacityFrom: 0.55,
                    opacityTo: 0.05,
                    stops: [0, 70, 100, 100]
                },
                colorsMask:['#FFC646','#50BFFE','#4EBE67','#F86995','#727BED','#e75a5a','#58508d','#bc5090'],
                colors:['#FFC646','#50BFFE','#4EBE67','#F86995','#727BED','#e75a5a','#58508d','#bc5090'],
                updateKey: 1,
                labels:null,
                series:null,
                graphVars:[],
                legend:{},
                hiddenIndexes:[]
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
                        width: 600,
                        height:'auto',
                         toolbar: {
                            show:false
                         },
                        type: 'radialBar',
                        zoom: {
                            enabled:false
                        }
                    },
                    plotOptions: {
                        pie: {
                            startAngle: 0,
                            endAngle: 360,
                            offsetX: 0,
                            offsetY: 0,
                            hollow:{
                                margin: 5,
                                size: '10%',
                            },
                            dataLabels: {
                                value: {
                                fontSize: '16px',
                            },
                        enabled: true,
                        formatter: function (val) {
                        return val.toFixed(2) + "%"
                        },
                    
                    },
                        }
                    },
                    legend:{
                        show:false,
                        showForSingleSeries: false,
                        position: 'right',
                        labels:{
                            colors:["#4D4F5C80"]
                        },
                         onItemClick: function(e){
                            e.preventDefault(); 
                         },
                        height:'auto',
                        itemMargin: {
                            horizontal: 20,
                            vertical: 0
                        },
                        labels: {
                            colors: '#5F5F9A',
                        },
                        markers: {
                            size: 10,
                            strokeColor: this.colors,
                            strokeWidth: 4,
                            strokeOpacity: [1],
                            fillColors: ['#fff','#fff','#fff','#fff','#fff','#fff','#fff','#fff','#fff'],
                            fillOpacity: [1],
                            shape: "circle",
                            radius: 12,
                            offsetX: 0,
                            offsetY: 0,
                            showNullDataPoints: true,
                            hover: {
                                size: undefined,
                                sizeOffset: 3
                            }
                        },
                    },
                    markers: {
                        size: 4,
                        colors: this.colors,
                        strokeWidth: 9,
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
                        enabled: false,
                        formatter: function (val) {
                        return val.toFixed(2) + "%"
                        },
                    
                    },
                    stroke: {
                        curve: 'smooth',
                        width:6,
                        lineCap:'round'
                    },
                    width:10,
                    xaxis: {
                        type: 'category',
                        categories: chartData.xAxisData
                    },
                    tooltip: {
                        y: {
                            formatter: function(v) {
                                return moneyFormatter(v)
                            }
                        }
                    },
                    fill: {
                        colors: this.colors,
                    },
                    labels:this.labels,
                    series:this.series
                }
            }
        },
        components: {
            apexchart: VueApexCharts
        },
        methods:{
            on_create(){
                this.apiDataTransformer()
            },
            apiDataTransformer(){
                const
                    label = [],
                    value = [],
                    graphVars = [];
                let index = 0;
                this.data.forEach(el=>{
                    el['index'] = index;
                    graphVars.push(el);
                    value.push(el.current_invested_amount);
                    label.push(el.status_name);
                    this.legend['legend'+index] = false;
                    index ++ 
                });
                this.$set(this, 'labels' ,label)
                this.$set(this, 'series' ,value)
                this.$set(this,'graphVars',graphVars)
                this.updateKey2 ++
            },
            toggleLegend(el){
                let legendState = this.legend['legend'+el.index];
                if(!legendState && !this.hiddenIndexes.includes(el.index)){
                    this.hiddenIndexes.push(el.index)
                }
                else{
                    let currIndex = this.hiddenIndexes.indexOf(el.index)
                    this.hiddenIndexes.splice(currIndex,1)
                }
                let labels =[],colors = [],series = []
                this.graphVars.forEach(item =>{
                    
                    if(this.hiddenIndexes.includes(item.index)){
                        //Clicked to remove item
                        this.series.splice(item.index,1,0);
                    }
                    else{
                        this.series.splice(item.index,1,item.current_invested_amount);
                    }
                }) 
                //toggle
                this.legend['legend'+el.index] = !this.legend['legend'+el.index]
            }
        },
    }
</script>

<style
    lang="scss"
    scoped
    src="~c/dashboard/radial-graph.scss"
></style>