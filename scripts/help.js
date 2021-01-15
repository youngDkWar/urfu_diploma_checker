function Instruction()
{
    var obj=document.getElementById('instruction');
    if(obj.style.display=='none')
        obj.style.display='block';
    else
        obj.style.display='none';
}
function Doc()
{
    var obj=document.getElementById('doc');
    if(obj.style.display=='none')
        obj.style.display='block';
    else
        obj.style.display='none';
}

function chg(id,chk){
    var el = document.getElementById(id);
    var ch = document.getElementById(chk);
    if (el.src.indexOf("images/plus.jpeg")>0){
        el.src="images/plus.jpeg"
        ch.checked="true";
    }else{
        el.src="images/plus.jpeg"
        ch.checked="";
    }
}