switch (test) {
    case 1: {
        const foo = true;
        console.log(foo);
        break;
    }
    case 2:
    case 3: {
        let foo = false;
        break;
    }
    case 4:
    case 5:
        console.log("3");
        break;
    case 6:
    case 7:
        console.log("Hi");
    default:
        console.log("Invalid")
        break;
}