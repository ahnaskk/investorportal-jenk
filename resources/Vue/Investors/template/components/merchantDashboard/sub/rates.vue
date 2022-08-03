<template>
    <div class="rates-card">
        <div class="corp-info">
            <div class="icon-box">
                <img :src="require('@image/icons/moneySack.svg').default" />
            </div>
            <div class="data-box">
                <h2 class="corp-name">
                    {{ data.display_value == "mid" ? "Merchant ID (MID) : " : '' }}
                    {{ data.merchant  || '--' }}
                </h2>
                <h3 class="funded">
                    {{ data.participant_funded  || '--' }}
                    <span class="label">
                        Net Investment
                    </span>
                </h3>
                <a
                    href="javascript:void(0)"
                    class="active-advance"
                >{{ data.sub_status  || '--' }}</a>
            </div>
        </div>
        <div class="rates">
            <div class="title-box">
                <div class="icon">
                    <img :src="require('@image/icons/hut.svg').default" />
                </div>
                <div class="titles">
                    <h3 class="label">
                        Industry
                        <tooltip
                            tooltipText="Name of Industry"
                            width="200"
                            class="left"
                        />
                    </h3>
                    <h2 class="title">
                        {{ data.industry  || '--' }}
                    </h2>
                </div>
            </div>
            <div class="rates-row">
                <!-- RTR -->
                <div class="rate-col">
                    <div class="rate-box">
                        <div class="icon">
                            <img :src="require('@image/icons/rtr-icon.svg').default" />
                        </div>
                        <div class="titles">
                            <h3 class="label">
                                RTR
                                <tooltip
                                    tooltipText="RTR or Right to Receive represents total amount expected to be collected from the merchant"
                                    width="200"
                                    class="left"
                                />
                            </h3>
                            <h2 class="value">
                                {{ data.rtr  || '--' }}
                            </h2>
                        </div>
                    </div>
                </div>
                <!-- CTD -->
                <div class="rate-col">
                    <div class="rate-box">
                        <div class="icon">
                            <img :src="require('@image/icons/ctd-icon.svg').default" />
                        </div>
                        <div class="titles">
                            <h3 class="label">
                                Cash to Date (CTD)
                                <tooltip
                                    tooltipText="Cash to Date, represents the sum of all cash collected from the inception of your portfolio"
                                    width="200"
                                    class="left"
                                />
                            </h3>
                            <h2 class="value">
                                {{ data.ctd  || '--' }}
                            </h2>
                        </div>
                    </div>
                </div>
                <!-- RTR -->
                <div class="rate-col">
                    <div class="rate-box">
                        <div class="icon">
                            <img :src="require('@image/icons/balance-icon.svg').default" />
                        </div>
                        <div class="titles">
                            <h3 class="label">
                                Balance
                                <tooltip
                                    tooltipText="Amount of money that is due from merchant on current advance"
                                    width="200"
                                    class="left"
                                />
                            </h3>
                            <h2 class="value">
                                {{ data.balance  || '--' }}
                            </h2>
                        </div>
                    </div>
                </div>
                <!-- Profit -->
                <!-- <div class="rate-col">
                    <div class="rate-box">
                        <div class="icon">
                            <img :src="require('@image/icons/profit-icon.svg').default" />
                        </div>
                        <div class="titles">
                            <h3 class="label">
                                Profit
                                <tooltip
                                    tooltipText="The sum of all earnings from the inception of your portfolio"
                                    width="200"
                                    class="left"
                                />
                            </h3>
                            <h2 class="value">
                                {{ data.profit  || '--' }}
                            </h2>
                        </div>
                    </div>
                </div> -->
            </div>
        </div>
    </div>
</template>

<script>
    import tooltip from '@c/tooltip';
    export default {
        name: 'rates',
        components:{
            tooltip
        },
        props: [
            'data'
        ]
    }
</script>

<style
    lang="scss"
    scoped src="~c/merchantDashboard/sub/rates.scss"
></style>