export default{
    data() {
        return {
            vCurrency: {
                currency: 'USD',
                locale: undefined,
                autoDecimalMode: false,
                precision: undefined,
                distractionFree: {
                    hideNegligibleDecimalDigits:false,
                    hideCurrencySymbol:false,
                    hideGroupingSymbol:false
                },
                valueAsInteger: false,
                min: 10
            }
        }
    },
}