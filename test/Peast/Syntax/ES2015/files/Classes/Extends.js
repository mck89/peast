class Angry extends Person
{
    speak() {
        super.speak("Go away!");
        super["speak"]("...");
        super();
    }
}