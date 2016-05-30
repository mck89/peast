export default class {
    get item(){
        return this.items[this.current];
    }
    set item(val) {
        this.items[this.current] = val;
    }
}