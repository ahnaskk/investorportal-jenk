<template>
    <div>
        <div class="filter-wrapper">
            <form class="fields">
                <div class="quarter">
                    <label for="">From Date</label>
                    <datePicker v-model="filter.date_start"/>
                </div>
                <div class="quarter">
                    <label for="">To Date</label>
                    <datePicker v-model="filter.date_end" />
                </div>
                <div class="quarter">
                    <label for="">Group by <pre>*</pre></label>
                    <v-select 
                        :options="groupBy"
                        placeholder="Select Group"
                        v-model="filter.attribute"
                    />
                    <label class="error" v-if="errors && !loading && errors.attribute"> {{ errors.attribute }} </label>
                </div>
                <div class="quarter">
                    <label for="">Value <pre>*</pre></label>
                     <v-select 
                        :options="value"
                        placeholder="Select Value"
                        v-model="filter.graph_value"
                    />
                    <label class="error" v-if="errors && !loading && errors.graph_value"> {{ errors.graph_value }} </label>
                </div>
                <div class="quarter">
                    <label for="">Label <pre>*</pre></label>
                    <v-select 
                        :options="label"
                        placeholder="Select Label"
                        v-model="filter.label"
                    />
                    <label class="error" v-if="errors && !loading && errors.label"> {{ errors.label }} </label>
                </div>
            </form>
            <div class="controls">
                <button  class="g loader" @click="submit" :disabled="isValid" v-loader="loading">Update Graph</button>
                <button  class="r loader" @click="clearFilter">Clear Filters</button>
                <button  class="b loader" @click="download" v-loader="dLoading" :disabled="isValid">Download</button>
            </div>
        </div>
        <hr class="hr">
        <preloader v-if="loading"></preloader>
        <horizontalGraph 
            v-else
            :data = graph
        />
    </div>
</template>

<script>
import preloader from "@c/preloader"
import { cloneDeep } from "lodash";
import selectBox from '@c/selectBox'
import datePicker from '@c/datePicker'
import horizontalGraph from "@c/horizontalGraph"
import { fieldsAreValid , fieldErrors } from './graph/validation'
import { drawGraph } from './graph/graph'
import {combineLoading} from './graph/combineLoading'
import { mapState } from 'vuex';
export default {
    components:{
        horizontalGraph,
        selectBox,
        datePicker,
        preloader
    },
    data(){
        return{
            filter:{
                date_start:null,
                date_end:null,
                attribute:null,
                graph_value:null,
                label:null,
            },
            groupBy:[],
            value:[],
            label:[],
            graph: {
                average:0,
                total:0,
                data:[]
            },
            dLoading:false
        }
    },
    methods:{
        submit(){
            // Create a copy of "this.filter" to avoid state mutation.
            let data = cloneDeep(this.filter)
            Object.keys(data).forEach(k=>{
                if(data[k]){
                    if(data[k] instanceof Date){
                        data[k] = new Date(data[k].getTime() - (data[k].getTimezoneOffset() * 60000 ))
                        .toISOString()
                        .split("T")[0];
                    }
                    else if(typeof data[k] == 'object')
                    data[k] = this.select(data[k])
                }
            });
            this.$store.dispatch('api/getData',{
                force: true,
                method:'post',
                post:{...data},
                url: '/chart-values',
                field: 'loadGraph',
            })
            .then(res => {
                this.updateGraph(res)
            })
            .catch(e => {
            })
        },
        assign(data){
            if(data){
                let { attribute , graph_value , labels } = data
                // set options
                this.groupBy = attribute
                this.value = graph_value
                this.label = labels
                //set default
                this.filter.attribute = attribute[0]
                this.filter.graph_value = graph_value[0]
                this.filter.label = labels[0]
                this.submit()
            }
        },
        /**
         * @param data data to be passed in to the graph from api.
         * This function sets the graph arguments like Total, Average and
         * data.
         * default values are set in the vue data object.
         */
        updateGraph(data){
          this.graph = drawGraph(data)
        },
        /**
         * call assign function on success.
         */
        loadFilter(){
            this.$store.dispatch('api/getData',{
                force: true,
                method:'get',
                url: '/graph-filter',
                field: 'graphFilterLoad',
            })
            .then(res => {
                this.assign(res)
            })
            .catch(e => {})
        },
        select(obj){
            if(obj == null) return null
            else return obj.value
        },
        clearFilter(){
            for ( let field in this.filter){
                this.filter[field] = null
            }
        },
        download(){
            this.dLoading = true
            let data = cloneDeep(this.filter)
            Object.keys(data).forEach(k=>{
                if(data[k]){
                    if(data[k] instanceof Date){
                        data[k] = new Date(data[k].getTime() - (data[k].getTimezoneOffset() * 60000 ))
                        .toISOString()
                        .split("T")[0];
                    }
                    else if(typeof data[k] == 'object')
                    data[k] = this.select(data[k])
                }
            });
            this.$store.dispatch('api/getData',{
                force: true,
                method:'post',
                post:{...data},
                url: '/download-chart',
                field: 'downloadGraph',
                buffer:true,
                handler:this.fileDownload
            })
            .catch(e => {})
        },
        fileDownload(r){
            var blob = new Blob([r], {type: "application/vnd.ms-excel"});
            let url = window.URL.createObjectURL(blob); 
            let a = document.createElement("a");
            a.href = url;
            a.download = "merchant_graph.xlsx";
            a.click();
            window.URL.revokeObjectURL(url);
            this.dLoading = false
        }
    },
    computed:{
        isValid(){
            return fieldsAreValid(this.filter.attribute,this.filter.graph_value,this.filter.label)
        },
        errors(){
            return fieldErrors(
                    {
                        field:'attribute',
                        value:this.filter.attribute,
                    },
                    {
                        field:'graph_value',
                        value:this.filter.graph_value,
                    },
                    {
                        field:'label',
                        value:this.filter.label
                    }
            )
        },
        ...mapState('api',{
            filterLoading:s => s.loading.graphFilterLoad,
            graphLoading:s => s.loading.loadGraph
        }),
        loading(){
            return combineLoading(this.filterLoading,this.graphLoading)
        }
    },
    created(){
        this.loadFilter()
    }
}
</script>
<style 
    scoped
    lang="scss"
    src="~v/graph.scss"
>
</style>