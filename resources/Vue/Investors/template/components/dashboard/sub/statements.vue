<template>
    <div class="statement-box">
        <img
            :src="require('@image/icons/download-statement.svg').default"
            class="download-img"
        />
        <div class="content-box">
            <h2 class="title">Last Generated Statement</h2>
            <h3 class="on">
                Generated on <br /> {{ on }}
            </h3>
            <div class="range"> {{ period }} </div>
        </div>
        <div class="action">
            <router-link to="/statements" class="view-all">
                View All Statements
                <span class="angle">
                    <icon :icon="['fas','angle-right']" />
                </span>
            </router-link>
        </div>
    </div>
</template>

<script>
export default {
    name: 'statements',
    props:[
        'on',
        'period'
    ]

}
</script>

<style
    lang="scss"
    scoped
    src="~c/dashboard/sub/statements.scss"
></style>