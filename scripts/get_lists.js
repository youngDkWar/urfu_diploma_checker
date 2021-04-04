let instruction = document.querySelectorAll(".instruction");
let instList = document.querySelector(".instruction-list");
let manual = document.querySelector('.manual-list');

instruction[0].onclick = function (){
    if (instList.style.display === "none"){
        instList.style.display = "block";
    }
    else {
        instList.style.display = "none";
    }
}

instruction[1].onclick = function (){
    if (manual.style.display === "none"){
        manual.style.display = "block";
    }
    else {
        manual.style.display = "none";
    }
}
