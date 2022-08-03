/**
 * Scroll interfaces
 */
interface ScrollProperties{
    scrollTop:number,
    clientHeight:number,
    scrollHeight:number
}

export class ScrollEvent{
    protected element:string
    public hit:boolean
    constructor(_element:string){
        this.element = _element
        this.hit = false
    }
    public elHitBottom(target:ScrollProperties){
        let {scrollTop , clientHeight , scrollHeight } = target
        if (scrollTop + clientHeight >= scrollHeight) {
            return true
        }
    }
    public windowHitBottom(){
        if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight) {
            return true
        }
    }
}

