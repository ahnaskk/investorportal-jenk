export const loader={
    // called once when the directive is first bind to the element
    bind(el){
        if(el.classList){
            el.classList.add('loader')
        }
    },
    // called on each nod update
    update(el,binding){
        if(binding.value){
            el.classList.add('loading');
        }
        else{
            el.classList.remove('loading');
        }
    }
};