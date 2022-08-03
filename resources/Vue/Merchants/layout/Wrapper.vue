<template>
    <section
        class="section-wrapper"
        :class="[{'layout-app': layout != 'plain'}]"
    >
        <!-- PLAIN -->
        <div
            class="layout-wrapper plain"
            v-if="layout == 'plain'"
        >
            <slot></slot>
        </div>
        <!-- HOME -->
        <div
            class="layout-wrapper app"
            v-else-if="layout == 'home'"
        >
            <Head @refresh="refresh" :merchants="merchants"/>
            <section class="main-view-slot">
                <slot></slot>
            </section>
            <Footer />
        </div>
        <!-- DEFAULT -->
        <div
            class="layout-wrapper app"
            v-else
        >
            <Head @refresh="refresh" :merchants="merchants"/>
            <section class="main-view-slot">
                <slot></slot>
            </section>
            <Footer />
        </div>
    </section>
</template>

<script>
    import Head from './Header';
    import Footer from './Footer';
    import Breadcrumb from './Breadcrumb';
    export default {
        name: 'layout-wrapper',
        components:{
            Head,
            Footer,
        },
        props:{
            layout: String,
            merchants:{
                default:null
            }
        },
        methods:{
            refresh(){
                this.$emit('refresh')
            }
        }
    }
</script>

<style
    src="~l/wrapper.scss"
    lang="scss"
    scoped
></style>