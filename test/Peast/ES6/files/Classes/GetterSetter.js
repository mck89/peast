class Employee extends Person
{
    get name()
    {
        return [this.firstname, this.surname];
    }
    
    set name(name)
    {
        [firstname, surname] = name.split(" ");
        this.firstname = firstname;
        this.surname = surname;
    }
}