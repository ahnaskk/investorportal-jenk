/**
 * Redirects to a given link in a new tab
 * @param link link to be redirected
 */
export function redirect(link:string):void{
    const reLink:HTMLAnchorElement = document.createElement('a')
    reLink.target = '_blank'
    reLink.href = link
    reLink.setAttribute('visibility', 'hidden');
    reLink.click();
}