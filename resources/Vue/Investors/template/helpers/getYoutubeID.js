export const getYoutubeID = (url) => {
    if(!url) return 
    if(/youtube.com/i.test(url)){
        const id = new URL(url)
        return id.searchParams.get('v')
    }
    else if(/youtu.be/i.test(url)){
        return url.split('/').pop()
    }
    return null
}
