const guard=(to,from,next)=>{
    const meta=to.meta;
    if(meta){
        const title=meta.title;
        if(title) document.title=title;
    }
    next();
};
 
export { guard };