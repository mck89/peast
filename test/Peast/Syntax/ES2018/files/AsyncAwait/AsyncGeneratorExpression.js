var fn = async function *(param) {
    var res = await operation(param);
    yield res;
}