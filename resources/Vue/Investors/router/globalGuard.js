import store from '@/store';
export default (to,from,next)=>{
    const loggedIn = store.getters['auth/loggedIn'];
    // visitor
    const forVisitor = to.matched.some(route => route.meta.visitor);
    // auth
    const requiresAuth = to.matched.some(route => route.meta.user);
    // participant
    const forParticipant = to.matched.some(route => route.meta.participant);
    const marketplaceFlag = store.getters['auth/marketplaceFlag'];
    // redirect path
    const redirect = to.meta ? to.meta.redirect || null : null;
    // check
    if(requiresAuth){
        if(!loggedIn){
            // this state is used in App.vue > watch > loggedIn
            store.dispatch('init/backUpRoute',{url:to.path})
            if(redirect){
                next({path:redirect});
            }else next({ path:'/login' })
        }else{
            if(forParticipant && !marketplaceFlag)
                next({path:'/'});
            else next()
        }
    }
    else if(forVisitor){
        if(loggedIn){
            if(redirect){
                next({path:redirect});
            }else{
                next({ path:'/' });
            }
        }else next();
    }
    else next();
};
