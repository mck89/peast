var test = async (a,b) => {
    alert(a);
    await test(b);
}