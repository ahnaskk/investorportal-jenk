<script>
export default {
    name: 'test',
    render(createElement){
        return createElement('h1',this.$slots.default)
    }
}
</script>