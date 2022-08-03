export default {
    $clickOut(el,target, edge = document.body ){
      let currentTarget=target;
      while(currentTarget){
        if(currentTarget==el) return false;
        if(currentTarget==edge) return true;
        currentTarget=currentTarget.parentNode;
      }
    },
    parcePercent(v){
        return parseInt(
          v.toString()
          .replace(/[^0-9]/g, "")
        );
    }
}
