/**
 * Error factory.
 */
export class Errors{
    private data:any 
    private custom:boolean | string
    protected error:string
    constructor(_data:any[],_custom:boolean | string){
        this.data = _data
        this.custom = _custom
        this.error = this.throwError()
    }
    throwError(){
        if(this.data) return this.handleError()
        if(this.custom) return this.custom as any as string
        else return 'Something went wrong! Please try again later'
    }
    handleError(){
        if(Array.isArray(this.data)){
            this.data.forEach(error => {
                if(error){
                    return error
                }
            })
        }
        return this.data
    }
}