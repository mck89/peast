async function *test(param) {
    var res = await operation(param);
    yield res;
}