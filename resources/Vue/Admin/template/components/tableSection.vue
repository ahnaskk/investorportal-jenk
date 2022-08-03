<template>
  <section class="table-sec">
    <!-- header -->
    <header class="table-header" v-if="!hideHeader">
      <!-- date selector -->
      <div class="date-selector" v-if="rangeSelector">
        <!-- <div class="input-col">
          <h2 class="input-title">From Date</h2>
            <datePicker v-model="filter.from" />
        </div>
        <div class="input-col">
          <h2 class="input-title">To Date</h2>
          <datePicker v-model="filter.to" />
        </div>
        <div class="input-col">
          <h2 class="input-title">Merchant</h2>
          <datePicker v-model="filter.merchant" />
        </div>
        <div class="input-col bt-col">
          <button
            class="blue-bt"
            type="button"
            @click="applyFilter"
          >
            Apply Filter
          </button>
        </div> -->
        <div class="input-col hr">
          <div class="hr"></div>
        </div>
      </div>
      <!-- filter selector -->
      <div class="filters">
        <div class="filter-box" v-if="rangeFilters">
          Show
          <selectBox
            class="select-box"
            :options="rangeFilters"
            :selected="filterRange ? filterRange : null"
            @select="selectRange"
            />
          Entries
        </div>
        <div class="search-box" v-if="!hideSearch">
          Search :
          <form @submit.prevent>
            <input
              type="text"
              class="search-input"
              :placeholder="'Enter Keyword'"
              v-model="keyword"
              @change="search"
            >
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
          <tr v-if="data.length > 0">
            <th v-for="(title,i) in Object.keys(data[0])" :key="i+0.1">
              {{ title | capitalize }}
            </th>
          </tr>
        </thead>
        <tbody v-if="data.length > 0">
          <tr v-for="n in data.length" :key="n+0.2">
            <td
              v-for="(val,i) in Object.values(data[n-1])"
              :key="i"
              :class="{action:i==0 || i==1}"
            >
              <!-- just the value -->
              <span v-if="val">
                {{val.value || val}}
              </span>
              <span v-else>
                
              </span>
            </td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr>
            <span>No data available in table</span>
          </tr>
        </tbody>
      </table>
    </main>
    <!-- footer -->
    <footer class="table-footer" v-if="pagination && pagination.last_page > 1">
      <article class="table-info">
        Showing {{
          pagination.from + 1
        }} to {{
          pagination.to + 1
        }} of {{
          pagination.total + 1
        }} entries
      </article>
      <div class="pagination">
        <paginate
          :page-count="pagination.last_page"
          :value="pagination.current_page"
          :click-handler="goToPage"
          :prev-text="'Prev'"
          :next-text="'Next'"
          prevClass="prev"
          nextClass="next"
          :container-class="'paginator'"
        ></paginate>
      </div>
    </footer>
    <!-- <div class="controls">
      <button data-v-2675d25c="" type="button" class="green-bt">
        View investors
      </button>
      <button data-v-2675d25c="" type="button" class="blue-bt">
        Create
      </button>
    </div> -->
  </section>
</template>

<script>
  import selectBox from '@ac/selectBox';
  import datePicker from '@ac/datePicker'
  export default {
    name: 'table-section',
    data(){
      return {
        keyword: '',
        filter:{
          from: '',
          to: '',
          merchant_name: ''
        },
        updatePaginate: 0,
      }
    },
    computed: {
      titles() {
        return Object.keys(this.data[0])
      }
    },
    methods: {
      applyFilter(){
        const f = {
          ...this.filter
        }
        Object.keys(f).forEach(k=>{
          if(f[k] instanceof Date)
            f[k] = f[k].toUTCString();
          f[k]=f[k].replace(/\s/g,'%');
        });
        
        this.$router.push({
          path:this.$route.path,
          query: {
            ...this.$route.query,
            ...f
          }
        })
      },
      selectRange(e){
        this.$router.push({
          path: this.$route.path,
          query:{
            ...this.$route.query,
            range: e.value
          }
        });
      },
      search(){
        this.$router.push({
          path: this.$route.path,
          query:{
            ...this.$route.query,
            page:1,
            keyword: this.keyword.replace(/\s/g,'%')
          }
        });
      },
      goToPage(page) {
        this.$router.push({
          path: this.$route.path,
          query:{
            ...this.$route.query,
            page
          }
        });
      }
    },
    components: {
      selectBox,
      datePicker
    },
    props:{
      hideHeader:Boolean,
      rangeSelector:Boolean,
      showSlot: Boolean,
      data:Array,
      rangeFilters:Array,
      searchKeyword: String,
      filterRange: [Number,String],
      filterFrom: [Number,String],
      filterTo: [Number,String],
      filterMerchant: [Number,String],
      pagination: Object,
      hideSearch: Boolean,
      columns: Object
    },
    created(){
      if(this.searchKeyword) this.keyword = this.searchKeyword;
      if(this.filterFrom) this.filter.from = new Date(this.filterFrom)
      if(this.filterTo) this.filter.to = new Date(this.filterTo)
      if(this.filterMerchant) this.filter.merchant_name = this.filterMerchant
      console.log(this.data)
    },
    watch:{
      pagination:{
        deep: true,
        immediate:true,
        handler(){
          this.updatePaginate ++;
        }
      }
    }
  }
</script>

<style lang="scss" scoped src="~ac/tableSection.scss"></style>