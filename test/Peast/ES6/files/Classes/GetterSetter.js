class Employee extends Person
{
    get name()
    {
        return [this.firstname, this.surname];
    }
    
    set name(name)
    {
        var parts = name.split(" ");
        this.firstname = parts[0];
        this.surname = parts[1];
    }
}