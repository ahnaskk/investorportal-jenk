/**
 * Class representing apexchart pie graph.
 * @author vishnu.
 * @module GraphHelper
 * @typedef {Object} Keys
 * @property {string} name - key name of the label from theh API.
 * @property {string} id - key name of the id from theh API.
 * @property {string} amount - key name of the amount from theh API.
 */
export default class GraphHelper{
    /**
     * Generates initial series and labels.
     * @param { Array <object> } _data - Graph data from the api.
     * @param {Keys} _keys - Key names of the label, series, and id.
     */
    constructor(_data,_keys){
        this.data = _data
        this.keys = _keys
        this.valid = undefined
        this.graphSeries = []
        this.hiddenIndices = []
        this.borrows = []
        this.mutated = {
            label:[],
            series:[],
            description:[],
            colors:[]
        }
        this.validateData()
        this.initialSeries = _data
    }
    /**
     * Graph colors are defined in the static colors method.
     * Add or remove colors on the colors method.
     * @returns {Array} - colors
     */
    static colors(){
        return ['#a86b00','#50BFFE','#4EBE67','#F86995','#727BED','#e75a5a','#58508d','#bc5090']
    }
    /**
     * @param {boolean} valid - sets whether the data is valid or not.
     */
    set isValid(valid){
        this.valid = valid
        return valid
    }
    /**
     * @param {Number} limit - sets the minimum percentage to be shown in the graph
     * if the value is less than the limiting condition.
     */
    set setLowerLimit(limit){
        this.lowerLimit = limit
    }
    /**
     * @param {Array} data - graph data from the API
     */
    set initialSeries(data){
        let acc = []
        for(let i=0; i<this.size; i++){
            let graphItem = {}
            graphItem.name = data[i][this.keys.name]
            graphItem.amount = this.calculatePercentage(data[i][this.keys.amount])
            graphItem.description =  data[i][this.keys.amount]
            graphItem.id = data[i][this.keys.id]
            graphItem.color = GraphHelper.colors()[i]
            graphItem.index = i
            acc.push(graphItem)
        }
        this.graphSeries = acc
        if(!this.borrows.length == 0) {
            this.adjustPercent()
        }
        this.sort()
        return acc
    }
    /**
     * @param {Array} indices - indices to be removed from the initially generated
     * graph values
     */
    set setHiddenIndices(indices){
        this.hiddenIndices = indices
        this.mutate()
    }
    /**
     * @returns {number} size of the data array.
     */
    get size(){
        if(this.valid) return this.data.length
        return 0
    }
    /**
     * @returns {Number} total amount calculated from the data array.
     */
    get totalAmount(){
        if(this.valid){
            return this.data.reduce( (acc,amount) => {
                let conveted = Number(amount[this.keys.amount]) 
                return acc+conveted
            },0)
        }
        return 0
    }
    /**
     * Validates the data.
     * @returns {Boolean} data is valid or not.
     */
    validateData(){
        if( Array.isArray(this.data) && this.data.length ){
            this.valid = true
            return true
        }
        return false
    }
    /**
     * 
     * @param {Number} amount - individual amount.
     * calculates the percentage of the given amount over total.
     * @returns {Number} percentage of the amount.
     * calculates percentage and sets borrows if there are percentages less than the 
     * limit set.
     */
    calculatePercentage(amount){
        let percent =  (amount/this.totalAmount)*100
        this.setLowerLimit = 2
        if(percent < this.lowerLimit){
            let borrow =  this.lowerLimit - percent
            this.borrows.push(borrow)
            return 2 
        }
        else {
            return percent
        }
    }
    /**
     * if there are borrows, largest percentage value in the array is decremented.
     * (appx.)
     * @returns {void}
     */
    adjustPercent(){
        this.borrows.forEach( borrow => {
            this.borrowFromLargestPercentage(borrow)
        })
        this.roundOff()
    }
    /**
     *
     * @param {Number} borrow - borrows from the amounts which are less than the limit set.
     * @returns {void}
     */
    borrowFromLargestPercentage(borrow){
        let largestPercentage = 0
        let largestItem = null
        this.graphSeries.forEach( item => {
            if( item.amount > largestPercentage){
                largestPercentage = item.amount
                largestItem = item
            }

        })
        largestItem.amount = largestItem.amount - borrow
    }
    /**
     * converts the percentages to integer values.
     */
    roundOff(){
        this.graphSeries.forEach(item => {
            item.amount = Math.floor(item.amount)
        })
    }
    /**
     * sorts the graph series according to the indices.
     */
    sort(){
        this.graphSeries = this.graphSeries.sort( (a,b) => a.index - b.index)
        this.split(this.graphSeries)
    }
    /**
     * 
     * @param {Object} graphSeries - graph data
     * sets the label, description,series, and color values except the hidden ones in mutated property.
     * @returns {void}
     */
    split(graphSeries){
        this.resetMutated()
        graphSeries.forEach( item => {
            this.mutated.label.push(item.name)
            this.mutated.description.push(item.description)
            this.mutated.series.push(item.amount)
            this.mutated.colors.push(item.color)
        })
    }
    /**
     * sets the label, description,series, and color values if there are hidden indices.
     * @returns {void}
     */
    mutate(){
        let mutatedSeries = this.graphSeries.reduce( (acc,item) => {
            if(!(this.hiddenIndices.includes(item.index))){
                acc.push(item)
            }
            return acc
        },[])
        this.split(mutatedSeries)
    }
    /**
     * resets the mutated property.
     * @returns {void}
     */
    resetMutated(){
        this.mutated = {
            label:[],
            series:[],
            description:[],
            colors:[]
        }
    }

}

