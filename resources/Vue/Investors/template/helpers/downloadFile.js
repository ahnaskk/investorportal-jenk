export default class downloadFile{
    constructor(_url,_open = true){
        this.url = _url 
        this.open = _open
    }
    get filename(){
        if(!this.url) return 
        return this.url.split('/').pop()
    }
    download(){
        let a = document.createElement('a')
        a.href = this.url
        if(this.open){
            a.target= '_blank'
        }
        document.body.appendChild(a)
        a.download = this.filename
        a.click()
        document.body.removeChild(a)
    }
}
