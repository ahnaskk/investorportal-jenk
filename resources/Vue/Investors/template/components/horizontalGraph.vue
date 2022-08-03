<template>
    <div class="graph-outer">
        <div class="head">
            <span class="title">
                Total : {{data.total || 0}}
            </span>
            <span class="title">
                Average : {{data.average || 0}}
            </span>
        </div>
        <div class="graph">
            <div 
                v-for="(item,i) in data.data"
                :key="i"
                class="graph-item"
            >
                <p class="label">{{item.name}}</p>
                <span class="outer">
                    <span 
                        class="inner"
                        :style="{
                            maxWidth:item.percentage+'%',
                            backgroundColor:colors[i%colors.length],
                            animationDelay:i*0.1+'s'
                        }"
                    >
                    </span>
                </span>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name:'horizontal-graph',
    props:{
        data:{
            type:Object,
            default:null
        }
    },
    data(){
        return{
            colors:[
                "#3366cc",
                "#dc3912",
                "#ff9900",
                "#109618",
                "#990099",
                "#0099c6",
                "#dd4477",
                "#66aa00",
                "#b82e2e",
                "#316395",
                "#3366cc",
                "#994499",
                "#22aa99",
                "#aaaa11",
                "#6633cc",
                "#e67300",
                "#8b0707",
                "#651067",
                "#329262",
                "#5574a6",
                "#3b3eac",
                "#b77322",
                "#16d620",
                "#b91383",
                "#f4359e",
                "#9c5935",
                "#a9c413",
                "#2a778d",
                "#668d1c",
                "#bea413",
                "#0c5922",
                "#743411"
            ]
        }
    }
}
</script>
<style 
    lang="scss"
    src="~c/horizontal-graph.scss"
    scoped
>
</style>