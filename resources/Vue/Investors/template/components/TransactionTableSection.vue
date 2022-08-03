<template>
    <section class="table-sec">
        <!-- header -->
        <header class="table-header" v-if="!hideHeader">
            <!-- date selector -->
            <div
                class="date-selector"
                v-if="rangeSelector"
                :class="{download:downloadLink}"
            >
                <div class="input-col">
                    <h2 class="input-title">From Date</h2>
                    <datePicker v-model="filter.from" />
                </div>
                <div class="input-col">
                    <h2 class="input-title">To Date</h2>
                    <datePicker v-model="filter.to" />
                </div>
                <!-- <div class="input-col">
                    <h2 class="input-title">Account No</h2>
                    <input type="text" v-model="filter.account_no" class="input">
                </div> -->
                <div class="input-col bt-col">
                    <button class="action-bt blue-bt" type="button" @click="applyFilter">
                        Apply Filter
                    </button>
                    <a
                        class="action-bt blue-bt download-link"
                        @click="download(downloadLink)"
                        v-if="downloadLink"
                        target="_blank"
                    >
                        Download
                    </a>
                </div>
                <div class="input-col hr">
                    <div class="hr"></div>
                </div>
            </div>
            <!-- filter selector -->
            <div class="filters">
                <div class="filter-box" v-if="rangeFilters">
                    Show
                    <selectBox class="select-box" :options="rangeFilters" :selected="filterRange ? filterRange : null" @select="selectRange" />
                    Entries
                </div>
                <div class="search-box" v-if="!hideSearch">
                    Search :
                    <form @submit.prevent>
                        <input type="text" class="search-input" :placeholder="'Enter Keyword'" v-model="keyword" @change="search">
                    </form>
                </div>
            </div>
        </header>
        <!-- body -->
        <main class="table-wrapper" v-if="showSlot">
            <slot />
        </main>
        <main class="table-wrapper" v-else>
            <table class="data-table">
                <thead>
                    <tr>
                        <th v-for="(title,i) in Object.keys(data[0])" :key="i+0.1">
                            {{ title | capitalize }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="n in data.length" :key="n+0.2">
                        <td v-for="(val,i) in Object.values(data[n-1])" :key="i" :class="{action:i==0 || i==1}">
                            <!-- just the value -->
                            <span>
                                {{val.value || val}}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </main>
        <!-- footer -->
        <footer class="table-footer" v-if="pagination && !hidePagination">
            <article class="table-info">
                Showing {{
          pagination.from
        }} to {{
          pagination.to
        }} of {{
          pagination.total
        }} entries
            </article>
            <div class="pagination" v-if="pagination.last_page > 1">
                <paginate :page-count="pagination.last_page" :value="pagination.current_page" :click-handler="goToPage" :prev-text="'Prev'" :next-text="'Next'" prevClass="prev" nextClass="next" :container-class="'paginator'"></paginate>
            </div>
        </footer>
    </section>
</template>

<script>
import selectBox from '@c/selectBox';
import datePicker from '@c/datePicker'
export default {
    name: 'table-section',
    data() {
        return {
            keyword: '',
            filter: {
                from: '',
                to: '',
                account_no: ''
            },
            updatePaginate: 0
        }
    },
    computed: {
        titles() {
            return Object.keys(this.data[0])
        }
    },
    methods: {
        applyFilter() {
            const f = {
                ...this.filter
            }
            Object.keys(f).forEach(k => {
                if (f[k]) {
                    if (f[k] instanceof Date)
                        f[k] = f[k].toUTCString();
                    f[k] = f[k].replace(/\s/g, '%');
                }
            });

            this.$router.push({
                path: this.$route.path,
                query: {
                    ...this.$route.query,
                    ...f,
                    page: 1
                }
            }).catch(e => e);
        },
        selectRange(e) {
            this.$router.push({
                path: this.$route.path,
                query: {
                    ...this.$route.query,
                    range: e.value,
                    page: 1
                }
            });
        },
        search() {
            this.$router.push({
                path: this.$route.path,
                query: {
                    ...this.$route.query,
                    page: 1,
                    keyword: this.keyword.replace(/\s/g, '%')
                }
            });
        },
        goToPage(page) {
            this.$router.push({
                path: this.$route.path,
                query: {
                    ...this.$route.query,
                    page
                }
            });
        },
        download(link){
            const f = {
            ...this.filter
            }
            Object.keys(f).forEach(k=>{
            if(f[k]){
                if(f[k] instanceof Date){
                    f[k] = new Date(f[k].getTime() - (f[k].getTimezoneOffset() * 60000 ))
                    .toISOString()
                    .split("T")[0];
                }
                else if(typeof f[k] == 'object')
                f[k] = f[k].value
                f[k]=f[k].toString().replace(/\s/g,'%');
            }
            });
            let target = link
                for (let key in f){
                    if(f[key] !=null ){
                        target+=`&${key}=${f[key]}`
                    }
                }
            Object.assign(document.createElement('a'), {
                target: '_blank',
                href: target,
            }).click();
      }
    },
    components: {
        selectBox,
        datePicker
    },
    props: {
        hideHeader: Boolean,
        rangeSelector: Boolean,
        showSlot: Boolean,
        data: Array,
        rangeFilters: Array,
        searchKeyword: String,
        filterRange: [Number, String],
        filterFrom: [Number, String],
        filterTo: [Number, String],
        filterMerchant: [Number, String],
        pagination: Object,
        hideSearch: Boolean,
        hidePagination: Boolean,
        downloadLink: String
    },
    created() {
        if (this.searchKeyword) this.keyword = this.searchKeyword;
        if (this.filterFrom) this.filter.from = new Date(this.filterFrom)
        if (this.filterTo) this.filter.to = new Date(this.filterTo)
        if (this.filterMerchant) this.filter.account_no = this.filterMerchant
    },
    watch: {
        pagination: {
            deep: true,
            immediate: true,
            handler() {
                this.updatePaginate++;
            }
        }
    }
}
</script>

<style lang="scss" scoped src="~c/tableSection.scss"></style>