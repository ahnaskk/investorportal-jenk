<template>
    <aside class="breadcrumb">
        <div class="left">
            <h2 class="page-title">
                {{ $route.name.replace(/-/g,' ') }}
            </h2>
            <ul class="bd-list">
                <li class="bd-item">
                    <router-link to="/">
                        Home
                    </router-link>
                </li>
                <li class="divider">/</li>
                <li class="bd-item" v-if="$route.name == 'marketplace-documents'">
                    <router-link to="/marketplace">
                        marketplace 
                    </router-link>
                </li>
                <li class="divider" v-if="$route.name == 'marketplace-documents'">/</li>
                <li class="bd-item">
                    <router-link :to="$route.path">
                        {{ $route.name.replace(/-/g,' ') }}
                    </router-link>
                </li>
            </ul>
        </div>
    </aside>
</template>

<script>
    export default {
        name: 'layout-breadcrumb'
    }
</script>

<style
  lang="scss"
  scoped
  src="~l/breadcrumb.scss"
></style>