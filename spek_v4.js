function readAloud1() {
const uttr = new SpeechSynthesisUtterance()
uttr.text = "Please get in"
    uttr.lang = "en-US"
    uttr.rate = 0.6
    uttr.pitch =1
    uttr.volume = 1
    window.speechSynthesis.speak(uttr)
}
function readAloud2() {
const uttr = new SpeechSynthesisUtterance()
uttr.text = "Let me close the door"
    uttr.lang = "en-US"
    uttr.rate = 0.6
    uttr.pitch = 1
    uttr.volume = 1
    window.speechSynthesis.speak(uttr)
}
function readAloud3() {
const uttr = new SpeechSynthesisUtterance()
uttr.text = "Please fasten your seatbelt"
    uttr.lang = "en-US"
    uttr.rate = 0.6
    uttr.pitch = 1
    uttr.volume = 1
    window.speechSynthesis.speak(uttr)
}
function readAloud4() {
const uttr = new SpeechSynthesisUtterance()
uttr.text = "Where are you going"
    uttr.lang = "en-US"
    uttr.rate = 0.6
    uttr.pitch = 1
    uttr.volume = 1
    window.speechSynthesis.speak(uttr)
}
function readAloud5() {
const uttr = new SpeechSynthesisUtterance()
uttr.text = "Do you have the address"
    uttr.lang = "en-US"
    uttr.rate = 0.6
    uttr.pitch = 1
    uttr.volume = 1
    window.speechSynthesis.speak(uttr)
}
function readAloud6() {
const uttr = new SpeechSynthesisUtterance()
uttr.text = "Would you like to take a toll road"
    uttr.lang = "en-US"
    uttr.rate = 0.6
    uttr.pitch = 1
    uttr.volume = 1
    window.speechSynthesis.speak(uttr)
}
function readAloud7() {
const uttr = new SpeechSynthesisUtterance()
uttr.text = "Is this okay"
    uttr.lang = "en-US"
    uttr.rate = 0.6
    uttr.pitch = 1
    uttr.volume = 1
    window.speechSynthesis.speak(uttr)
}
function readAloud8() {
const uttr = new SpeechSynthesisUtterance()
uttr.text = "The fare is the one shown on the display"
    uttr.lang = "en-US"
    uttr.rate = 0.6
    uttr.pitch = 1
    uttr.volume = 1
    window.speechSynthesis.speak(uttr)
}
function readAloud9() {
const uttr = new SpeechSynthesisUtterance()
uttr.text = "Don't forget anything"
    uttr.lang = "en-US"
    uttr.rate = 0.6
    uttr.pitch = 1
    uttr.volume = 1
    window.speechSynthesis.speak(uttr)
}
function readAloud10() {
const uttr = new SpeechSynthesisUtterance()
uttr.text = "Thank you for riding"
    uttr.lang = "en-US"
    uttr.rate = 0.6
    uttr.pitch = 1
    uttr.volume = 1
    window.speechSynthesis.speak(uttr)
}
function readAloud11() {
const uttr = new SpeechSynthesisUtterance()
uttr.text = "How much will it cost"
    uttr.lang = "en-US"
    uttr.rate = 0.6
    uttr.pitch = 1
    uttr.volume = 1
    window.speechSynthesis.speak(uttr)
}
function readAloud11_1() {
const uttr = new SpeechSynthesisUtterance()
const textbox = document.getElementById('yen')
const text = document.getElementById('yen').value;
    uttr.text = "It's about" + text + "yen"
    uttr.lang = "en-US"
    uttr.rate = 0.6
    uttr.pitch = 1
    uttr.volume = 1
    window.speechSynthesis.speak(uttr);
    textbox.value = "";
}
function readAloud12() {
const uttr = new SpeechSynthesisUtterance()
    uttr.text = "How long will it take"
    uttr.lang = "en-US"
    uttr.rate = 0.6
    uttr.pitch = 1
    uttr.volume = 1
    window.speechSynthesis.speak(uttr)
}
function readAloud12_1() {
const uttr = new SpeechSynthesisUtterance()
const time1box= document.getElementById('time1');
const time1= document.getElementById('time1').value;
    uttr.text = "It's about" + time1+"hour"
    uttr.lang = "en-US"
    uttr.rate = 0.6
    uttr.pitch = 1
    uttr.volume = 1
    window.speechSynthesis.speak(uttr);
    time1box.value = "";
}
function readAloud12_2() {
const uttr = new SpeechSynthesisUtterance()
const time2box= document.getElementById('time2')
const time2= document.getElementById('time2').value;
    uttr.text = "It's about" + time2+"minutes"
    uttr.lang = "en-US"
    uttr.rate = 0.6
    uttr.pitch = 1
    uttr.volume = 1
    window.speechSynthesis.speak(uttr);
    time2box.value = "";
}
function readAloud12_3() {
const uttr = new SpeechSynthesisUtterance()
    uttr.text = "It takes about 1 hour and 30 minutes"
    uttr.lang = "en-US"
    uttr.rate = 0.6
    uttr.pitch = 1
    uttr.volume = 1
    window.speechSynthesis.speak(uttr)
}
