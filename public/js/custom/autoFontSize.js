function AutoFontSize (el,min = 12){
    this.el = el;
    this.min = min
    this.containerWidth();
}

Object.defineProperty(AutoFontSize.prototype,'elements',{
    get : function(){
        return $(this.el);
    }
})

AutoFontSize.prototype.containerWidth = function(){
    $(this.elements).each(function(index,elem){
        $(elem).css({
            'display':'inline-block',
            'max-width': 'fit-content'
        })
        let parentContainerWidth = $(elem).parent().width();
        let elemHeight = $(elem).height();
        let fs = window.getComputedStyle(elem,null).getPropertyValue('font-size').replace('px','');
        let charWidth = $(elem).width();
        if(charWidth > parentContainerWidth){
            maxSize = this.setFontSize(charWidth,parentContainerWidth,elem,fs)
            $(elem).css({
                'font-size':maxSize+'px',
                'height':elemHeight+'px'
            })
        }
    }.bind(this))
}

AutoFontSize.prototype.setFontSize = function(charW,containerW,el,font){
    let fs = Math.floor((containerW/charW)*font)
    if(fs < this.min){
        $(el).css({
            'text-overflow':'ellipsis',
            'overflow':'hidden',
            'white-space':'nowrap',
            'width': '100%'
        })
    }
    return fs < this.min ? this.min : fs
}