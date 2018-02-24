class test {
    async *test() {
        var res = await operation(param);
        yield res; 
    }
}