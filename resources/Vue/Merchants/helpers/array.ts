export class ArrayFactory{
    constructor(){

    }
    /**
     * - Reduces multiple arrays into a single array.
     * @param arrays list of arrays to reduce.
     * @returns reduced array
     */
    public mergeArrays(...arrays:any[]):any[]{
        if(arrays){
            return arrays.reduce( (mergedArray,array) => {
                if(Array.isArray(array)){
                    return mergedArray.concat(array)
                }
            },[])
        }
        return []
    }
    static errors(err:string[] | string , index:number = 0){
        if(!err) return null
        if(Array.isArray(err)) return err[index]
        return err
    }
}